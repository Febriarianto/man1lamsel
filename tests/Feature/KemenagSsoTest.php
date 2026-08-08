<?php

namespace Tests\Feature;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KemenagSsoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'kemenag-sso.enabled' => true,
            'kemenag-sso.app_id' => 'app-man-1-lamsel',
            'kemenag-sso.signin_url' => 'https://sso.kemenag.go.id/auth/signin',
            'kemenag-sso.verify_url' => 'https://sso.kemenag.go.id/auth/verify',
            'kemenag-sso.signout_url' => 'https://sso.kemenag.go.id/auth/signout',
            'kemenag-sso.verify_method' => 'POST',
            'kemenag-sso.callback_token_parameter' => 'token',
            'kemenag-sso.auto_provision' => true,
            'kemenag-sso.require_staff_match' => true,
            'kemenag-sso.auto_link_by_nip' => true,
        ]);
    }

    public function test_redirect_uses_official_signin_url_and_app_id(): void
    {
        $this->get(route('admin.sso.redirect'))
            ->assertRedirect('https://sso.kemenag.go.id/auth/signin?appid=app-man-1-lamsel')
            ->assertSessionHas('kemenag_sso_login_attempt');
    }

    public function test_redirect_returns_to_login_when_app_id_is_empty(): void
    {
        config(['kemenag-sso.app_id' => null]);

        $this->get(route('admin.sso.redirect'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('error', 'APP ID SSO Kemenag belum diisi oleh administrator.');
    }

    public function test_callback_creates_author_and_links_staff_by_nip(): void
    {
        $staff = $this->createStaff('198801012010011001');
        $this->fakeSuccessfulVerification();

        $response = $this->withSession($this->ssoAttemptSession())
            ->get(route('admin.sso.callback', ['token' => 'token-rahasia']));

        $response->assertRedirect(route('admin.dashboard'));
        $user = User::query()->sole();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('author', $user->role);
        $this->assertSame('kemenag_sso', $user->auth_provider);
        $this->assertSame('198801012010011001', $user->provider_id);
        $this->assertSame('198801012010011001', $user->nip);
        $this->assertSame($staff->id, $user->staff_id);
        $this->assertSame('Guru SSO', $user->name);
        $this->assertSame('https://example.test/guru.jpg', $user->avatar);

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'https://sso.kemenag.go.id/auth/verify'
            && $request->hasHeader('Authorization', 'Bearer token-rahasia')
        );
    }

    public function test_existing_local_author_becomes_hybrid_and_can_still_login_manually(): void
    {
        $staff = $this->createStaff('198801012010011001');
        $user = User::query()->create([
            'staff_id' => $staff->id,
            'name' => 'Guru Lokal',
            'email' => 'guru@mansalase.sch.id',
            'password' => 'password-rahasia',
            'role' => 'author',
            'auth_provider' => 'local',
            'nip' => '198801012010011001',
            'active' => true,
        ]);
        $this->fakeSuccessfulVerification('guru@mansalase.sch.id');

        $this->withSession($this->ssoAttemptSession())
            ->get(route('admin.sso.callback', ['token' => 'token-rahasia']))
            ->assertRedirect(route('admin.dashboard'));

        $user->refresh();
        $this->assertSame('local_kemenag_sso', $user->auth_provider);
        $this->assertSame('198801012010011001', $user->provider_id);

        $this->post(route('admin.logout'))
            ->assertRedirect('https://sso.kemenag.go.id/auth/signout');

        $this->post(route('admin.login.store'), [
            'email' => 'guru@mansalase.sch.id',
            'password' => 'password-rahasia',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_callback_rejects_nip_not_registered_as_active_staff(): void
    {
        $this->fakeSuccessfulVerification();

        $this->withSession($this->ssoAttemptSession())
            ->get(route('admin.sso.callback', ['token' => 'token-rahasia']))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('error', 'NIP belum terdaftar sebagai GTK aktif pada website.');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_callback_accepts_response_with_only_nip_baru(): void
    {
        $staff = $this->createStaff('198801012010011001');
        $this->fakeSuccessfulVerification(includeOldNip: false);

        $this->withSession($this->ssoAttemptSession())
            ->get(route('admin.sso.callback', ['token' => 'token-rahasia']))
            ->assertRedirect(route('admin.dashboard'));

        $user = User::query()->sole();
        $this->assertSame('198801012010011001', $user->nip);
        $this->assertSame('198801012010011001', $user->provider_id);
        $this->assertSame($staff->id, $user->staff_id);
    }

    public function test_callback_ignores_base64_photo_that_does_not_fit_avatar_column(): void
    {
        $staff = $this->createStaff('198801012010011001');
        $this->fakeSuccessfulVerification(
            photo: 'data:image/jpeg;base64.'.str_repeat('A', 600)
        );

        $this->withSession($this->ssoAttemptSession())
            ->get(route('admin.sso.callback', ['token' => 'token-rahasia']))
            ->assertRedirect(route('admin.dashboard'));

        $user = User::query()->sole();

        $this->assertAuthenticatedAs($user);
        $this->assertSame($staff->id, $user->staff_id);
        $this->assertNull($user->avatar);
    }

    private function createStaff(string $nip): Staff
    {
        return Staff::query()->create([
            'name' => 'Guru SSO',
            'nip' => $nip,
            'slug' => 'guru_sso',
            'position' => 'Guru',
            'subject' => 'MAN 1 Lampung Selatan',
            'type' => 'guru',
            'sort_order' => 1,
            'active' => true,
        ]);
    }

    private function fakeSuccessfulVerification(
        string $email = 'guru.sso@kemenag.go.id',
        bool $includeOldNip = true,
        string $photo = 'https://example.test/guru.jpg',
    ): void {
        $pegawai = [
            'NIP_BARU' => '198801012010011001',
            'NAMA' => 'Guru SSO',
            'EMAIL' => $email,
            'SATKER_1' => 'MAN 1 Lampung Selatan',
            'PHOTO' => $photo,
        ];

        if ($includeOldNip) {
            $pegawai['NIP'] = '150413807';
        }

        Http::fake([
            'https://sso.kemenag.go.id/auth/verify' => Http::response([
                'status' => 200,
                'pegawai' => $pegawai,
            ]),
        ]);
    }

    private function ssoAttemptSession(): array
    {
        return [
            'kemenag_sso_login_started_at' => now()->timestamp,
            'kemenag_sso_login_attempt' => 'attempt-id',
        ];
    }
}
