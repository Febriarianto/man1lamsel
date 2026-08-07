@extends('layouts.app')
@section('title', ($siteSettings['site_name'] ?? '').' Portal Resmi ')
@section('content')
<section class="hero-section">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-indicators">@foreach($banners as $banner)<button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}"></button>@endforeach</div>
        <div class="carousel-inner">
            @forelse($banners as $banner)
            @php($bannerImage = $banner->image ? (str_starts_with($banner->image,'demo/') ? asset('images/'.$banner->image) : Storage::url($banner->image)) : asset('images/demo/hero-1.svg'))
            <div class="carousel-item {{ $loop->first ? 'active' : '' }}" style="background-image:linear-gradient(90deg,rgba(var(--brand-dark-rgb),.96),rgba(var(--brand-rgb),.72),rgba(var(--brand-rgb),.22)),url('{{ $bannerImage }}')">
                <div class="container hero-content">
                    <div class="row">
                        <div class="col-lg-8 col-xl-7"><span class="eyebrow-light"><i class="bi bi-stars"></i> Selamat datang di Portal Resmi | MAN 1 LAMPUNG SELATAN</span>
                            <h1>{{ $banner->title }}</h1>
                            <p>{{ $banner->subtitle }}</p>@if($banner->button_text)<a href="{{ $banner->button_url ?: '#' }}" class="btn btn-gold btn-lg">{{ $banner->button_text }} <i class="bi bi-arrow-right ms-2"></i></a>@endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="carousel-item active" style="background-image:linear-gradient(90deg,rgba(var(--brand-dark-rgb),.96),rgba(var(--brand-rgb),.55)),url('{{ asset('images/demo/hero-1.svg') }}')">
                <div class="container hero-content">
                    <h1>MAN 1 LAMPUNG SELATAN</h1>
                </div>
            </div>
            @endforelse
        </div>
        @if($banners->count()>1)<button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button><button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>@endif
    </div>
</section>

@if($links->isNotEmpty())
<section class="quick-access-wrap">
    <div class="container">
        <div class="quick-access dynamic-services shadow-lg">
            @foreach($links as $link)
            <a href="{{ $link->url }}" @if($link->new_tab) target="_blank" rel="noopener" @endif>
                <span><i class="bi {{ $link->icon }}"></i></span>
                <div>
                    <strong>{{ $link->name }}</strong>
                    <small>{{ $link->description ?: 'Layanan Madrasah' }}</small>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($principal)
<section class="section-space">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <div class="principal-photo-wrap">
                    <div class="principal-accent"></div>@php($principalImage = $principal->photo ? (str_starts_with($principal->photo,'demo/') ? asset('images/'.$principal->photo) : Storage::url($principal->photo)) : asset('images/demo/principal.svg'))<img src="{{ $principalImage }}" alt="{{ $principal->name }}" class="principal-photo">
                    <div class="principal-badge"><i class="bi bi-patch-check-fill"></i><span>Kepala Madrasah<br><strong>{{ $principal->name }}</strong></span></div>
                </div>
            </div>
            <div class="col-lg-7"><span class="section-eyebrow">Sambutan Kepala Madrasah</span>
                <h2 class="section-title">Membangun generasi yang berakhlak, adaptif, dan berprestasi</h2>
                <div class="quote-mark">“</div>
                <p class="lead text-secondary">{{ $principal->bio }}</p>
                <p class="mb-4">Kami mengundang orang tua, masyarakat, alumni, dan seluruh pemangku kepentingan untuk bertumbuh bersama membangun ekosistem pendidikan yang aman, nyaman, inklusif, dan relevan dengan masa depan.</p><a href="{{ route('pages.show','selayang_pandang') }}" class="text-link">Kenali madrasah lebih dekat <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>
@endif

<section class="section-space bg-soft">
    <div class="container">
        <div class="section-heading d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
            <div><span class="section-eyebrow">Informasi Terkini</span>
                <h2 class="section-title mb-0">Berita dan kegiatan madrasah</h2>
            </div><a href="{{ route('posts.news') }}" class="btn btn-outline-brand">Lihat Semua Berita <i class="bi bi-arrow-right ms-2"></i></a>
        </div>
        <div class="row g-4 mt-2">
            @if($featured)
            <div class="col-lg-7">@php($featuredImage = $featured->image ? (str_starts_with($featured->image,'demo/') ? asset('images/'.$featured->image) : Storage::url($featured->image)) : asset('images/demo/news-osn.svg'))<article class="featured-card" style="background-image:linear-gradient(0deg,rgba(var(--ink-rgb),.94),rgba(var(--brand-rgb),.08)),url('{{ $featuredImage }}')">
                    <div class="featured-content"><span class="badge-soft-gold">Berita Utama</span>
                        <div class="post-meta text-white-50 mt-3"><span><i class="bi bi-calendar3"></i> {{ optional($featured->published_at)->translatedFormat('d F Y') }}</span><span><i class="bi bi-eye"></i> {{ number_format($featured->views) }}</span></div>
                        <h3><a href="{{ route('posts.show',$featured) }}">{{ $featured->title }}</a></h3>
                        <p>{{ $featured->excerpt }}</p>
                    </div>
                </article>
            </div>
            @endif
            <div class="col-lg-5">
                <div class="news-list h-100">@foreach($news->take(4) as $item)@php($newsImage = $item->image ? (str_starts_with($item->image,'demo/') ? asset('images/'.$item->image) : Storage::url($item->image)) : asset('images/demo/news-meeting.svg'))<article class="news-list-item"><img src="{{ $newsImage }}" alt="{{ $item->title }}">
                        <div>
                            <div class="post-meta"><span>{{ optional($item->published_at)->translatedFormat('d M Y') }}</span></div>
                            <h4><a href="{{ route('posts.show',$item) }}">{{ $item->title }}</a></h4><a class="read-more" href="{{ route('posts.show',$item) }}">Baca selengkapnya <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </article>@endforeach</div>
            </div>
        </div>
    </div>
</section>

<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div><i class="bi bi-people"></i><strong data-counter="{{ $siteSettings['student_count'] ?? 211 }}">{{ $siteSettings['student_count'] ?? 211 }}</strong><span>Peserta Didik</span></div>
            <div><i class="bi bi-person-workspace"></i><strong data-counter="{{ $siteSettings['teacher_count'] ?? 49 }}">{{ $siteSettings['teacher_count'] ?? 49 }}</strong><span>Guru & Tendik</span></div>
            <div><i class="bi bi-trophy"></i><strong>{{ $siteSettings['achievement_count'] ?? 35 }}</strong><span>Prestasi</span></div>
            <div><i class="bi bi-mortarboard"></i><strong>{{ $siteSettings['alumni_count'] ?? '2500+' }}</strong><span>Alumni</span></div>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div><span class="section-eyebrow">Agenda</span>
                        <h2 class="section-title mb-0">Kegiatan Mendatang</h2>
                    </div>
                </div>
                <div class="agenda-list">@forelse($events as $event)<article class="agenda-item">
                        <div class="agenda-date"><strong>{{ $event->starts_at->format('d') }}</strong><span>{{ $event->starts_at->translatedFormat('M') }}</span></div>
                        <div><span class="agenda-time"><i class="bi bi-clock"></i> {{ $event->starts_at->format('H:i') }} WIB</span>
                            <h4>{{ $event->title }}</h4>
                            <p><i class="bi bi-geo-alt"></i> {{ $event->location ?: 'MAN 1 Lampung Selatan' }}</p>
                        </div>
                    </article>@empty<div class="empty-state">Belum ada agenda mendatang.</div>@endforelse</div>
            </div>
            <div class="col-lg-5">
                <div class="announcement-panel"><span class="section-eyebrow text-warning">Pengumuman</span>
                    <h2 class="text-white mb-4">Informasi penting</h2>@forelse($announcements as $item)<a href="{{ route('posts.show',$item) }}" class="announcement-item"><span><i class="bi bi-megaphone"></i></span>
                        <div><small>{{ optional($item->published_at)->translatedFormat('d M Y') }}</small><strong>{{ $item->title }}</strong></div><i class="bi bi-arrow-up-right"></i>
                    </a>@empty<p class="text-white-50">Belum ada pengumuman.</p>@endforelse<a href="{{ route('posts.announcements') }}" class="btn btn-outline-light mt-4">Semua Pengumuman</a>
                </div>
            </div>
        </div>
    </div>
