<!doctype html>
<html lang="id">
<head>
    @php
        $siteName = $siteSettings['site_name'] ?? config('app.name', 'MAN 1 LAMPUNG SELATAN');
        $sectionTitle = trim($__env->yieldContent('title'));
        $separator = $siteSettings['seo_title_separator'] ?? '|';
        $defaultTitle = $siteSettings['seo_default_title'] ?? $siteName;
        $pageTitle = $sectionTitle ? $sectionTitle.' '.$separator.' '.$siteName : $defaultTitle;
        $metaDescription = trim($__env->yieldContent('meta_description')) ?: ($siteSettings['seo_default_description'] ?? $siteSettings['site_description'] ?? 'Portal resmi MAN 1 Lampung Selatan');
        $metaKeywords = trim($__env->yieldContent('meta_keywords')) ?: ($siteSettings['seo_default_keywords'] ?? 'MAN 1 Lampung Selatan, madrasah, sekolah, Kalianda');
        $metaImage = trim($__env->yieldContent('meta_image')) ?: \App\Models\Setting::mediaUrl($siteSettings['seo_og_image'] ?? null, asset('images/demo/hero-1.svg'));
        $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
        $robots = trim($__env->yieldContent('robots')) ?: (($siteSettings['seo_indexing'] ?? '1') === '1' ? 'index, follow' : 'noindex, nofollow');
        $logoUrl = \App\Models\Setting::mediaUrl($siteSettings['site_logo'] ?? null, asset('images/logo.svg'));
        $faviconUrl = \App\Models\Setting::mediaUrl($siteSettings['site_favicon'] ?? null, asset('images/logo.svg'));
        $themePrimary = \App\Models\Setting::normalizeHex($siteSettings['theme_primary'] ?? '#0877C9');
        $themePrimaryDark = \App\Models\Setting::normalizeHex($siteSettings['theme_primary_dark'] ?? '#045A9D', '#045A9D');
        $themeAccent = \App\Models\Setting::normalizeHex($siteSettings['theme_accent'] ?? '#F4CD00', '#F4CD00');
        $themeBackground = \App\Models\Setting::normalizeHex($siteSettings['theme_background'] ?? '#FFFFFF', '#FFFFFF');
        $themeSurface = \App\Models\Setting::normalizeHex($siteSettings['theme_surface'] ?? '#F5F7FA', '#F5F7FA');
        $themeText = \App\Models\Setting::normalizeHex($siteSettings['theme_text'] ?? '#171717', '#171717');
        $themeMuted = \App\Models\Setting::normalizeHex($siteSettings['theme_muted'] ?? '#667085', '#667085');
        $themeBorder = \App\Models\Setting::normalizeHex($siteSettings['theme_border'] ?? '#E3E8EF', '#E3E8EF');
        $themeRatios = [
            'white' => max(0, min(100, (int) ($siteSettings['theme_white_ratio'] ?? 75))),
            'primary' => max(0, min(100, (int) ($siteSettings['theme_primary_ratio'] ?? 18))),
            'accent' => max(0, min(100, (int) ($siteSettings['theme_accent_ratio'] ?? 5))),
            'neutral' => max(0, min(100, (int) ($siteSettings['theme_neutral_ratio'] ?? 2))),
        ];
        if (array_sum($themeRatios) !== 100) $themeRatios = ['white'=>75,'primary'=>18,'accent'=>5,'neutral'=>2];
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">
    @if(!empty($siteSettings['seo_google_verification']))<meta name="google-site-verification" content="{{ $siteSettings['seo_google_verification'] }}">@endif
    @if(!empty($siteSettings['seo_bing_verification']))<meta name="msvalidate.01" content="{{ $siteSettings['seo_bing_verification'] }}">@endif
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $logoUrl }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
@php
    $siteCssVersion = file_exists(public_path('assets/css/site.css'))
        ? filemtime(public_path('assets/css/site.css'))
        : time();
@endphp

