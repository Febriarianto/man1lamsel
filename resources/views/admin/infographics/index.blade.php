@extends('admin.layout')
@section('title','Infografis')
@section('page_title','Infografis')
@section('page_subtitle','Kelola publikasi visual dan data informatif madrasah')
@section('page_actions')<a href="{{ route('admin.infographics.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Infografis</a>@endsection
@section('content')
<div class="admin-card">
<form class="filter-bar" method="get"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari judul infografis..."><button class="btn btn-dark">Cari</button><a href="{{ route('admin.infographics.index') }}" class="btn btn-light">Reset</a></form>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Infografis</th><th>Publikasi</th><th>Urutan</th><th>Dilihat</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
@forelse($infographics as $item)
<tr><td><div class="d-flex align-items-center gap-3">@php($img=str_starts_with($item->image,'demo/')?asset('images/'.$item->image):Storage::url($item->image))<img src="{{ $img }}" class="table-thumb" alt=""><div><strong>{{ $item->title }}</strong>@if($item->featured)<small class="d-block text-warning"><i class="bi bi-star-fill"></i> Unggulan</small>@endif</div></div></td><td>{{ optional($item->published_at)->format('d/m/Y H:i') ?: '-' }}</td><td>{{ $item->sort_order }}</td><td>{{ number_format($item->views) }}</td><td><span class="status-dot {{ $item->active?'published':'draft' }}">{{ $item->active?'Aktif':'Nonaktif' }}</span></td><td><div class="table-actions"><a target="_blank" href="{{ route('infographics.show',$item) }}" class="btn btn-light btn-sm"><i class="bi bi-eye"></i></a><a href="{{ route('admin.infographics.edit',$item) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i></a><form method="post" action="{{ route('admin.infographics.destroy',$item) }}">@csrf @method('delete')<button class="btn btn-light btn-sm text-danger" data-confirm="Hapus infografis ini?"><i class="bi bi-trash"></i></button></form></div></td></tr>
@empty<tr><td colspan="6" class="text-center py-5">Belum ada infografis.</td></tr>@endforelse
</tbody></table></div><div class="p-3">{{ $infographics->links() }}</div></div>
@endsection
