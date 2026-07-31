<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        if (blank(config('kemenag-sso.app_id'))) {
            return redirect()->route('admin.login')
                ->with('error', 'APP ID SSO Kemenag belum diisi oleh administrator.');
        }

        $this->ensureConfigured();

        $request->session()->put('kemenag_sso_login_started_at', now()->timestamp);
        $request->session()->put('kemenag_sso_login_attempt', Str::random(40));

        $separator = str_contains((string) config('kemenag-sso.signin_url'), '?') ? '&' : '?';
        $url = rtrim((string) config('kemenag-sso.signin_url'), '&?')
            .$separator
            .http_build_query(['appid' => config('kemenag-sso.app_id')], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless(config('kemenag-sso.enabled'), 404);

        try {
            $this->ensureConfigured();
            $this->validateLoginAttempt($request);

            $tokenParameter = (string) config('kemenag-sso.callback_token_parameter', 'token');
            $token = trim((string) $request->query($tokenParameter));
            if ($token === '') {
                throw new RuntimeException('Token callback SSO tidak diterima.');
            }

            $pegawai = $this->verifyToken($token);
            $user = $this->resolveUser($pegawai);

            if (! $user->active) {
                return redirect()->route('admin.login')
                    ->with('error', 'Akun Anda belum aktif atau telah dinonaktifkan oleh administrator.');
            }

            Auth::login($user, true);
            $request->session()->regenerate();
            $request->session()->put('authenticated_via', 'kemenag_sso');

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Berhasil masuk melalui SSO Kemenag.');
        } catch (Throwable $exception) {
            Log::warning('Kemenag SIMPEG SSO login failed', [
                'message' => $exception->getMessage(),
                'exception' => get_class($exception),
            ]);

            return redirect()->route('admin.login')
                ->with('error', $this->safeErrorMessage($exception));
        } finally {
            $request->session()->forget([
                'kemenag_sso_login_started_at',
                'kemenag_sso_login_attempt',
            ]);
        }
    }

    private function validateLoginAttempt(Request $request): void
    {
        $startedAt = (int) $request->session()->get('kemenag_sso_login_started_at', 0);
        $attempt = (string) $request->session()->get('kemenag_sso_login_attempt', '');
        $ttl = max(60, (int) config('kemenag-sso.login_attempt_ttl', 600));

        if ($startedAt === 0 || $attempt === '' || now()->timestamp - $startedAt > $ttl) {
            throw new RuntimeException('Sesi login SSO tidak ditemukan atau sudah kedaluwarsa.');
        }
    }

    private function verifyToken(string $token): array
    {
        $method = strtoupper((string) config('kemenag-sso.verify_method', 'POST'));
        if (! in_array($method, ['GET', 'POST'], true)) {
            throw new RuntimeException('Metode verifikasi SSO harus GET atau POST.');
        }

        $response = $this->http()
            ->acceptJson()
            ->withToken($token)
            ->send($method, (string) config('kemenag-sso.verify_url'));

        if (! $response->successful()) {
            throw new RuntimeException('Server verifikasi SSO merespons HTTP '.$response->status().'.');
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('Respons verifikasi SSO bukan JSON yang valid.');
        }

        $status = data_get($payload, 'status');
        if ($status !== null && (string) $status !== '200') {
            throw new RuntimeException('Token SSO ditolak oleh server Kemenag.');
        }

        $pegawai = data_get($payload, 'pegawai')
            ?? data_get($payload, 'data.pegawai')
            ?? data_get($payload, 'data');

        if (! is_array($pegawai) || $this->firstValue($pegawai, ['NIP', 'nip']) === '') {
            throw new RuntimeException('Data pegawai atau NIP tidak ditemukan pada respons SSO.');
        }

        return $pegawai;
    }

    private function resolveUser(array $pegawai): User
    {
        $nip = $this->normalizeNip($this->firstValue($pegawai, ['NIP', 'nip', 'NIP_LAMA', 'nip_lama']));
        if ($nip === '') {
            throw new RuntimeException('NIP dari SSO tidak valid.');
        }

        $name = $this->firstValue($pegawai, ['NAMA', 'nama', 'NAMA_LENGKAP', 'nama_lengkap']);
        $email = strtolower($this->firstValue($pegawai, ['EMAIL', 'email', 'EMAIL_KANTOR', 'email_kantor']));
        $unit = $this->firstValue($pegawai, [
            'SATKER_1',
            'satker_1',
            'SATUAN_KERJA',
            'satuan_kerja',
            'KETERANGAN_SATUAN_KERJA',
            'keterangan_satuan_kerja',
            'SATKER_2',
            'satker_2',
        ]);
        $avatar = $this->firstValue($pegawai, ['PHOTO', 'photo', 'FOTO', 'foto']);

        $staff = Staff::query()->where('nip', $nip)->first();
        if (config('kemenag-sso.require_staff_match') && (! $staff || ! $staff->active)) {
            throw new RuntimeException('NIP belum terdaftar sebagai GTK aktif pada website.');
        }

        $candidates = $this->userCandidates($nip, $email, $staff);
        if ($candidates->count() > 1) {
            throw new RuntimeException('NIP atau email terhubung ke lebih dari satu akun. Hubungi administrator.');
        }

        $user = $candidates->first();
        $alreadyLinkedToSso = $user
            && $user->usesSso()
            && hash_equals((string) $user->provider_id, $nip);

        if ($user && $user->isAdmin() && ! $alreadyLinkedToSso) {
            throw new RuntimeException('Akun administrator tidak dapat ditautkan ke SSO secara otomatis.');
        }

        if (! $user && ! config('kemenag-sso.auto_provision')) {
            throw new RuntimeException('Akun SSO belum didaftarkan oleh administrator.');
        }

        if (! $user) {
            $user = new User([
                'role' => 'author',
                'active' => true,
                'password' => Hash::make(Str::random(64)),
                'auth_provider' => 'kemenag_sso',
            ]);
        } elseif ($user->auth_provider === 'local') {
            $user->auth_provider = 'local_kemenag_sso';
        }

        $resolvedEmail = $this->resolveEmail($email, $nip, $user);
        $user->fill([
            'staff_id' => $staff?->id,
            'name' => $name !== '' ? $name : ($staff?->name ?: $user->name ?: 'Pegawai Kemenag'),
            'email' => $resolvedEmail,
            'provider_id' => $nip,
            'nip' => $nip,
            'unit_name' => $unit !== '' ? $unit : ($user->unit_name ?: $staff?->subject),
            'avatar' => $avatar !== '' ? $avatar : $user->avatar,
            'last_login_at' => now(),
        ]);
        $user->save();

        return $user;
    }

    private function userCandidates(string $nip, string $email, ?Staff $staff): Collection
    {
        if (! config('kemenag-sso.auto_link_by_nip')) {
            return User::query()
                ->whereIn('auth_provider', ['kemenag_sso', 'local_kemenag_sso'])
                ->where('provider_id', $nip)
                ->get();
        }

        return User::query()
            ->where(function ($query) use ($nip, $email, $staff) {
                $query->where(function ($providerQuery) use ($nip) {
                    $providerQuery
                        ->whereIn('auth_provider', ['kemenag_sso', 'local_kemenag_sso'])
                        ->where('provider_id', $nip);
                })->orWhere('nip', $nip);

                if ($staff) {
                    $query->orWhere('staff_id', $staff->id);
                }

                if ($this->isUsableEmail($email)) {
                    $query->orWhereRaw('LOWER(email) = ?', [$email]);
                }
            })
            ->get()
            ->unique('id')
            ->values();
    }

    private function resolveEmail(string $email, string $nip, User $user): string
    {
        if ($this->isUsableEmail($email)) {
            $emailOwner = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->when($user->exists, fn ($query) => $query->whereKeyNot($user->getKey()))
                ->exists();

            if (! $emailOwner) {
                return $email;
            }
        }

        if ($user->exists && $this->isUsableEmail((string) $user->email)) {
            return strtolower((string) $user->email);
        }

        return 'sso.'.$nip.'@users.invalid';
    }

    private function isUsableEmail(string $email): bool
    {
        return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function normalizeNip(string $nip): string
    {
        return preg_replace('/\D+/', '', $nip) ?: '';
    }

    private function firstValue(array $data, array $keys): string
    {
        foreach ($keys as $key) {
            $value = trim((string) data_get($data, $key));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function ensureConfigured(): void
    {
        foreach (['app_id', 'signin_url', 'verify_url'] as $key) {
            if (blank(config('kemenag-sso.'.$key))) {
                throw new RuntimeException('Konfigurasi KEMENAG_SSO_'.strtoupper($key).' belum diisi.');
            }
        }
    }

    private function safeErrorMessage(Throwable $exception): string
    {
        $allowedMessages = [
            'Sesi login SSO tidak ditemukan atau sudah kedaluwarsa.',
            'Token callback SSO tidak diterima.',
            'NIP belum terdaftar sebagai GTK aktif pada website.',
            'NIP atau email terhubung ke lebih dari satu akun. Hubungi administrator.',
            'Akun administrator tidak dapat ditautkan ke SSO secara otomatis.',
            'Akun SSO belum didaftarkan oleh administrator.',
        ];

        return in_array($exception->getMessage(), $allowedMessages, true)
            ? $exception->getMessage()
            : 'Login SSO belum berhasil. Periksa konfigurasi atau hubungi administrator aplikasi.';
    }

    private function http(): PendingRequest
    {
        return Http::timeout((int) config('kemenag-sso.http.timeout', 15))
            ->withOptions(['verify' => config('kemenag-sso.http.verify_ssl', true)]);
    }
}
