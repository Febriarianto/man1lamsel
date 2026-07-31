<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SimpegSynchronizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SimpegSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config([
            'simpeg.base_url' => 'https://api.kemenag.go.id/v1',
            'simpeg.email' => 'api-user@example.test',
            'simpeg.password' => 'test-password',
            'simpeg.satker_code' => '02090325000000',
            'simpeg.page_size' => 400,
            'simpeg.sync_staff' => true,
        ]);
    }

    public function test_sync_filters_satker_upserts_employee_and_creates_staff(): void
    {
        $this->fakeApi();

        $log = app(SimpegSynchronizer::class)->sync();

        $this->assertSame('completed', $log->status);
        $this->assertSame(2, $log->fetched);
        $this->assertSame(1, $log->matched);
        $this->assertSame(1, $log->inserted);
        $this->assertSame(1, $log->skipped);
        $this->assertSame(1, $log->staff_created);
        $this->assertDatabaseCount('simpeg_employees', 1);
        $this->assertDatabaseHas('simpeg_employees', [
            'identity_nip' => '198801012010011001',
            'kode_satuan_kerja' => '02090325000000',
            'nama_lengkap' => 'Guru SIMPEG, S.Pd.',
        ]);
        $this->assertDatabaseMissing('simpeg_employees', [
            'identity_nip' => '199901012020012002',
        ]);
        $this->assertDatabaseHas('staff', [
            'nip' => '198801012010011001',
            'type' => 'guru',
            'active' => true,
        ]);

        Http::assertSent(function ($request): bool {
            if ($request->url() !== 'https://api.kemenag.go.id/v1/pegawai') {
                return true;
            }

            $parts = collect($request->data())->keyBy('name');

            return $request->hasHeader('Authorization', 'Bearer test-token')
                && (string) data_get($parts->get('satker'), 'contents') === '02090325000000'
                && (int) data_get($parts->get('start'), 'contents') === 0
                && (int) data_get($parts->get('limit'), 'contents') === 400;
        });
    }

    public function test_repeated_sync_updates_existing_employee_and_staff(): void
    {
        $this->fakeApi();
        app(SimpegSynchronizer::class)->sync();

        $secondLog = app(SimpegSynchronizer::class)->sync();

        $this->assertSame(0, $secondLog->inserted);
        $this->assertSame(1, $secondLog->updated);
        $this->assertSame(0, $secondLog->staff_created);
        $this->assertSame(1, $secondLog->staff_updated);
        $this->assertDatabaseCount('simpeg_employees', 1);
        $this->assertDatabaseCount('staff', 1);
    }

    public function test_admin_can_open_simpeg_page_with_locked_satker_code(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
            'auth_provider' => 'local',
            'active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.simpeg.index'))
            ->assertOk()
            ->assertSee('Sinkronisasi Pegawai SIMPEG')
            ->assertSee('02090325000000');
    }

    private function fakeApi(string $name = 'Guru SIMPEG, S.Pd.'): void
    {
        Http::fake([
            'https://api.kemenag.go.id/v1/auth/login' => Http::response([
                'token' => 'test-token',
            ]),
            'https://api.kemenag.go.id/v1/pegawai' => Http::response([
                'total' => 2,
                'data' => [
                    [
                        'NIP' => '19880101 201001 1 001',
                        'NIP_BARU' => '198801012010011001',
                        'NAMA' => 'Guru SIMPEG',
                        'NAMA_LENGKAP' => $name,
                        'KODE_SATUAN_KERJA' => '02090325000000',
                        'SATKER_1' => 'MAN 1 Lampung Selatan',
                        'TAMPIL_JABATAN' => 'Guru Ahli Muda',
                        'EMAIL' => 'guru@kemenag.go.id',
                        'STATUS_PEGAWAI' => 'PNS',
                    ],
                    [
                        'NIP_BARU' => '199901012020012002',
                        'NAMA_LENGKAP' => 'Pegawai Satker Lain',
                        'KODE_SATUAN_KERJA' => '02090326000000',
                        'TAMPIL_JABATAN' => 'Pelaksana',
                    ],
                ],
            ]),
        ]);
    }
}
