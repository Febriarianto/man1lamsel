@extends('admin.layout')
@section('title','GTK')
@section('page_title','GTK')
@section('page_subtitle','Kelola Kepala Madrasah, guru, pegawai, serta hubungan akun penulis')
@section('page_actions')
<a href="{{ route('admin.staff.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Data</a>
@endsection
@section('content')
<div class="admin-card">
    <form class="filter-bar" method="get">
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIP, jabatan, pangkat, pendidikan, email...">
        <select class="form-select" name="type">
            <option value="">Semua jenis</option>
            <option value="kepala" @selected(request('type')==='kepala' )>Kepala Madrasah</option>
            <option value="guru" @selected(request('type')==='guru' )>Guru</option>
            <option value="pegawai" @selected(request('type')==='pegawai' )>Pegawai</option>
        </select>
        <button class="btn btn-dark">Filter</button>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-light">Reset</a>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>GTK</th>
                    <th>NIP</th>
                    <th>Jabatan & Unit</th>
                    <th>Data Kepegawaian SIMPEG</th>
                    <th>Kontak SIMPEG</th>
                    <th>Akun Penulis</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staff as $member)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @php($img = $member->photo ? (str_starts_with($member->photo, 'demo/') ? asset('images/'.$member->photo) : Storage::url($member->photo)) : asset('images/demo/person-1.svg'))
                            <img src="{{ $img }}" class="table-avatar" alt="{{ $member->name }}">
                            <div class="table-title">
                                <strong>{{ $member->name }}</strong>
                                <small>{{ ucfirst($member->type) }} · {{ $member->simpeg_employee_id ? 'Terhubung SIMPEG' : 'Data lokal' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $member->nip ?: '-' }}</td>
                    <td>
                        {{ $member->simpeg_jabatan ?: $member->position }}
                        <small class="d-block text-secondary">{{ $member->simpeg_satker_2 ?: ($member->subject ?: '-') }}</small>
                    </td>
                    <td>
                        @if($member->simpeg_employee_id)
                        <strong>{{ $member->simpeg_status_pegawai ?: 'Status belum tersedia' }}</strong>
                        <small class="d-block text-secondary">{{ collect([$member->simpeg_pangkat, $member->simpeg_gol_ruang])->filter()->join(' / ') ?: 'Pangkat/golongan belum tersedia' }}</small>
                        <small class="d-block text-secondary">{{ $member->simpeg_pendidikan ?: 'Pendidikan belum tersedia' }}</small>
                        @else
                        <span class="badge bg-light text-secondary">Belum terhubung</span>
                        @endif
                    </td>
                    <td>
                        <span class="d-block">{{ $member->simpeg_email ?: '-' }}</span>
                        <small class="d-block text-secondary">{{ $member->simpeg_no_hp ?: 'No. HP belum tersedia' }}</small>
                    </td>
                    <td>
                        @if($member->user)
                        <span class="badge bg-success-subtle text-success-emphasis">Terhubung</span>
                        <small class="d-block text-secondary">{{ $member->user->email }}</small>
                        @else
                        <span class="badge bg-light text-secondary">Belum terhubung</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-dot {{ $member->active ? 'published' : 'draft' }}">{{ $member->active ? 'Aktif' : 'Nonaktif' }}</span>
                        @if($member->simpeg_synced_at)<small class="d-block text-secondary">Sinkron {{ \Carbon\Carbon::parse($member->simpeg_synced_at)->format('d/m/Y H:i') }}</small>@endif
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('admin.staff.edit', $member) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="{{ route('admin.staff.destroy', $member) }}">@csrf @method('delete')
                                <button data-confirm="Hapus data ini?" class="btn btn-light btn-sm text-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">Belum ada data yang sesuai pencarian.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $staff->links() }}</div>
</div>
@endsection