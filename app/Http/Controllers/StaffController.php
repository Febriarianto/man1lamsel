<?php

namespace App\Http\Controllers;

use App\Models\Staff;

class StaffController extends Controller
{
    public function teachers() { return $this->index('guru'); }
    public function employees() { return $this->index('pegawai'); }

    public function index(string $type = 'guru')
    {
        abort_unless(in_array($type, ['guru', 'pegawai'], true), 404);
              $staff = Staff::query()
            ->leftJoin('simpeg_employees as simpeg', function ($join): void {
                $join->on('staff.nip', '=', 'simpeg.identity_nip')
                    ->where('simpeg.kode_satker_2', '=', (string) config('simpeg.satker_2_code'));
            })
            ->select([
                'staff.*',
                'simpeg.id as simpeg_employee_id',
                'simpeg.status_pegawai as simpeg_status_pegawai',
                'simpeg.pangkat as simpeg_pangkat',
                'simpeg.gol_ruang as simpeg_gol_ruang',
                'simpeg.pendidikan as simpeg_pendidikan',
                'simpeg.email as simpeg_email',
                'simpeg.no_hp as simpeg_no_hp',
                'simpeg.tampil_jabatan as simpeg_jabatan',
                'simpeg.satker_2 as simpeg_satker_2',
                'simpeg.synced_at as simpeg_synced_at',
            ])
            ->with('user')
	    ->where('staff.active', true)
	    ->where('staff.type', $type)
            ->orderBy('staff.sort_order')
            ->orderBy('staff.name')
            ->paginate(16)
            ->withQueryString();
        return view('staff.index', compact('staff', 'type'));
    }
}
