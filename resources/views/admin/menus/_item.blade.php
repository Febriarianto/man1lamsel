<li class="menu-item" data-id="{{ $menu->id }}">
    <div class="menu-row">
        <span class="drag-handle" title="Geser untuk mengubah urutan"><i class="bi bi-grip-vertical"></i></span>
        <span class="menu-icon"><i class="bi {{ $menu->icon ?: 'bi-link-45deg' }}"></i></span>
        <div class="menu-info">
            <strong>{{ $menu->title }}</strong>
            <small>{{ $menu->url ?: 'Menu induk/tanpa tautan' }}</small>
        </div>
        <span class="status-dot {{ $menu->active ? 'published' : 'draft' }}">{{ $menu->active ? 'Aktif' : 'Nonaktif' }}</span>
        <div class="table-actions">
            <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i></a>
            <form method="post" action="{{ route('admin.menus.destroy', $menu) }}">@csrf @method('delete')
                <button class="btn btn-light btn-sm text-danger" data-confirm="Hapus menu ini?"><i class="bi bi-trash"></i></button>
            </form>
        </div>
    </div>
    <ol class="menu-list nested-list">
        @foreach($menu->childrenRecursive as $child)
            @include('admin.menus._item', ['menu' => $child])
        @endforeach
    </ol>
</li>