<link rel="stylesheet"
      href="{{ asset('assets/css/site.css') }}?v={{ $siteCssVersion }}">
    <style>
        :root{
            --brand:{{ $themePrimary }};--brand-rgb:{{ \App\Models\Setting::hexToRgb($themePrimary) }};
            --brand-dark:{{ $themePrimaryDark }};--brand-dark-rgb:{{ \App\Models\Setting::hexToRgb($themePrimaryDark,'#045A9D') }};
            --brand-2:{{ $themePrimary }};--accent:{{ $themeAccent }};--accent-rgb:{{ \App\Models\Setting::hexToRgb($themeAccent,'#F4CD00') }};
            --accent-contrast:{{ \App\Models\Setting::contrastColor($themeAccent) }};
            --page-bg:{{ $themeBackground }};--surface:{{ $themeSurface }};--ink:{{ $themeText }};--ink-rgb:{{ \App\Models\Setting::hexToRgb($themeText,'#171717') }};
            --muted:{{ $themeMuted }};--border:{{ $themeBorder }};
            --ratio-white:{{ $themeRatios['white'] }}%;--ratio-primary:{{ $themeRatios['primary'] }}%;--ratio-accent:{{ $themeRatios['accent'] }}%;--ratio-neutral:{{ $themeRatios['neutral'] }}%;
        }
    </style>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        'name' => $siteName,
        'url' => url('/'),
        'logo' => $logoUrl,
        'description' => $metaDescription,
        'email' => $siteSettings['email'] ?? null,
        'telephone' => $siteSettings['phone'] ?? null,
        'address' => $siteSettings['address'] ?? null,
    ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @if(!empty($siteSettings['seo_analytics_id']))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $siteSettings['seo_analytics_id'] }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','{{ $siteSettings['seo_analytics_id'] }}');</script>
    @endif
    @stack('styles')
