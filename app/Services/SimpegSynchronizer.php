<?php

namespace App\Services;

use App\Models\SimpegEmployee;
use App\Models\SimpegSyncLog;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SimpegSynchronizer
{
    public function __construct(private readonly SimpegApiClient $client) {}

    public function sync(?int $userId = null): SimpegSyncLog
    {
        $lock = Cache::lock('simpeg-employees-sync', 900);
        if (! $lock->get()) {
            throw new LockTimeoutException('Sinkronisasi SIMPEG lain masih berjalan.');
        }

        try {
            return $this->runSync($userId);
        } finally {
            $lock->release();
        }
    }

    public function repairStaffFromStoredData(): array
    {
        $lock = Cache::lock('simpeg-employees-sync', 900);
        if (! $lock->get()) {
            throw new LockTimeoutException('Sinkronisasi SIMPEG lain masih berjalan.');
        }

        $stats = ['processed' => 0, 'created' => 0, 'updated' => 0];

        try {
            SimpegEmployee::query()
                ->where('kode_satker_2', (string) config('simpeg.satker_2_code'))
                ->orderBy('id')
                ->each(function (SimpegEmployee $employee) use (&$stats): void {
                    $result = DB::transaction(fn (): string => $this->syncStaff($employee));
                    $stats['processed']++;
                    $stats[$result]++;
                });

            return $stats;
        } finally {
            $lock->release();
        }
    }

    private function runSync(?int $userId): SimpegSyncLog
    {
        $satkerCode = $this->normalizeCode((string) config('simpeg.satker_2_code'));
        if ($satkerCode === '') {
            throw new RuntimeException('Filter KODE_SATKER_2 SIMPEG belum diisi.');
        }

        $log = SimpegSyncLog::query()->create([
            'user_id' => $userId,
            'satker_code' => $satkerCode,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $token = $this->client->login();
            $pageSize = (int) config('simpeg.page_size', 400);
            $start = 0;
            $totalReported = null;

            do {
                $batch = $this->client->employees($start, $pageSize, $token);
                $totalReported ??= $batch['total'];
                $rows = $batch['data'];

                $batchStats = $this->storeBatch($rows, $satkerCode);
                foreach ($batchStats as $field => $increment) {
                    $log->{$field} += $increment;
                }

                $log->total_reported = $totalReported;
                $log->save();

                $received = count($rows);
                $start += $pageSize;
                $hasMore = $totalReported > 0
                    ? $start < $totalReported
                    : $received === $pageSize;
            } while ($hasMore && $start < 100000);

            $log->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return $log->refresh();
        } catch (Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 1000),
                'completed_at' => now(),
            ]);

            throw $exception;
        }
    }

    private function storeBatch(array $rows, string $satkerCode): array
    {
        $stats = [
            'fetched' => count($rows),
            'matched' => 0,
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'staff_created' => 0,
            'staff_updated' => 0,
        ];

        DB::transaction(function () use ($rows, $satkerCode, &$stats): void {
            foreach ($rows as $row) {
                $mapped = $this->mapEmployee($row, $satkerCode);
                if ($mapped === null) {
                    $stats['skipped']++;

                    continue;
                }

                $stats['matched']++;
                $employee = SimpegEmployee::query()
                    ->where('identity_nip', $mapped['identity_nip'])
                    ->first();

                if ($employee) {
                    $employee->update($mapped);
                    $stats['updated']++;
                } else {
                    $employee = SimpegEmployee::query()->create($mapped);
                    $stats['inserted']++;
                }

                if (config('simpeg.sync_staff')) {
                    $staffResult = $this->syncStaff($employee);
                    $stats[$staffResult === 'created' ? 'staff_created' : 'staff_updated']++;
                }
            }
        });

        return $stats;
    }

    private function mapEmployee(array $row, string $satkerCode): ?array
    {
        $rowSatker = $this->normalizeCode($this->value($row, [
            'KODE_SATKER_2',
            'kode_satker_2',
        ]));

        if ($rowSatker !== $satkerCode) {
            return null;
        }

        $nip = $this->normalizeNip($this->value($row, ['NIP', 'nip']));
        $nipBaru = $this->normalizeNip($this->value($row, ['NIP_BARU', 'nip_baru']));
        $identityNip = $nipBaru ?: $nip;
        if ($identityNip === '') {
            return null;
        }

        return [
            'identity_nip' => $identityNip,
            'nip' => $nip ?: null,
            'nip_baru' => $nipBaru ?: null,
            'nama' => $this->nullable($this->value($row, ['NAMA', 'nama'])),
            'nama_lengkap' => $this->nullable($this->value($row, ['NAMA_LENGKAP', 'nama_lengkap'])),
            'agama' => $this->nullable($this->value($row, ['AGAMA', 'agama'])),
            'tempat_lahir' => $this->nullable($this->value($row, ['TEMPAT_LAHIR', 'tempat_lahir'])),
            'tanggal_lahir' => $this->date($this->value($row, ['TANGGAL_LAHIR', 'tanggal_lahir'])),
            'jenis_kelamin' => $this->nullable($this->value($row, ['JENIS_KELAMIN', 'jenis_kelamin'])),
            'pendidikan' => $this->nullable($this->value($row, ['PENDIDIKAN', 'pendidikan'])),
            'kode_level_jabatan' => $this->nullable($this->value($row, ['KODE_LEVEL_JABATAN', 'kode_level_jabatan'])),
            'level_jabatan' => $this->nullable($this->value($row, ['LEVEL_JABATAN', 'level_jabatan'])),
            'pangkat' => $this->nullable($this->value($row, ['PANGKAT', 'pangkat'])),
            'gol_ruang' => $this->nullable($this->value($row, ['GOL_RUANG', 'gol_ruang'])),
            'tmt_cpns' => $this->date($this->value($row, ['TMT_CPNS', 'tmt_cpns'])),
            'tmt_pangkat' => $this->date($this->value($row, ['TMT_PANGKAT', 'tmt_pangkat'])),
            'mk_tahun' => $this->unsignedInt($this->value($row, ['MASAKERJA_TAHUN', 'MK_TAHUN', 'masakerja_tahun'])),
            'mk_bulan' => $this->unsignedInt($this->value($row, ['MASAKERJA_BULAN', 'MK_BULAN', 'masakerja_bulan'])),
            'tipe_jabatan' => $this->nullable($this->value($row, ['TIPE_JABATAN', 'tipe_jabatan'])),
            'kode_jabatan' => $this->nullable($this->value($row, ['KODE_JABATAN', 'kode_jabatan'])),
            'tampil_jabatan' => $this->nullable($this->value($row, ['TAMPIL_JABATAN', 'tampil_jabatan'])),
            'tmt_jabatan' => $this->date($this->value($row, ['TMT_JABATAN', 'tmt_jabatan'])),
            // Kolom lama dipertahankan untuk kompatibilitas data dan laporan.
            // Nilainya sekarang selalu mewakili KODE_SATKER_2 yang lolos filter.
            'kode_satuan_kerja' => $rowSatker,
            'satker_1' => $this->nullable($this->value($row, ['SATKER_1', 'satker_1'])),
            'kode_satker_2' => $rowSatker,
            'satker_2' => $this->nullable($this->value($row, ['SATKER_2', 'satker_2'])),
            'kode_satker_3' => $this->nullable($this->value($row, ['KODE_SATKER_3', 'kode_satker_3'])),
            'satker_3' => $this->nullable($this->value($row, ['SATKER_3', 'satker_3'])),
            'kode_satker_4' => $this->nullable($this->value($row, ['KODE_SATKER_4', 'kode_satker_4'])),
            'satker_4' => $this->nullable($this->value($row, ['SATKER_4', 'satker_4'])),
            'kode_satker_5' => $this->nullable($this->value($row, ['KODE_SATKER_5', 'kode_satker_5'])),
            'satker_5' => $this->nullable($this->value($row, ['SATKER_5', 'satker_5'])),
            'status_kawin' => $this->nullable($this->value($row, ['STATUS_KAWIN', 'status_kawin'])),
            'alamat_1' => $this->nullable($this->value($row, ['ALAMAT_1', 'alamat_1'])),
            'alamat_2' => $this->nullable($this->value($row, ['ALAMAT_2', 'alamat_2'])),
            'telepon' => $this->nullable($this->value($row, ['TELEPON', 'telepon'])),
            'kab_kota' => $this->nullable($this->value($row, ['KAB_KOTA', 'kab_kota'])),
            'provinsi' => $this->nullable($this->value($row, ['PROVINSI', 'provinsi'])),
            'kode_pos' => $this->nullable($this->value($row, ['KODE_POS', 'kode_pos'])),
            'kode_lokasi' => $this->nullable($this->value($row, ['KODE_LOKASI', 'kode_lokasi'])),
            'kode_pangkat' => $this->nullable($this->value($row, ['KODE_PANGKAT', 'kode_pangkat'])),
            'no_hp' => $this->nullable($this->value($row, ['NO_HP', 'no_hp'])),
            'email' => $this->nullable(strtolower($this->value($row, ['EMAIL', 'email']))),
            'tmt_pangkat_yad' => $this->date($this->value($row, ['TMT_PANGKAT_YAD', 'tmt_pangkat_yad'])),
            'tmt_kgb_yad' => $this->date($this->value($row, ['TMT_KGB_YAD', 'tmt_kgb_yad'])),
            'tmt_pensiun' => $this->date($this->value($row, ['TMT_PENSIUN', 'tmt_pensiun'])),
            'kode_kua' => $this->nullable($this->value($row, ['KODE_KUA', 'kode_kua'])),
            'nsm' => $this->nullable($this->value($row, ['NSM', 'nsm'])),
            'npsn' => $this->nullable($this->value($row, ['NPSN', 'npsn'])),
            'status_pegawai' => $this->nullable($this->value($row, ['STATUS_PEGAWAI', 'status_pegawai'])),
            'synced_at' => now(),
            'source_payload' => $row,
        ];
    }

    private function syncStaff(SimpegEmployee $employee): string
    {
        $nip = $employee->identity_nip;
        $name = trim((string) ($employee->nama_lengkap ?: $employee->nama));
        $name = $name !== '' ? $name : 'Pegawai '.$nip;

        $staff = Staff::query()->where('nip', $nip)->first();
        $legacyStaff = $this->legacyStaffMatch($name, $staff?->id);

        if ($staff && $legacyStaff) {
            $this->mergeLegacyStaff($staff, $legacyStaff);
            $staff->refresh();
        } elseif (! $staff) {
            $staff = $legacyStaff;
        }

        $position = trim((string) (
            $employee->tampil_jabatan
            ?: $employee->level_jabatan
            ?: $employee->tipe_jabatan
            ?: 'Pegawai'
        ));
        $unit = trim((string) (
            $employee->status_pegawai
            ?: $employee->satker_4
            ?: $employee->satker_3
            ?: $employee->satker_2
            ?: $employee->satker_1
        ));

        if ($staff) {
            $staff->update([
                'nip' => $nip,
                'name' => $name,
                'position' => $position,
                'subject' => $unit ?: $staff->subject,
                'active' => true,
            ]);

            return 'updated';
        }

        Staff::query()->create([
            'nip' => $nip,
            'name' => $name,
            'slug' => $this->uniqueStaffSlug($name),
            'position' => $position,
            'subject' => $unit ?: null,
            'type' => $this->staffType($position),
            'photo' => $this->defaultStaffPhoto($nip),
            'sort_order' => ((int) Staff::query()->max('sort_order')) + 1,
            'active' => true,
        ]);

        return 'created';
    }

    private function legacyStaffMatch(string $name, ?int $exceptId = null): ?Staff
    {
        $nameKey = $this->normalizedStaffName($name);
        if ($nameKey === '') {
            return null;
        }

        $matches = Staff::query()
            ->whereNull('nip')
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->get()
            ->filter(fn (Staff $staff): bool => $this->normalizedStaffName($staff->name) === $nameKey)
            ->values();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function mergeLegacyStaff(Staff $target, Staff $legacy): void
    {
        $preserved = [
            'photo' => $target->photo ?: $legacy->photo,
            'bio' => $target->bio ?: $legacy->bio,
            'sort_order' => $legacy->sort_order,
        ];

        $targetUser = $target->user()->first();
        $legacyUser = $legacy->user()->first();

        if (! $targetUser && $legacyUser) {
            $legacyUser->update(['staff_id' => $target->id]);
            $legacyUser = null;
        }

        $target->update($preserved);

        // Dua profil yang sama hanya digabung jika tidak ada dua akun berbeda
        // yang masih bergantung pada kedua baris GTK tersebut.
        if (! $legacyUser) {
            $legacy->delete();
        }
    }

    private function normalizedStaffName(string $name): string
    {
        $name = (string) Str::of($name)
            ->before(',')
            ->ascii()
            ->lower()
            ->replaceMatches('/^(?:(?:prof|dr|drs|hj|h)\.?\s+)+/u', '')
            ->replaceMatches('/[^a-z0-9]+/u', ' ')
            ->squish();

        return trim($name);
    }

    private function defaultStaffPhoto(string $nip): string
    {
        return 'demo/person-'.((abs(crc32($nip)) % 4) + 1).'.svg';
    }

    private function uniqueStaffSlug(string $name): string
    {
        $base = Staff::makeSlug($name) ?: 'pegawai';
        $slug = $base;
        $suffix = 2;

        while (Staff::query()->where('slug', $slug)->exists()) {
            $slug = $base.'_'.$suffix++;
        }

        return $slug;
    }

    private function staffType(string $position): string
    {
        $position = strtolower($position);
        if (str_contains($position, 'kepala madrasah')) {
            return 'kepala';
        }

        return str_contains($position, 'guru') ? 'guru' : 'pegawai';
    }

    private function value(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            $value = data_get($row, $key);
            if ($value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return '';
    }

    private function normalizeNip(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?: '';
    }

    private function normalizeCode(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?: '';
        $targetLength = strlen((string) config('simpeg.satker_2_code'));

        return $digits !== '' && strlen($digits) < $targetLength
            ? str_pad($digits, $targetLength, '0', STR_PAD_LEFT)
            : $digits;
    }

    private function nullable(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }

    private function unsignedInt(string $value): ?int
    {
        return $value !== '' ? max(0, (int) $value) : null;
    }

    private function date(string $value): ?string
    {
        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        try {
            return Carbon::parse(substr($value, 0, 19))->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
