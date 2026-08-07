@extends('layouts.app')
@php($seoImage = $post->image ? (str_starts_with($post->image, 'demo/') ? asset('images/'.$post->image) : Storage::url($post->image)) : null)
@php($categoryRoute = match($post->category) {
'artikel' => route('posts.articles'),
'pengumuman' => route('posts.announcements'),
'prestasi' => route('posts.achievements'),
'informasi' => route('posts.information'),
default => route('posts.news'),
})
@section('title', $post->meta_title ?: $post->title)
@section('meta_description', (string) ($post->meta_description ?: $post->excerpt))
@section('meta_keywords', (string) $post->meta_keywords)
@section('meta_image', (string) $seoImage)
@section('og_type', 'article')
@php($hasSidebar = $sidebarInfographics->isNotEmpty() || $latestPosts->isNotEmpty())

@section('content')
<article class="article-detail">
    <header class="article-header">
        <div class="container article-reading-wrap">
            <div class="row">
                <div class="col-12">
                    <div class="article-heading">
                        <a class="article-category" href="{{ $categoryRoute }}">{{ ucfirst($post->category) }}</a>
                        <h2 style="font-weight: 100px; font-size: 40px">{{ $post->title }}</h2>
                        @if($post->excerpt)<p class="article-summary">{{ $post->excerpt }}</p>@endif
                        <div class="article-meta">
                            <span><i class="bi bi-calendar3"></i> {{ optional($post->published_at)->translatedFormat('d F Y') }}</span>
                            <span><i class="bi bi-person"></i> {{ $post->author_display_name }}@if($post->author?->unit_name)<small> &middot; {{ $post->author->unit_name }}</small>@endif</span>
                            <span><i class="bi bi-eye"></i> {{ number_format($post->views) }} dibaca</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="article-reading-section">
        <div class="container article-reading-wrap">
            <div class="row g-4 align-items-start">
                <div class="{{ $hasSidebar ? 'col-lg-8' : 'col-12' }} article-main-column">
                    @if($post->image)
                    @php($img = str_starts_with($post->image, 'demo/') ? asset('images/'.$post->image) : Storage::url($post->image))
                    <figure class="article-cover-wrap">
                        <img src="{{ $img }}" class="article-cover" alt="{{ $post->title }}">
                    </figure>
                    @endif

                    <div class="article-content-card">
                        <div class="article-body">{!! $post->content !!}</div>

                        @if($post->attachment)
                        <div class="article-attachment">
                            <span class="article-attachment-icon"><i class="bi bi-file-earmark-arrow-down"></i></span>
                            <div>
                                <small>Lampiran</small>
                                <strong>{{ $post->attachment_name ?: basename($post->attachment) }}</strong>
                            </div>
                            <a href="{{ Storage::url($post->attachment) }}" class="btn btn-primary" download>
                                <i class="bi bi-download me-1"></i> Unduh
                            </a>
                        </div>
                        @endif

                        <div class="share-box">
                            <strong>Bagikan artikel</strong>
                            <div class="share-actions">
                                <a target="_blank" rel="noopener" href="https://wa.me/?text={{ urlencode($post->title.' '.request()->url()) }}" aria-label="Bagikan ke WhatsApp"><i class="bi bi-whatsapp"></i></a>
                                <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" aria-label="Bagikan ke Facebook"><i class="bi bi-facebook"></i></a>
                                <button type="button" onclick="navigator.clipboard.writeText(location.href)" title="Salin tautan" aria-label="Salin tautan"><i class="bi bi-link-45deg"></i></button>
                            </div>
                        </div>
                    </div>
                </div>


                @if($hasSidebar)
                <aside class="col-lg-4">
                    <div class="article-sidebar-sticky">
                        @if($sidebarInfographics->isNotEmpty())
                        <section class="article-sidebar-card">
                            <div class="article-sidebar-heading">
                                <div><span>Visual Data</span>
                                    <h2>Infografis</h2>
                                </div>
                                <a href="{{ route('infographics.index') }}" aria-label="Lihat semua infografis"><i class="bi bi-arrow-up-right"></i></a>
                            </div>
                            <div id="articleInfographicCarousel" class="carousel slide infographic-sidebar-carousel" data-bs-ride="carousel" data-bs-interval="4500" data-bs-pause="hover">
                                <div class="carousel-inner">
                                    @foreach($sidebarInfographics as $infographic)
                                    @php($infographicImage = str_starts_with($infographic->image, 'demo/') ? asset('images/'.$infographic->image) : Storage::url($infographic->image))
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                        <a href="{{ route('infographics.show', $infographic) }}" class="sidebar-infographic-card">
                                            <img src="{{ $infographicImage }}" alt="{{ $infographic->title }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                                            <div>
                                                <small>{{ optional($infographic->published_at)->translatedFormat('d M Y') }}</small>
                                                <h3>{{ $infographic->title }}</h3>
                                                <span>Lihat infografis <i class="bi bi-arrow-right"></i></span>
                                            </div>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                                @if($sidebarInfographics->count() > 1)
                                <div class="infographic-carousel-controls">
                                    <button type="button" data-bs-target="#articleInfographicCarousel" data-bs-slide="prev" aria-label="Infografis sebelumnya"><i class="bi bi-arrow-left"></i></button>
                                    <span>{{ $sidebarInfographics->count() }} infografis</span>
                                    <button type="button" data-bs-target="#articleInfographicCarousel" data-bs-slide="next" aria-label="Infografis berikutnya"><i class="bi bi-arrow-right"></i></button>
                                </div>
                                @endif
                            </div>
                        </section>
                        @endif

                        @if($latestPosts->isNotEmpty())
                        <section class="article-sidebar-card latest-news-widget">
                            <div class="article-sidebar-heading">
                                <div><span>Informasi Terbaru</span>
                                    <h2>Berita Terkini</h2>
                                </div>
                                <a href="{{ route('posts.news') }}" aria-label="Lihat semua berita"><i class="bi bi-arrow-up-right"></i></a>
                            </div>
                            <div class="latest-news-list">
                                @foreach($latestPosts as $latestPost)
                                @php($latestImage = $latestPost->image ? (str_starts_with($latestPost->image, 'demo/') ? asset('images/'.$latestPost->image) : Storage::url($latestPost->image)) : asset('images/demo/news-osn.svg'))
                                <article class="latest-news-item">
                                    <a href="{{ route('posts.show', $latestPost) }}" class="latest-news-thumb">
                                        <img src="{{ $latestImage }}" alt="{{ $latestPost->title }}" loading="lazy">
                                    </a>
                                    <div>
                                        <small><i class="bi bi-calendar3"></i> {{ optional($latestPost->published_at)->translatedFormat('d M Y') }}</small>
                                        <h3><a href="{{ route('posts.show', $latestPost) }}">{{ $latestPost->title }}</a></h3>
                                    </div>
                                </article>
                                @endforeach
                            </div>
                            <a href="{{ route('posts.news') }}" class="latest-news-more">Lihat semua berita <i class="bi bi-arrow-right"></i></a>
                        </section>
                        @endif
                    </div>
                </aside>
                @endif
            </div>
        </div>
    </section>
</article>

@if($related->isNotEmpty())
<section class="section-space bg-soft related-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
            <div><span class="section-eyebrow">Konten Terkait</span>
                <h2 class="section-title mb-0">Baca informasi lainnya</h2>
            </div>
        </div>
        <div class="row g-4">
            @foreach($related as $item)
            <div class="col-md-6 col-lg-3">
                <article class="mini-card">
                    <span>{{ optional($item->published_at)->translatedFormat('d M Y') }}</span>
                    <h4><a href="{{ route('posts.show', $item) }}">{{ $item->title }}</a></h4>
                    <a href="{{ route('posts.show', $item) }}" class="read-more">Baca <i class="bi bi-arrow-right"></i></a>
                </article>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection