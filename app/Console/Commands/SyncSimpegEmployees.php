<?php

namespace App\Console\Commands;

use App\Services\SimpegSynchronizer;
use Illuminate\Console\Command;
use Throwable;

class SyncSimpegEmployees extends Command
{
    protected $signature = 'simpeg:sync';

    protected $description = 'Sinkronkan pegawai SIMPEG untuk kode satuan kerja yang dikunci pada konfigurasi';

    public function handle(SimpegSynchronizer $synchronizer): int
    {
        $this->info('Memulai sinkronisasi SIMPEG untuk KODE_SATKER_2 '.config('simpeg.satker_2_code').'...');

        try {
            $log = $synchronizer->sync();
        } catch (Throwable $exception) {
            $this->error('Sinkronisasi gagal: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Dilaporkan', 'Diambil', 'Sesuai', 'Baru', 'Diperbarui', 'Dilewati', 'GTK Baru', 'GTK Diperbarui'],
            [[
                $log->total_reported,
                $log->fetched,
                $log->matched,
                $log->inserted,
                $log->updated,
                $log->skipped,
                $log->staff_created,
                $log->staff_updated,
            ]]
        );

        return self::SUCCESS;
    }
}
