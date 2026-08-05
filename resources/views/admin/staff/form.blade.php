@extends('admin.layout')
@php($editing = $staffMember->exists)
@section('title', $editing ? 'Edit GTK' : 'Tambah GTK')
@section('page_title', $editing ? 'Edit GTK' : 'Tambah GTK')
@section('page_subtitle', 'NIP digunakan untuk menautkan data GTK dengan akun penulis dan SSO Kemenag')
@section('page_actions')
<a href="{{ route('admin.staff.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $editing ? route('admin.staff.update', $staffMember) : route('admin.staff.store') }}">
    @csrf
    @if($editing) @method('put') @endif
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="admin-card p-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nama pada SIMPEG</label>
                        <input name="name" value="{{ old('name', $staffMember->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Urutan</label>
                        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $staffMember->sort_order ?? 0) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NIP</label>
                        <input name="nip" value="{{ old('nip', $staffMember->nip) }}" class="form-control" inputmode="numeric">
                        <small class="text-secondary"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jabatan</label>
                        <input name="position" value="{{ old('position', $staffMember->position) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input name="subject" value="{{ old('subject', $staffMember->subject) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jenis</label >
                        <select name="type" class="form-select" required >
                            <option value="kepala" @selected(old('type', $staffMember->type) === 'kepala')>Kepala Madrasah</option>
                            <option value="guru" @selected(old('type', $staffMember->type ?: 'guru') === 'guru')>Guru</option>
                            <option value="pegawai" @selected(old('type', $staffMember->type) === 'pegawai')>Pegawai</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug <small>(otomatis)</small></label>
                        <input name="slug" value="{{ old('slug', $staffMember->slug) }}" readonly class="form-control" >
                    </div>
                    <!--<div class="col-12">
                        <label class="form-label">Biografi/Sambutan</label>
                        <textarea name="bio" rows="7" class="form-control">{{ old('bio', $staffMember->bio) }}</textarea>
                    </div>-->
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="active" value="1" id="active" @checked(old('active', $staffMember->exists ? $staffMember->active : true))>
                            <label class="form-check-label" for="active">Tampilkan di Website</label>
                        </div>
                    </div>
                    @if($staffMember->user)
                    <div class="col-12">
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-link-45deg me-2"></i>
                            Terhubung ke akun <strong>{{ $staffMember->user->name }}</strong> ({{ $staffMember->user->email }}).
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="admin-card p-4">
                <h5>Foto</h5>
                @if($staffMember->photo)
                    @php($img = str_starts_with($staffMember->photo, 'demo/') ? asset('images/'.$staffMember->photo) : Storage::url($staffMember->photo))
                    <img src="{{ $img }}" class="image-preview portrait mb-3" alt="{{ $staffMember->name }}">
                @endif
                <input type="file" name="photo" class="form-control" accept="image/*">
                <small class="text-secondary">Maksimal 3 MB.</small>
            </div>
            <button class="btn btn-primary btn-lg w-100 mt-4">Simpan Data</button>
        </div>
    </div>
</form>
@endsection
