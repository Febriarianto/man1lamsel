@extends('admin.layout')
@php($editing = $link->exists)
@section('title', $editing ? 'Edit Layanan' : 'Tambah Layanan')
@section('page_title', $editing ? 'Edit Layanan' : 'Tambah Layanan')
@section('page_subtitle', 'Atur kartu layanan yang tampil pada panel akses cepat di beranda')
@section('page_actions')
<a href="{{ route('admin.links.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection

@section('content')
<form method="post" action="{{ $editing ? route('admin.links.update', $link) : route('admin.links.store') }}">
    @csrf
    @if($editing) @method('put') @endif
    <div class="admin-card p-4 mw-form">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Layanan</label>
                <input name="name" value="{{ old('name', $link->name) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Ikon Bootstrap</label>
                <input name="icon" value="{{ old('icon', $link->icon ?: 'bi-link-45deg') }}" class="form-control" required>
                <small class="text-secondary">Contoh: bi-building, bi-database, bi-people</small>
            </div>
            <div class="col-12">
                <label class="form-label">Deskripsi Singkat</label>
                <input name="description" maxlength="160" value="{{ old('description', $link->description) }}" class="form-control" placeholder="Contoh: Seleksi Murid Baru">
            </div>
            <div class="col-12">
                <label class="form-label">URL</label>
                <input name="url" value="{{ old('url', $link->url) }}" class="form-control" placeholder="/informasi atau https://..." required>
                <small class="text-secondary">Boleh menggunakan URL internal, misalnya <code>/informasi</code>, atau URL lengkap.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Urutan</label>
                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $link->sort_order ?? 0) }}" class="form-control" required>
            </div>
            <div class="col-md-8 d-flex align-items-end gap-4 pb-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="active" value="1" id="active" @checked(old('active', $link->exists ? $link->active : true))>
                    <label class="form-check-label" for="active">Aktif</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="new_tab" value="1" id="new_tab" @checked(old('new_tab', $link->exists ? $link->new_tab : false))>
                    <label class="form-check-label" for="new_tab">Buka tab baru</label>
                </div>
            </div>
        </div>
        <button class="btn btn-primary mt-4">Simpan Layanan</button>
    </div>
</form>
@endsection
