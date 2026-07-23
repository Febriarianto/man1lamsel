@extends('layouts.app')
@section('title','Infografis')
@section('meta_description','Kumpulan infografis, data, prestasi, dan informasi visual MAN 1 Lampung Selatan.')
@section('content')
@include('partials.page-header',['title'=>'Infografis','subtitle'=>'Data dan informasi madrasah dalam visual yang ringkas dan mudah dipahami'])
<section class="section-space">
<div class="container">
@if($featured)
@php($featuredImage=str_starts_with($featured->image,'demo/')?asset('images/'.$featured->image):Storage::url($featured->image))
<div class="infographic-feature mb-5">
    <a href="{{ route('infographics.show',$featured) }}"><img src="{{ $featuredImage }}" alt="{{ $featured->title }}"></a>
    <div class="infographic-feature-content"><span class="section-eyebrow">Infografis Unggulan</span><h2>{{ $featured->title }}</h2><p class="text-secondary">{{ Str::limit($featured->description,220) }}</p><div class="infographic-meta mb-4"><span><i class="bi bi-calendar3"></i> {{ optional($featured->published_at)->translatedFormat('d F Y') }}</span><span><i class="bi bi-eye"></i> {{ number_format($featured->views) }} dilihat</span></div><a href="{{ route('infographics.show',$featured) }}" class="btn btn-primary align-self-start">Lihat Infografis <i class="bi bi-arrow-right ms-1"></i></a></div>
</div>
@endif
<div class="row g-4">
@forelse($infographics as $item)
@php($img=str_starts_with($item->image,'demo/')?asset('images/'.$item->image):Storage::url($item->image))
<div class="col-md-6 col-lg-4"><article class="infographic-card"><a href="{{ route('infographics.show',$item) }}"><img src="{{ $img }}" alt="{{ $item->title }}" loading="lazy"></a><div class="infographic-card-body"><small class="text-secondary">{{ optional($item->published_at)->translatedFormat('d F Y') }}</small><h3 class="mt-2"><a href="{{ route('infographics.show',$item) }}">{{ $item->title }}</a></h3><p>{{ Str::limit($item->description,110) }}</p><a href="{{ route('infographics.show',$item) }}" class="text-link">Buka infografis <i class="bi bi-arrow-right"></i></a></div></article></div>
@empty
<div class="col-12"><div class="empty-state">Belum ada infografis yang dipublikasikan.</div></div>
@endforelse
</div>
<div class="mt-5">{{ $infographics->links() }}</div>
</div>
</section>
@endsection
