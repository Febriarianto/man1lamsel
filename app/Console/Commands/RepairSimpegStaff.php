<?php

namespace App\Console\Commands;

use App\Services\SimpegSynchronizer;
use Illuminate\Console\Command;
use Throwable;

class RepairSimpegStaff extends Command
{
    protected $signature = 'simpeg:repair-staff';

    protected $description = 'Perbaiki relasi GTK dari data SIMPEG tersimpan sambil mempertahankan foto dan profil lama';

    public function handle(SimpegSynchronizer $synchronizer): int
    {
        try {
            $stats = $synchronizer->repairStaffFromStoredData();

            $this->info('Perbaikan data GTK selesai.');
            $this->table(
                ['Diproses', 'GTK Baru', 'GTK Diperbarui'],
                [[$stats['processed'], $stats['created'], $stats['updated']]],
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Perbaikan data GTK gagal: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
