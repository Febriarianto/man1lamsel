<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SsoController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        abort_unless(config('kemenag-sso.enabled'), 404);
        $this->ensureConfigured();

        $state = Str::random(48);
        $nonce = Str::random(48);
        $request->session()->put('kemenag_sso_state', $state);
        $request->session()->put('kemenag_sso_nonce', $nonce);

        $query = http_build_query([
            'client_id' => config('kemenag-sso.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', config('kemenag-sso.scopes', [])),
            'state' => $state,
            'nonce' => $nonce,
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away(rtrim((string) config('kemenag-sso.authorization_url'), '?').'?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless(config('kemenag-sso.enabled'), 404);

        if ($request->filled('error')) {
            $message = $request->string('error_description')->toString() ?: $request->string('error')->toString();
            return redirect()->route('admin.login')->with('error', 'Login SSO dibatalkan atau ditolak: '.$message);
        }

        $expectedState = (string) $request->session()->pull('kemenag_sso_state');
        $request->session()->forget('kemenag_sso_nonce');
        $receivedState = (string) $request->input('state');

        if ($expectedState === '' || $receivedState === '' || ! hash_equals($expectedState, $receivedState)) {
            return redirect()->route('admin.login')->with('error', 'Validasi keamanan SSO gagal. Silakan ulangi proses login.');
        }

        $code = (string) $request->input('code');
        if ($code === '') {
            return redirect()->route('admin.login')->with('error', 'Kode otorisasi SSO tidak diterima.');
        }

        try {
            $this->ensureConfigured();
            $token = $this->exchangeCode($code);
            $profile = $this->fetchProfile($token);
            $user = $this->resolveUser($profile);

            if (! $user->active) {
                return redirect()->route('admin.login')->with('error', 'Akun Anda belum aktif atau telah dinonaktifkan oleh administrator.');
            }

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'))->with('success', 'Berhasil masuk melalui SSO Kemenag.');
        } catch (Throwable $exception) {
            Log::error('Kemenag SSO callback failed', [
                'message' => $exception->getMessage(),
                'exception' => get_class($exception),
            ]);

            return redirect()->route('admin.login')->with('error', 'Login SSO belum berhasil. Periksa konfigurasi atau hubungi administrator aplikasi.');
        }
    }

    private function exchangeCode(string $code): string
    {
        $payload = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri(),
        ];

        $request = $this->http()->asForm();
        if (config('kemenag-sso.token_auth_method') === 'client_secret_basic') {
            $request = $request->withBasicAuth(
                (string) config('kemenag-sso.client_id'),
                (string) config('kemenag-sso.client_secret')
            );
            $payload['client_id'] = config('kemenag-sso.client_id');
        } else {
            $payload['client_id'] = config('kemenag-sso.client_id');
            $payload['client_secret'] = config('kemenag-sso.client_secret');
        }

        $response = $request->post((string) config('kemenag-sso.token_url'), $payload);
        if (! $response->successful()) {
            throw new RuntimeException('Token endpoint merespons HTTP '.$response->status());
        }

        $accessToken = (string) $response->json('access_token');
        if ($accessToken === '') {
            throw new RuntimeException('Access token tidak ditemukan pada respons SSO.');
        }

        return $accessToken;
    }

    private function fetchProfile(string $accessToken): array
    {
        $response = $this->http()
            ->acceptJson()
            ->withToken($accessToken)
            ->get((string) config('kemenag-sso.userinfo_url'));

        if (! $response->successful()) {
            throw new RuntimeException('UserInfo endpoint merespons HTTP '.$response->status());
        }

        $profile = $response->json();
        if (! is_array($profile)) {
            throw new RuntimeException('Format profil SSO tidak valid.');
        }

        return $profile;
    }

    private function resolveUser(array $profile): User
    {
        $claims = config('kemenag-sso.claims');
        $providerId = trim((string) data_get($profile, $claims['id']));
        $name = trim((string) data_get($profile, $claims['name']));
        $email = strtolower(trim((string) data_get($profile, $claims['email'])));
        $nip = trim((string) data_get($profile, $claims['nip']));
        $unit = trim((string) data_get($profile, $claims['unit']));
        $avatar = trim((string) data_get($profile, $claims['avatar']));

        if ($providerId === '' || $email === '') {
            throw new RuntimeException('Claim ID atau email tidak tersedia dari SSO.');
        }

        $this->ensureAllowedDomain($email);

        $user = User::query()
            ->where(fn ($query) => $query
                ->where(fn ($byProvider) => $byProvider
                    ->where('auth_provider', 'kemenag_sso')
                    ->where('provider_id', $providerId))
                ->orWhere('email', $email))
            ->first();

        if (! $user && ! config('kemenag-sso.auto_provision')) {
            throw new RuntimeException('Akun belum terdaftar pada aplikasi.');
        }

        $user ??= new User([
            'role' => in_array(config('kemenag-sso.default_role'), ['admin', 'author'], true)
                ? config('kemenag-sso.default_role')
                : 'author',
            'active' => true,
            'password' => Hash::make(Str::random(64)),
        ]);

        $user->fill([
            'name' => $name !== '' ? $name : $email,
            'email' => $email,
            'auth_provider' => 'kemenag_sso',
            'provider_id' => $providerId,
            'nip' => $nip !== '' ? $nip : null,
            'unit_name' => $unit !== '' ? $unit : null,
            'avatar' => $avatar !== '' ? $avatar : null,
            'last_login_at' => now(),
        ]);
        $user->save();

        return $user;
    }

    private function ensureAllowedDomain(string $email): void
    {
        $domains = config('kemenag-sso.allowed_email_domains', []);
        if ($domains === []) {
            return;
        }

        $domain = strtolower((string) Str::afterLast($email, '@'));
        $allowed = collect($domains)->contains(fn ($item) => strtolower(trim((string) $item)) === $domain);
        if (! $allowed) {
            throw new RuntimeException('Domain email tidak diizinkan.');
        }
    }

    private function ensureConfigured(): void
    {
        foreach (['client_id', 'client_secret', 'authorization_url', 'token_url', 'userinfo_url'] as $key) {
            if (blank(config('kemenag-sso.'.$key))) {
                throw new RuntimeException('Konfigurasi KEMENAG_SSO_'.strtoupper($key).' belum diisi.');
            }
        }
    }

    private function redirectUri(): string
    {
        return (string) (config('kemenag-sso.redirect_uri') ?: route('admin.sso.callback'));
    }

    private function http(): PendingRequest
    {
        return Http::timeout((int) config('kemenag-sso.http.timeout', 15))
            ->withOptions(['verify' => config('kemenag-sso.http.verify_ssl', true)]);
    }
}
