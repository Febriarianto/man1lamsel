@extends('admin.layout')
@section('title','Akun Saya')
@section('page_title','Akun Saya')
@section('page_subtitle',$user->usesSsoOnly()?'Identitas akun tersinkron dari SSO Kemenag':'Ubah nama, email, dan kata sandi akun manual')
@section('content')
@if($user->usesSsoOnly())
<div class="admin-card p-4 mw-form"><div class="alert alert-success"><i class="bi bi-shield-check me-2"></i>Akun terhubung dengan <strong>SSO Kemenag</strong>. Nama dan email akan diperbarui setiap kali Anda login melalui SSO.</div><div class="row g-3"><div class="col-md-6"><label class="form-label">Nama</label><input value="{{ $user->name }}" class="form-control" disabled></div><div class="col-md-6"><label class="form-label">Email</label><input value="{{ $user->email }}" class="form-control" disabled></div><div class="col-md-6"><label class="form-label">NIP</label><input value="{{ $user->nip ?: '-' }}" class="form-control" disabled></div><div class="col-md-6"><label class="form-label">Unit Kerja</label><input value="{{ $user->unit_name ?: '-' }}" class="form-control" disabled></div><div class="col-md-6"><label class="form-label">Role</label><input value="{{ $user->isAdmin()?'Administrator':'Penulis Artikel' }}" class="form-control" disabled></div><div class="col-md-6"><label class="form-label">Login Terakhir</label><input value="{{ optional($user->last_login_at)->format('d/m/Y H:i') ?: '-' }}" class="form-control" disabled></div></div></div>
@else
<form method="post" action="{{ route('admin.account.update') }}">@csrf @method('put')
<div class="admin-card p-4 mw-form">@if($user->usesSso())<div class="alert alert-success"><i class="bi bi-shield-check me-2"></i>Akun ini mendukung login manual dan SSO. Nama serta email dapat disinkronkan kembali saat login SSO berikutnya.</div>@endif<div class="row g-3"><div class="col-md-6"><label class="form-label">Nama</label><input name="name" value="{{ old('name',$user->name) }}" class="form-control" required></div><div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email',$user->email) }}" class="form-control" required></div><div class="col-md-6"><label class="form-label">Kata Sandi Baru</label><input type="password" name="password" class="form-control"><small class="text-secondary">Kosongkan bila tidak diubah. Minimal 8 karakter.</small></div><div class="col-md-6"><label class="form-label">Konfirmasi Kata Sandi</label><input type="password" name="password_confirmation" class="form-control"></div></div><button class="btn btn-primary mt-4">Simpan Akun</button></div>
</form>
@endif
@endsection
