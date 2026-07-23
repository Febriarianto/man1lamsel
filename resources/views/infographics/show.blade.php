@extends('layouts.app')
@php($imageUrl=str_starts_with($infographic->image,'demo/')?asset('images/'.$infographic->image):Storage::url($infographic->image))
@section('title',$infographic->meta_title ?: $infographic->title)
@section('meta_description',$infographic->meta_description ?: $infographic->description)
@section('meta_keywords',$infographic->meta_keywords)
@section('meta_image',$imageUrl)
@section('og_type','article')
@section('content')
<section class="article-header">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9"><span class="article-category">Infografis</span>
                <h1>{{ $infographic->title }}</h1>
                <div class="article-meta"><span><i class="bi bi-calendar3"></i> {{ optional($infographic->published_at)->translatedFormat('d F Y') }}</span><span><i class="bi bi-eye"></i> {{ number_format($infographic->views) }} dilihat</span>@if($infographic->source_name)<span><i class="bi bi-database"></i> {{ $infographic->source_name }}</span>@endif</div>
            </div>
        </div>
    </div>
</section>
<section class="section-space pt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-9"><a href="{{ $imageUrl }}" target="_blank" title="Buka ukuran penuh"><img src="{{ $imageUrl }}" class="infographic-detail-image" alt="{{ $infographic->title }}"></a>@if($infographic->description)<div class="article-body pb-3">{!! nl2br(e($infographic->description)) !!}</div>@endif @if($infographic->source_url)<p class="text-center"><a class="btn btn-outline-primary" target="_blank" rel="noopener" href="{{ $infographic->source_url }}"><i class="bi bi-box-arrow-up-right me-1"></i> Lihat Sumber Data</a></p>@endif<div class="share-box"><strong>Bagikan:</strong><a target="_blank" href="https://wa.me/?text={{ urlencode($infographic->title.' '.request()->url()) }}"><i class="bi bi-whatsapp"></i></a><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"><i class="bi bi-facebook"></i></a><button onclick="navigator.clipboard.writeText(location.href)" title="Salin tautan"><i class="bi bi-link-45deg"></i></button></div>
            </div>
        </div>
    </div>
</section>
@if($related->isNotEmpty())<section class="section-space bg-soft">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div><span class="section-eyebrow">Lainnya</span>
                <h2 class="section-title mb-0">Infografis terkait</h2>
            </div><a href="{{ route('infographics.index') }}" class="text-link">Lihat semua</a>
        </div>
        <div class="row g-4">@foreach($related as $item)@php($img=str_starts_with($item->image,'demo/')?asset('images/'.$item->image):Storage::url($item->image))<div class="col-md-6 col-lg-3">
                <article class="infographic-card"><a href="{{ route('infographics.show',$item) }}"><img src="{{ $img }}" alt="{{ $item->title }}"></a>
                    <div class="infographic-card-body">
                        <h3><a href="{{ route('infographics.show',$item) }}">{{ $item->title }}</a></h3>
                    </div>
                </article>
            </div>@endforeach</div>
    </div>
    @endif
</section>
@endsection