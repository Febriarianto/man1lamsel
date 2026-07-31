<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimpegEmployee extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tmt_cpns' => 'date',
            'tmt_pangkat' => 'date',
            'tmt_jabatan' => 'date',
            'tmt_pangkat_yad' => 'date',
            'tmt_kgb_yad' => 'date',
            'tmt_pensiun' => 'date',
            'source_payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }
}
