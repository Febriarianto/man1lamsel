@extends('admin.layout')
@section('title', auth()->user()->isAdmin() ? 'Berita | Artikel' : 'Artikel Saya')
@section('page_title', auth()->user()->isAdmin() ? 'Berita | Artikel' : 'Artikel Saya')
@section('page_subtitle', auth()->user()->isAdmin() ? 'Kelola berita, artikel, pengumuman, prestasi, dan informasi berlampiran' : 'Tulis artikel sebagai guru atau pegawai; publikasi dilakukan setelah peninjauan administrator')
@section('page_actions')
<a href="{{ route('admin.posts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ auth()->user()->isAdmin() ? 'Tambah Konten' : 'Tulis Artikel' }}</a>
@endsection

@section('content')
@if(!auth()->user()->isAdmin())
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Artikel baru otomatis disimpan sebagai <strong>draft</strong>. Administrator dapat meninjau dan menerbitkannya tanpa mengubah nama penulis.</div>
@endif
<div class="admin-card">
    <form class="filter-bar" method="get">
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari judul, ringkasan, atau penulis...">
        @if(auth()->user()->isAdmin())
            <select class="form-select" name="category">
                <option value="">Semua kategori</option>
                @foreach(['berita', 'artikel', 'pengumuman', 'prestasi', 'informasi'] as $cat)
                    <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
        @endif
        <button class="btn btn-dark">Filter</button>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-light">Reset</a>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Judul</th>@if(auth()->user()->isAdmin())<th>Penulis</th>@endif<th>Kategori</th><th>Status</th><th>Publikasi</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($posts as $post)
                <tr>
                    <td>
                        <div class="table-title">
                            <strong>{{ $post->title }} @if($post->attachment)<i class="bi bi-paperclip text-primary" title="Memiliki lampiran"></i>@endif</strong>
                            <small>{{ Str::limit($post->excerpt, 75) }}</small>
                        </div>
                    </td>
                    @if(auth()->user()->isAdmin())
                        <td><div class="table-title"><strong>{{ $post->author_display_name }}</strong><small>{{ $post->author?->unit_name ?: ($post->author?->email ?: '-') }}</small></div></td>
                    @endif
                    <td><span class="badge bg-light text-dark">{{ ucfirst($post->category) }}</span></td>
                    <td><span class="status-dot {{ $post->status === 'published' ? 'published' : 'draft' }}">{{ ucfirst($post->status) }}</span></td>
                    <td>{{ optional($post->published_at)->format('d/m/Y H:i') ?: '-' }}</td>
                    <td>
                        <div class="table-actions">
                            @if($post->status === 'published')<a target="_blank" href="{{ route('posts.show', $post) }}" class="btn btn-light btn-sm" title="Lihat"><i class="bi bi-eye"></i></a>@endif
                            @if(auth()->user()->isAdmin() || $post->status === 'draft')
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-light btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="{{ route('admin.posts.destroy', $post) }}">@csrf @method('delete')<button data-confirm="Hapus konten ini?" class="btn btn-light btn-sm text-danger" title="Hapus"><i class="bi bi-trash"></i></button></form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}" class="text-center py-5 text-secondary">Belum ada konten.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $posts->links() }}</div>
</div>
@endsection
