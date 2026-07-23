@extends('admin.layout')
@php($editing=$menu->exists)
@section('title',$editing?'Edit Menu':'Tambah Menu')
@section('page_title',$editing?'Edit Menu':'Tambah Menu')
@section('page_subtitle','Atur judul, tautan, induk, dan tampilan menu navbar')
@section('page_actions')<a href="{{ route('admin.menus.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> Kembali</a>@endsection
@section('content')
<form method="post" action="{{ $editing ? route('admin.menus.update',$menu) : route('admin.menus.store') }}">
@csrf @if($editing) @method('put') @endif
<div class="row g-4">
    <div class="col-xl-8"><div class="admin-card p-4"><div class="row g-3">
        <div class="col-md-7"><label class="form-label">Nama Menu</label><input name="title" value="{{ old('title',$menu->title) }}" class="form-control" required placeholder="Contoh: Profil"></div>
        <div class="col-md-5"><label class="form-label">Menu Induk</label><select name="parent_id" class="form-select"><option value="">— Menu Utama —</option>@foreach($parentOptions as $id=>$label)<option value="{{ $id }}" @selected((string)old('parent_id',$menu->parent_id)===(string)$id)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-12"><label class="form-label">URL/Tautan</label><input name="url" value="{{ old('url',$menu->url) }}" class="form-control" placeholder="/berita atau https://rdm.kemenag.go.id"><small class="text-secondary">Kosongkan bila menu hanya menjadi induk dropdown.</small></div>
        <div class="col-md-5"><label class="form-label">Ikon Bootstrap <small>(opsional)</small></label><input name="icon" value="{{ old('icon',$menu->icon) }}" class="form-control" placeholder="bi-building"></div>
        <div class="col-md-4"><label class="form-label">Buka Tautan</label><select name="target" class="form-select"><option value="_self" @selected(old('target',$menu->target?:'_self')==='_self')>Tab yang sama</option><option value="_blank" @selected(old('target',$menu->target)==='_blank')>Tab baru</option></select></div>
        <div class="col-md-3"><label class="form-label">Urutan</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order',$menu->sort_order??0) }}" class="form-control"></div>
        <div class="col-12"><div class="form-check form-switch"><input type="checkbox" class="form-check-input" name="active" value="1" id="active" @checked(old('active',$menu->exists?$menu->active:true))><label for="active" class="form-check-label">Tampilkan menu di navbar</label></div></div>
    </div></div></div>
    <div class="col-xl-4"><div class="admin-card p-4"><h5>Contoh URL Internal</h5><div class="small text-secondary d-grid gap-2"><code>/</code><code>/profil/visi-dan-misi</code><code>/berita</code><code>/infografis</code><code>/galeri-foto</code><code>/hubungi-kami</code></div></div><button class="btn btn-primary btn-lg w-100 mt-4"><i class="bi bi-check2-circle me-1"></i> Simpan Menu</button></div>
</div>
</form>
@endsection
