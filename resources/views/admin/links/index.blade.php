@extends('admin.layout')
@section('title', 'Layanan Depan')
@section('page_title', 'Layanan Depan')
@section('page_subtitle', 'Kelola kartu akses cepat yang tampil dinamis di bawah banner beranda')
@section('page_actions')
<a href="{{ route('admin.links.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Layanan</a>
@endsection

@section('content')
<div class="admin-card">
    <form class="filter-bar" method="get">
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama, deskripsi, URL, atau ikon...">
        <button class="btn btn-dark"><i class="bi bi-search me-1"></i>Cari</button>
        <a href="{{ route('admin.links.index') }}" class="btn btn-light">Reset</a>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Layanan</th><th>URL</th><th>Ikon</th><th>Urutan</th><th>Target</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($links as $link)
                <tr>
                    <td><div class="table-title"><strong>{{ $link->name }}</strong><small>{{ $link->description ?: 'Tanpa deskripsi' }}</small></div></td>
                    <td><a href="{{ $link->url }}" target="_blank">{{ Str::limit($link->url, 45) }}</a></td>
                    <td><i class="bi {{ $link->icon }}"></i> <code>{{ $link->icon }}</code></td>
                    <td>{{ $link->sort_order }}</td>
                    <td>{{ $link->new_tab ? 'Tab baru' : 'Tab sama' }}</td>
                    <td><span class="status-dot {{ $link->active ? 'published' : 'draft' }}">{{ $link->active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td><div class="table-actions"><a href="{{ route('admin.links.edit', $link) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i></a><form method="post" action="{{ route('admin.links.destroy', $link) }}">@csrf @method('delete')<button data-confirm="Hapus layanan ini?" class="btn btn-light btn-sm text-danger"><i class="bi bi-trash"></i></button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-5">Tidak ada layanan yang sesuai pencarian.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $links->links() }}</div>
</div>
@endsection
