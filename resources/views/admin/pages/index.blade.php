@extends('admin.layout')
@section('title', 'Halaman Profil')
@section('page_title', 'Halaman Profil')
@section('page_subtitle', 'Kelola informasi tetap seperti profil, visi misi, sejarah, dan fasilitas')
@section('page_actions')
    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Halaman</a>
@endsection
@section('content')
<div class="admin-card">
    <form class="filter-bar" method="get">
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari judul, slug, atau ringkasan...">
        <button class="btn btn-dark"><i class="bi bi-search me-1"></i>Cari</button>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-light">Reset</a>
    </form>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Judul</th><th>Slug</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
    @forelse($pages as $page)
        <tr><td><strong>{{ $page->title }}</strong></td><td><code>/profil/{{ $page->slug }}</code></td><td><span class="status-dot {{ $page->status === 'published' ? 'published' : 'draft' }}">{{ ucfirst($page->status) }}</span></td><td><div class="table-actions"><a target="_blank" href="{{ route('pages.show', $page) }}" class="btn btn-light btn-sm"><i class="bi bi-eye"></i></a><a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i></a><form method="post" action="{{ route('admin.pages.destroy', $page) }}">@csrf @method('delete')<button data-confirm="Hapus halaman ini?" class="btn btn-light btn-sm text-danger"><i class="bi bi-trash"></i></button></form></div></td></tr>
    @empty
        <tr><td colspan="4" class="text-center py-5">Tidak ada halaman yang sesuai pencarian.</td></tr>
    @endforelse
    </tbody></table></div>
    <div class="p-3">{{ $pages->links() }}</div>
</div>
@endsection
