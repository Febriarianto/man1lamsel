@extends('admin.layout')
@section('title','Menu Navbar')
@section('page_title','Menu Navbar')
@section('page_subtitle','Susun menu dan submenu dengan drag-and-drop')
@section('page_actions')
    <a href="{{ route('admin.menus.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Menu</a>
@endsection
@section('content')
<div class="row g-4">
    <div class="col-xl-8">
        <div class="admin-card p-4">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <div><h5 class="mb-1">Struktur Navbar</h5><p class="text-secondary small mb-0">Geser ke atas/bawah untuk urutan. Geser ke kanan untuk menjadikannya submenu.</p></div>
                <button type="button" id="saveMenuOrder" class="btn btn-dark"><i class="bi bi-check2-circle me-1"></i> Simpan Urutan</button>
            </div>
            <div id="menuNotice" class="alert d-none"></div>
            <ol class="menu-list root-list" id="menuTree">
                @forelse($menus as $menu)
                    @include('admin.menus._item', ['menu' => $menu])
                @empty
                    <li class="empty-menu">Belum ada menu. Klik “Tambah Menu”.</li>
                @endforelse
            </ol>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="admin-card p-4">
            <h5><i class="bi bi-info-circle me-2"></i>Panduan</h5>
            <ul class="text-secondary small ps-3 mb-0">
                <li class="mb-2">Menu tanpa URL dapat digunakan sebagai induk dropdown.</li>
                <li class="mb-2">URL internal dapat ditulis seperti <code>/berita</code>.</li>
                <li class="mb-2">URL eksternal harus lengkap, misalnya <code>https://...</code>.</li>
                <li>Setelah menggeser struktur, klik <strong>Simpan Urutan</strong>.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    document.querySelectorAll('.menu-list').forEach(function (list) {
        new Sortable(list, {
            group: 'nested-menu',
            animation: 180,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            handle: '.drag-handle',
            emptyInsertThreshold: 18
        });
    });

    function serialize(list) {
        return Array.from(list.children).filter(el => el.classList.contains('menu-item')).map(function (item) {
            const childList = Array.from(item.children).find(el => el.classList.contains('menu-list'));
            return {id: Number(item.dataset.id), children: childList ? serialize(childList) : []};
        });
    }

    document.getElementById('saveMenuOrder')?.addEventListener('click', async function () {
        const button = this;
        const notice = document.getElementById('menuNotice');
        button.disabled = true;
        try {
            const response = await fetch(@json(route('admin.menus.reorder')), {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify({items: serialize(document.getElementById('menuTree'))})
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Urutan gagal disimpan.');
            notice.className = 'alert alert-success';
            notice.textContent = result.message;
        } catch (error) {
            notice.className = 'alert alert-danger';
            notice.textContent = error.message;
        } finally {
            button.disabled = false;
        }
    });
})();
</script>
@endpush