</section>

@if($achievements->isNotEmpty())<section class="section-space bg-soft">
    <div class="container">
        <div class="section-heading text-center mx-auto"><span class="section-eyebrow">Prestasi</span>
            <h2 class="section-title">Karya dan pencapaian terbaik</h2>
            <p>Apresiasi bagi peserta didik dan pembina yang terus mengharumkan nama madrasah.</p>
        </div>
        <div class="row g-4 mt-2">@foreach($achievements as $item)@php($img = $item->image ? (str_starts_with($item->image,'demo/') ? asset('images/'.$item->image) : Storage::url($item->image)) : asset('images/demo/achievement.svg'))<div class="col-md-6 col-lg-3">
                <article class="achievement-card"><img src="{{ $img }}" alt="{{ $item->title }}">
                    <div><span><i class="bi bi-award"></i> Prestasi</span>
                        <h4><a href="{{ route('posts.show',$item) }}">{{ $item->title }}</a></h4>
                    </div>
                </article>
            </div>@endforeach</div>
    </div>
</section>@endif

@if($infographics->isNotEmpty())
<section class="section-space bg-soft">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
            <div><span class="section-eyebrow">Infografis</span>
                <h2 class="section-title mb-0">Informasi penting dalam visual</h2>
            </div><a href="{{ route('infographics.index') }}" class="text-link">Lihat semua infografis <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">@foreach($infographics as $item)@php($img=str_starts_with($item->image,'demo/')?asset('images/'.$item->image):Storage::url($item->image))<div class="col-md-6 col-lg-3">
                <article class="infographic-card"><a href="{{ route('infographics.show',$item) }}"><img src="{{ $img }}" alt="{{ $item->title }}" loading="lazy"></a>
                    <div class="infographic-card-body"><small class="text-secondary">{{ optional($item->published_at)->translatedFormat('d M Y') }}</small>
                        <h3 class="mt-2"><a href="{{ route('infographics.show',$item) }}">{{ $item->title }}</a></h3><a href="{{ route('infographics.show',$item) }}" class="text-link">Lihat detail <i class="bi bi-arrow-right"></i></a>
                    </div>
                </article>
            </div>@endforeach</div>
    </div>
</section>
@endif

<section class="section-space">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
            <div><span class="section-eyebrow">Galeri Madrasah</span>
                <h2 class="section-title mb-0">Momen dan cerita dalam gambar</h2>
            </div><a href="{{ route('galleries.photos') }}" class="text-link">Lihat seluruh galeri <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="gallery-grid">@foreach($galleries as $gallery)@php($img = $gallery->image ? (str_starts_with($gallery->image,'demo/') ? asset('images/'.$gallery->image) : Storage::url($gallery->image)) : asset('images/demo/gallery-1.svg'))<a href="{{ $gallery->type==='video' ? ($gallery->video_url ?: '#') : $img }}" {{ $gallery->type==='video' ? 'target=_blank' : '' }} class="gallery-tile"><img src="{{ $img }}" alt="{{ $gallery->title }}"><span class="gallery-overlay"><i class="bi {{ $gallery->type==='video' ? 'bi-play-circle' : 'bi-arrows-fullscreen' }}"></i><strong>{{ $gallery->title }}</strong></span></a>@endforeach</div>
    </div>
</section>

@endsection