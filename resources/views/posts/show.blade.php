@extends('layouts.app')
@php($seoImage=$post->image?(str_starts_with($post->image,'demo/')?asset('images/'.$post->image):Storage::url($post->image)):null)
@section('title',$post->meta_title ?: $post->title)
@section('meta_description',$post->meta_description ?: $post->excerpt)
@section('meta_keywords',$post->meta_keywords)
@section('meta_image',$seoImage)
@section('og_type','article')
@section('content')
<article class="article-detail">
    <header class="article-header">
        <div class="container">
            <div class="article-heading">
                <a class="article-category" href="{{ match($post->category) {
                    'artikel' => route('posts.articles'),
                    'pengumuman' => route('posts.announcements'),
                    'prestasi' => route('posts.achievements'),
                    default => route('posts.news'),
                } }}">{{ ucfirst($post->category) }}</a>
                <h2>{{ $post->title }}</h2>
                @if($post->excerpt)<p class="article-summary">{{ $post->excerpt }}</p>@endif
                <div class="article-meta">
                    <span><i class="bi bi-calendar3"></i> {{ optional($post->published_at)->translatedFormat('d F Y') }}</span>
                    <span><i class="bi bi-person"></i> {{ $post->author_display_name }}@if($post->author?->unit_name)<small> · {{ $post->author->unit_name }}</small>@endif</span>
                    <span><i class="bi bi-eye"></i> {{ number_format($post->views) }} dibaca</span>
                </div>
            </div>
        </div>
    </header>

    <section class="article-reading-section">
        <div class="container article-reading-wrap">
            @if($post->image)
            @php($img=str_starts_with($post->image,'demo/')?asset('images/'.$post->image):Storage::url($post->image))
            <figure class="article-cover-wrap">
                <img src="{{ $img }}" class="article-cover" alt="{{ $post->title }}">
            </figure>
            @endif

            <div class="article-content-card">
                <div class="article-body">{!! $post->content !!}</div>
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
    </section>
</article>

@if($related->isNotEmpty())
<section class="section-space bg-soft related-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
            <div><span class="section-eyebrow">Bacaan Lainnya</span>
                <h2 class="section-title mb-0">Konten terkait</h2>
            </div>
        </div>
        <div class="row g-4">
            @foreach($related as $item)
            <div class="col-md-6 col-lg-3">
                <article class="mini-card">
                    <span>{{ optional($item->published_at)->translatedFormat('d M Y') }}</span>
                    <h4><a href="{{ route('posts.show',$item) }}">{{ $item->title }}</a></h4>
                    <a href="{{ route('posts.show',$item) }}" class="read-more">Baca <i class="bi bi-arrow-right"></i></a>
                </article>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection