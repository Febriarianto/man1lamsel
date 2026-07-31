<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SimpegEmployee;
use App\Models\SimpegSyncLog;
use App\Services\SimpegSynchronizer;
use Illuminate\Http\Request;
use Throwable;

class SimpegController extends Controller
{
    public function index(Request $request)
    {
        $employees = SimpegEmployee::query()
            ->where('kode_satuan_kerja', (string) config('simpeg.satker_code'))
            ->when($request->filled('q'), fn ($query) => $query->where(function ($search) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $search->where('nama_lengkap', 'like', $term)
                    ->orWhere('nama', 'like', $term)
                    ->orWhere('nip_baru', 'like', $term)
                    ->orWhere('nip', 'like', $term)
                    ->orWhere('tampil_jabatan', 'like', $term);
            }))
            ->when($request->filled('status'), fn ($query) => $query->where('status_pegawai', $request->string('status')))
            ->orderBy('nama_lengkap')
            ->orderBy('nama')
            ->paginate(25)
            ->withQueryString();

        $logs = SimpegSyncLog::query()
            ->with('user')
            ->latest('started_at')
            ->limit(10)
            ->get();

        $statuses = SimpegEmployee::query()
            ->where('kode_satuan_kerja', (string) config('simpeg.satker_code'))
            ->whereNotNull('status_pegawai')
            ->distinct()
            ->orderBy('status_pegawai')
            ->pluck('status_pegawai');

        return view('admin.simpeg.index', compact('employees', 'logs', 'statuses'));
    }

    public function sync(Request $request, SimpegSynchronizer $synchronizer)
    {
        try {
            $log = $synchronizer->sync($request->user()->id);

            return redirect()->route('admin.simpeg.index')->with(
                'success',
                "Sinkronisasi selesai: {$log->matched} pegawai sesuai satker, "
                ."{$log->inserted} baru, {$log->updated} diperbarui, dan {$log->skipped} dilewati."
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('admin.simpeg.index')
                ->with('error', 'Sinkronisasi SIMPEG gagal: '.$exception->getMessage());
        }
    }
}
