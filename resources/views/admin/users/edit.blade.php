@extends('admin.layout')
@section('title','Edit Pengguna')
@section('page_title','Edit Pengguna')
@section('page_subtitle','Atur role dan status akses pengguna CMS')
@section('page_actions')<a href="{{ route('admin.users.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> Kembali</a>@endsection
@section('content')
<form method="post" action="{{ route('admin.users.update',$user) }}">@csrf @method('put')
<div class="admin-card p-4 mw-form">
    <div class="alert alert-light border"><i class="bi bi-shield-check me-2"></i>Provider: <strong>{{ $user->auth_provider==='kemenag_sso'?'SSO Kemenag':'Login Lokal' }}</strong>@if($user->provider_id)<br><small class="text-secondary">ID Provider: {{ $user->provider_id }}</small>@endif</div>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Nama</label><input name="name" value="{{ old('name',$user->name) }}" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email',$user->email) }}" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">NIP</label><input name="nip" value="{{ old('nip',$user->nip) }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Unit Kerja</label><input name="unit_name" value="{{ old('unit_name',$user->unit_name) }}" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Role</label><select name="role" class="form-select" required><option value="author" @selected(old('role',$user->role)==='author')>Penulis Artikel</option><option value="admin" @selected(old('role',$user->role)==='admin')>Administrator</option></select></div>
        <div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="active" value="1" id="active" @checked(old('active',$user->active))><label class="form-check-label" for="active">Akun aktif dan boleh masuk</label></div></div>
        @if(!$user->usesSso())
        <div class="col-md-6"><label class="form-label">Kata Sandi Baru</label><input type="password" name="password" class="form-control" autocomplete="new-password"><small class="text-secondary">Kosongkan jika tidak diubah. Minimal 8 karakter.</small></div>
        <div class="col-md-6"><label class="form-label">Konfirmasi Kata Sandi</label><input type="password" name="password_confirmation" class="form-control" autocomplete="new-password"></div>
        @else
        <div class="col-12"><div class="alert alert-light border mb-0"><i class="bi bi-shield-lock me-2"></i>Kata sandi akun ini dikelola oleh SSO Kemenag dan tidak dapat diubah dari CMS.</div></div>
        @endif
    </div>
    <button class="btn btn-primary mt-4"><i class="bi bi-check2-circle me-1"></i> Simpan Pengguna</button>
</div>
</form>
@endsection
