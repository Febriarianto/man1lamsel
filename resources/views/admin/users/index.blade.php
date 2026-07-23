@extends('admin.layout')
@section('title','Pengguna')
@section('page_title','Pengguna & Penulis')
@section('page_subtitle','Kelola akun administrator, penulis manual, dan pengguna SSO Kemenag')
@section('page_actions')<a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Tambah Penulis Manual</a>@endsection
@section('content')
@if(!config('kemenag-sso.enabled'))<div class="alert alert-light border"><i class="bi bi-info-circle me-2"></i>Penulis dapat masuk menggunakan akun manual yang dibuat administrator. SSO Kemenag saat ini belum diaktifkan.</div>@else<div class="alert alert-success"><i class="bi bi-shield-check me-2"></i>Login manual dan SSO Kemenag aktif. Pengguna SSO baru akan dibuat otomatis sebagai <strong>Penulis Artikel</strong> saat login pertama.</div>@endif
<div class="admin-card">
    <form class="filter-bar" method="get">
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, NIP, atau unit kerja...">
        <select class="form-select" name="role"><option value="">Semua role</option><option value="admin" @selected(request('role')==='admin')>Administrator</option><option value="author" @selected(request('role')==='author')>Penulis</option></select>
        <select class="form-select" name="provider"><option value="">Semua login</option><option value="local" @selected(request('provider')==='local')>Lokal</option><option value="kemenag_sso" @selected(request('provider')==='kemenag_sso')>SSO Kemenag</option></select>
        <button class="btn btn-dark">Filter</button><a href="{{ route('admin.users.index') }}" class="btn btn-light">Reset</a>
    </form>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Pengguna</th><th>Identitas</th><th>Login</th><th>Role</th><th>Status</th><th>Login Terakhir</th><th class="text-end">Aksi</th></tr></thead><tbody>
    @forelse($users as $user)
    <tr>
        <td><div class="d-flex align-items-center gap-2"><span class="avatar">{{ strtoupper(substr($user->name,0,1)) }}</span><div class="table-title"><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></div></div></td>
        <td><div class="table-title"><strong>{{ $user->nip ?: '-' }}</strong><small>{{ $user->unit_name ?: 'Unit belum tersedia' }}</small></div></td>
        <td><span class="badge bg-light text-dark">{{ $user->auth_provider==='kemenag_sso'?'SSO Kemenag':'Lokal' }}</span></td>
        <td>{{ $user->role==='admin'?'Administrator':'Penulis' }}</td>
        <td><span class="status-dot {{ $user->active?'published':'draft' }}">{{ $user->active?'Aktif':'Nonaktif' }}</span></td>
        <td>{{ optional($user->last_login_at)->format('d/m/Y H:i') ?: '-' }}</td>
        <td class="text-end"><a href="{{ route('admin.users.edit',$user) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i></a></td>
    </tr>
    @empty<tr><td colspan="7" class="text-center py-5 text-secondary">Belum ada pengguna.</td></tr>@endforelse
    </tbody></table></div><div class="p-3">{{ $users->links() }}</div>
</div>
@endsection
