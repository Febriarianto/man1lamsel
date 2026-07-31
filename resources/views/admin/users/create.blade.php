@extends('admin.layout')
@section('title','Tambah Penulis Manual')
@section('page_title','Tambah Penulis Manual')
@section('page_subtitle','Buat akun lokal dan hubungkan dengan data guru atau pegawai')
@section('page_actions')
<a href="{{ route('admin.users.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
@endsection
@section('content')
<form method="post" action="{{ route('admin.users.store') }}">
    @csrf
    <div class="admin-card p-4 mw-form">
        <div class="alert alert-light border">
            <i class="bi bi-info-circle me-2"></i>
            Pilih data GTK agar akun dapat ditautkan otomatis saat pemilik NIP yang sama masuk melalui SSO Kemenag. Login manual akan tetap tersedia.
        </div>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Hubungkan dengan GTK <small class="text-secondary">(opsional)</small></label>
                <select name="staff_id" class="form-select">
                    <option value="">Tidak dihubungkan</option>
                    @foreach($staffOptions as $staff)
                        <option value="{{ $staff->id }}" @selected((string) old('staff_id') === (string) $staff->id)>
                            {{ $staff->name }}{{ $staff->nip ? ' — NIP '.$staff->nip : ' — NIP belum diisi' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nama Lengkap</label>
                <input name="name" value="{{ old('name') }}" class="form-control" required autofocus>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email Login</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autocomplete="off">
            </div>
            <div class="col-md-6">
                <label class="form-label">NIP <small class="text-secondary">(opsional)</small></label>
                <input name="nip" value="{{ old('nip') }}" class="form-control" inputmode="numeric">
                <small class="text-secondary">Jika GTK sudah memiliki NIP, nilainya harus sama.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Unit Kerja <small class="text-secondary">(opsional)</small></label>
                <input name="unit_name" value="{{ old('unit_name') }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Kata Sandi</label>
                <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                <small class="text-secondary">Minimal 8 karakter.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="active" value="1" id="active" @checked(old('active', true))>
                    <label class="form-check-label" for="active">Akun aktif dan langsung boleh masuk</label>
                </div>
            </div>
        </div>
        <button class="btn btn-primary mt-4"><i class="bi bi-person-check me-1"></i> Buat Akun Penulis</button>
    </div>
</form>
@endsection
