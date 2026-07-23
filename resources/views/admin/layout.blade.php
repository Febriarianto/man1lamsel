<!doctype html>
<html lang="id">
<head>
    @php($adminLogo=\App\Models\Setting::mediaUrl($siteSettings['site_logo']??null,asset('images/logo.svg')))
    @php($adminFavicon=\App\Models\Setting::mediaUrl($siteSettings['site_favicon']??null,asset('images/logo.svg')))
    @php($applyAdminTheme=($siteSettings['theme_apply_admin']??'1')==='1')
    @php($adminPrimary=\App\Models\Setting::normalizeHex($applyAdminTheme?($siteSettings['theme_primary']??'#0877C9'):'#0877C9'))
    @php($adminPrimaryDark=\App\Models\Setting::normalizeHex($applyAdminTheme?($siteSettings['theme_primary_dark']??'#045A9D'):'#045A9D','#045A9D'))
    @php($adminAccent=\App\Models\Setting::normalizeHex($applyAdminTheme?($siteSettings['theme_accent']??'#F4CD00'):'#F4CD00','#F4CD00'))
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Dashboard') | Admin {{ $siteSettings['site_name'] ?? 'MAN 1 Lampung Selatan' }}</title>
    <link rel="icon" href="{{ $adminFavicon }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote-lite.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
    <style>:root{--brand:{{ $adminPrimary }};--brand-rgb:{{ \App\Models\Setting::hexToRgb($adminPrimary) }};--brand-dark:{{ $adminPrimaryDark }};--brand-dark-rgb:{{ \App\Models\Setting::hexToRgb($adminPrimaryDark,'#045A9D') }};--accent:{{ $adminAccent }};--accent-rgb:{{ \App\Models\Setting::hexToRgb($adminAccent,'#F4CD00') }};}</style>
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar" id="sidebar">
        <a class="sidebar-brand" href="{{ route('admin.dashboard') }}"><img src="{{ $adminLogo }}" alt="Logo"><span>{{ $siteSettings['site_name'] ?? 'DASHBOARD' }}<small>{{ auth()->user()->isAdmin()?'Administrator':'Penulis' }}</small></span></a>
        <nav class="sidebar-nav">
            <span class="nav-label">UTAMA</span>
            <a class="{{ request()->routeIs('admin.dashboard')?'active':'' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>
            <span class="nav-label">{{ auth()->user()->isAdmin()?'KONTEN':'PENULIS' }}</span>
            <a class="{{ request()->routeIs('admin.posts.*')?'active':'' }}" href="{{ route('admin.posts.index') }}"><i class="bi bi-newspaper"></i> {{ auth()->user()->isAdmin()?'Berita & Artikel':'Artikel Saya' }}</a>
            @if(auth()->user()->isAdmin())
            <a class="{{ request()->routeIs('admin.pages.*')?'active':'' }}" href="{{ route('admin.pages.index') }}"><i class="bi bi-file-earmark-text"></i> Halaman Profil</a>
            <a class="{{ request()->routeIs('admin.staff.*')?'active':'' }}" href="{{ route('admin.staff.index') }}"><i class="bi bi-people"></i> GTK</a>
            <a class="{{ request()->routeIs('admin.galleries.*')?'active':'' }}" href="{{ route('admin.galleries.index') }}"><i class="bi bi-images"></i> Galeri</a>
            <a class="{{ request()->routeIs('admin.infographics.*')?'active':'' }}" href="{{ route('admin.infographics.index') }}"><i class="bi bi-file-earmark-bar-graph"></i> Infografis</a>
            <a class="{{ request()->routeIs('admin.events.*')?'active':'' }}" href="{{ route('admin.events.index') }}"><i class="bi bi-calendar-event"></i> Agenda</a>
            <span class="nav-label">TAMPILAN</span>
            <a class="{{ request()->routeIs('admin.menus.*')?'active':'' }}" href="{{ route('admin.menus.index') }}"><i class="bi bi-list-nested"></i> Menu Navbar</a>
            <a class="{{ request()->routeIs('admin.banners.*')?'active':'' }}" href="{{ route('admin.banners.index') }}"><i class="bi bi-window-stack"></i> Banner Utama</a>
            <a class="{{ request()->routeIs('admin.links.*')?'active':'' }}" href="{{ route('admin.links.index') }}"><i class="bi bi-link-45deg"></i> Tautan Layanan</a>
            <a class="{{ request()->routeIs('admin.settings.*')?'active':'' }}" href="{{ route('admin.settings.edit') }}"><i class="bi bi-gear"></i> Pengaturan, Tema & SEO</a>
            <span class="nav-label">PENGGUNA & LAYANAN</span>
            <a class="{{ request()->routeIs('admin.users.*')?'active':'' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-person-badge"></i> Pengguna & Penulis</a>
            <a class="{{ request()->routeIs('admin.messages.*')?'active':'' }}" href="{{ route('admin.messages.index') }}"><i class="bi bi-envelope"></i> Pesan Masuk @php($unread=\App\Models\ContactMessage::whereNull('read_at')->count())@if($unread)<span class="badge text-bg-warning ms-auto">{{ $unread }}</span>@endif</a>
            @endif
        </nav>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar"><button class="btn sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button><div class="ms-auto d-flex align-items-center gap-3"><a class="btn btn-light btn-sm" target="_blank" href="{{ route('home') }}"><i class="bi bi-box-arrow-up-right me-1"></i> Lihat Website</a><div class="dropdown"><button class="btn user-menu dropdown-toggle" data-bs-toggle="dropdown"><span class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><span class="d-none d-md-inline">{{ auth()->user()->name }}</span></button><ul class="dropdown-menu dropdown-menu-end"><li><div class="px-3 py-2"><small class="text-secondary d-block">{{ auth()->user()->isAdmin()?'Administrator':'Penulis Artikel' }}</small>@if(auth()->user()->unit_name)<small>{{ auth()->user()->unit_name }}</small>@endif</div></li><li><hr class="dropdown-divider"></li><li><a class="dropdown-item" href="{{ route('admin.account.edit') }}"><i class="bi bi-person-gear me-2"></i>Akun Saya</a></li><li><hr class="dropdown-divider"></li><li><form method="post" action="{{ route('admin.logout') }}">@csrf<button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button></form></li></ul></div></div></header>
        <div class="admin-content">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4"><div><h1 class="page-title">@yield('page_title','Dashboard')</h1><p class="page-subtitle mb-0">@yield('page_subtitle','Kelola portal MAN 1 Lampung Selatan')</p></div>@yield('page_actions')</div>
            @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger"><strong>Periksa kembali data berikut:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </div>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote-lite.min.js"></script>
<script>
document.getElementById('sidebarToggle')?.addEventListener('click',()=>document.body.classList.toggle('sidebar-open'));
document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{if(!confirm(el.dataset.confirm||'Yakin?'))e.preventDefault()}));

$('.summernote-editor').each(function () {
    $(this).summernote({
        height: Number(this.dataset.height || 440),
        minHeight: 260,
        placeholder: this.dataset.placeholder || 'Tulis isi konten di sini...',
        dialogsInBody: true,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']],
        ],
    });
});
</script>
@stack('scripts')
</body></html>