</head>
<body class="{{ ($siteSettings['theme_pattern_enabled'] ?? '1') === '1' ? 'theme-pattern-enabled' : '' }}">
    <div class="topbar d-none d-lg-block">
        <div class="container d-flex justify-content-between align-items-center py-2">
            <div class="d-flex gap-4 small">
                <span><i class="bi bi-telephone me-2"></i>{{ $siteSettings['phone'] ?? '(0727) 3320495' }}</span>
                <a href="mailto:{{ $siteSettings['email'] ?? 'info@mansalase.sch.id' }}"><i class="bi bi-envelope me-2"></i>{{ $siteSettings['email'] ?? 'info@mans1lamsel.sch.id' }}</a>
            </div>
            <div class="d-flex gap-3 small">
                <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                <a href="{{ $siteSettings['instagram'] ?? '#' }}" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a>
                <a href="{{ $siteSettings['youtube'] ?? '#' }}" target="_blank" rel="noopener"><i class="bi bi-youtube"></i></a>
                <a href="{{ $siteSettings['facebook'] ?? '#' }}" target="_blank" rel="noopener"><i class="bi bi-facebook"></i></a>
            </div>
        </div>
    </div>

    <header class="site-header sticky-top">
        <nav class="navbar navbar-expand-xl navbar-light">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center gap-3" href="{{ route('home') }}">
                    <img src="{{ $logoUrl }}" alt="Logo {{ $siteName }}" width="62" height="62">
                    <span><strong>{{ $siteName }}</strong><small>{{ $siteSettings['site_tagline'] ?? 'Madrasah Mandiri Berprestasi' }}</small></span>
                </a>
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav ms-auto align-items-xl-center gap-xl-1">
                        @forelse($navbarMenus as $menu)
                            @include('partials.nav-item', ['menu' => $menu, 'level' => 0])
                        @empty
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Beranda</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('posts.news') }}">Berita</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('posts.information') }}">Informasi</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('infographics.index') }}">Infografis</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Kontak</a></li>
                        @endforelse
                        <li class="nav-item ms-xl-2"><button class="btn btn-search" type="button" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="Cari"><i class="bi bi-search"></i></button></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <div class="brand-composition-strip" aria-hidden="true"></div>

    @if(session('success'))<div class="container mt-3"><div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>@endif
    @yield('content')

    <footer class="footer pt-5">
        <div class="container">
            <div class="row g-4 pb-5">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-3 mb-3"><img src="{{ $logoUrl }}" width="70" height="70" alt="Logo {{ $siteName }}"><div><h4 class="mb-0">{{ $siteSettings['site_name'] ?? 'MAN 1 Lampung Selatan' }}</h4><small>{{ $siteSettings['site_tagline'] ?? 'Madrasah Maju, Bermutu, Mendunia' }}</small></div></div>
                    <p class="footer-copy">{{ $siteSettings['site_description'] ?? 'Portal Resmi | MAN 1 LAMPUNG SELATAN' }}</p>
                    <div class="social-links"><a href="{{ $siteSettings['instagram'] ?? '#' }}"><i class="bi bi-instagram"></i></a><a href="{{ $siteSettings['youtube'] ?? '#' }}"><i class="bi bi-youtube"></i></a><a href="{{ $siteSettings['facebook'] ?? '#' }}"><i class="bi bi-facebook"></i></a></div>
                </div>
                <div class="col-6 col-lg-2"><h6>Navigasi</h6><ul class="footer-links"><li><a href="{{ route('posts.news') }}">Berita</a></li><li><a href="{{ route('posts.articles') }}">Artikel</a></li><li><a href="{{ route('posts.announcements') }}">Pengumuman</a></li><li><a href="{{ route('posts.information') }}">Informasi</a></li><li><a href="{{ route('infographics.index') }}">Infografis</a></li><li><a href="{{ route('galleries.photos') }}">Galeri</a></li></ul></div>
                <div class="col-6 col-lg-2"><h6>Layanan</h6><ul class="footer-links"><li><a href="{{ $siteSettings['spmb_url'] ?? '#' }}">SPMB</a></li><li><a href="{{ $siteSettings['rdm_url'] ?? '#' }}">RDM</a></li><li><a href="https://absensiswa.man1lamsel.sch.id/">#Absensiswa</a></li><li><a href="{{ route('contact') }}">Hubungi Kami</a></li><li><a href="{{ route('admin.login') }}">Administrator</a></li></ul></div>
                <div class="col-lg-3"><h6>Alamat</h6><ul class="contact-list"><li><i class="bi bi-geo-alt"></i><span>{{ $siteSettings['address'] ?? 'Kalianda, Lampung Selatan' }}</span></li><li><i class="bi bi-telephone"></i><span>{{ $siteSettings['phone'] ?? '-' }}</span></li><li><i class="bi bi-envelope"></i><span>{{ $siteSettings['email'] ?? '-' }}</span></li></ul></div>
            </div>
        </div>
        <div class="footer-bottom"><div class="container d-flex flex-column flex-md-row justify-content-between gap-2"><span>© {{ date('Y') }} {{ $siteSettings['site_name'] ?? 'MAN 1 Lampung Selatan' }}.</span><span>Dikembangkan Oleh Support System Gaara</span></div></div>
    </footer>

    <div class="modal fade" id="searchModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4"><div class="modal-body p-4"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">Cari Informasi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="{{ route('search') }}"><div class="input-group input-group-lg"><input class="form-control" name="q" placeholder="Ketik kata kunci..." required><button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button></div></form></div></div></div></div>
    <button class="back-to-top" id="backToTop" aria-label="Kembali ke atas"><i class="bi bi-arrow-up"></i></button>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/site.js') }}"></script>
    <script>
        document.querySelectorAll('.dropdown-submenu > .dropdown-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const submenu = this.nextElementSibling;
                this.closest('.dropdown-menu')?.querySelectorAll(':scope > .dropdown-submenu > .dropdown-menu.show').forEach(function (open) {
                    if (open !== submenu) open.classList.remove('show');
                });
                submenu?.classList.toggle('show');
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
