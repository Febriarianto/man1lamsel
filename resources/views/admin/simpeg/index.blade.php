@extends('admin.layout')
@php($configured = filled(config('simpeg.email')) && filled(config('simpeg.password')))
@section('title', 'Sinkroninasi SIMPEG')
@section('page_title', 'Sinkronisasi Data SIMPEG')
@section('page_subtitle', 'Mengambil pegawai dari satker API '.config('simpeg.request_satker_code').' dan menyimpan khusus KODE_SATKER_2 '.config('simpeg.satker_2_code'))
@section('page_actions')
<form method="post" action="{{ route('admin.simpeg.sync') }}">
    @csrf
    <button class="btn btn-primary" @disabled(!$configured) data-confirm="Mulai sinkronisasi data pegawai dari SIMPEG? Proses dapat memerlukan beberapa menit.">
        <i class="bi bi-arrow-repeat me-1"></i> Mulai Sinkronisasi
    </button>
</form>
@endsection
@section('content')
@if(!$configured)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Kredensial belum diisi. Tambahkan <code>SIMPEG_API_EMAIL</code> dan <code>SIMPEG_API_PASSWORD</code> pada file <code>.env</code>, lalu jalankan <code>php artisan optimize:clear</code>.
    </div>
@endif
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="admin-card p-3 h-100">
            <small class="text-secondary d-block">Filter KODE_SATKER_2</small>
            <strong class="fs-5">{{ config('simpeg.satker_2_code') }}</strong>
            <small class="text-secondary d-block mt-1">Request API: {{ config('simpeg.request_satker_code') }}</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card p-3 h-100">
            <small class="text-secondary d-block">Pegawai Tersimpan</small>
            <strong class="fs-5">{{ number_format($employees->total(), 0, ',', '.') }}</strong>
            <small class="text-secondary d-block mt-1">Hanya data yang lolos filter satker</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card p-3 h-100">
            <small class="text-secondary d-block">Sinkronisasi GTK</small>
            <strong class="fs-5">{{ config('simpeg.sync_staff') ? 'Aktif' : 'Nonaktif' }}</strong>
            <small class="text-secondary d-block mt-1">NIP dapat langsung digunakan oleh SSO</small>
        </div>
    </div>
</div>

<div class="admin-card mb-4">
    <form class="filter-bar" method="get">
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIP, atau jabatan...">
        <select class="form-select" name="status">
            <option value="">Semua status pegawai</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <button class="btn btn-dark">Cari</button>
        <a href="{{ route('admin.simpeg.index') }}" class="btn btn-light">Reset</a>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Nama | NIP</th><th>Jabatan</th><th>Unit Kerja 1</th><th>Unit Kerja 2</th><th>Sinkronisasi</th></tr></thead>
            <tbody>
            @forelse($employees as $employee)
                <tr>
                    <td>
                        <div class="table-title">
                            <strong>{{ $employee->nama_lengkap ?: $employee->nama ?: '-' }}</strong>
                            <small>{{ $employee->nip_baru ?: $employee->nip ?: '-' }}</small>
                            <small class="d-block text-secondary">{{ $employee->email ?: 'Email belum tersedia' }}</small>
                         <small class="d-block text-secondary">{{ $employee->no_hp ?: $employee->no_hp ?: '-' }}</small>
                        </div>
                    </td>
                    <td>
                        <strong>{{ $employee->status_pegawai ?: '-' }} | <small>{{ $employee->tampil_jabatan ?: $employee->level_jabatan ?: '-' }}</small></strong>
                        <small class="d-block text-secondary">{{ collect([$employee->pangkat, $employee->gol_ruang])->filter()->join(' ') ?: 'Pangkat/golongan belum tersedia' }}</small>
                        <small class="d-block text-secondary">{{ $employee->pendidikan ?: 'Pendidikan belum tersedia' }}</small>
                    </td>
                    <td><small>{{ $employee->satker_1 ?:  ' - ' }}</small></td>                    
                    <td><small>{{ $employee->satker_2?:  ' - ' }}</small></td> 
                    <td><small>{{ optional($employee->synced_at)->format('d/m/Y H:i') ?: '-' }}</small></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-5 text-secondary">Belum ada data SIMPEG yang sesuai filter atau pencarian.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $employees->links() }}</div>
</div>

<div class="admin-card">
    <div class="p-3 border-bottom"><h5 class="mb-0">Riwayat Sinkronisasi</h5></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Waktu</th><th>Admin</th><th>Status</th><th>Sesuai Filter</th><th>Baru / Diperbarui</th><th>GTK</th><th>Keterangan</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ optional($log->started_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $log->user?->name ?: 'CLI/Sistem' }}</td>
                    <td>
                        <span class="status-dot {{ $log->status === 'completed' ? 'published' : 'draft' }}">
                            {{ match($log->status) { 'completed' => 'Selesai', 'failed' => 'Gagal', default => 'Berjalan' } }}
                        </span>
                    </td>
                    <td>{{ $log->matched }} <small class="text-secondary">({{ $log->skipped }} dilewati)</small></td>
                    <td>{{ $log->inserted }} / {{ $log->updated }}</td>
                    <td>{{ $log->staff_created }} / {{ $log->staff_updated }}</td>
                    <td><small class="text-secondary">{{ $log->error_message ?: '-' }}</small></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-4 text-secondary">Belum ada riwayat sinkronisasi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
