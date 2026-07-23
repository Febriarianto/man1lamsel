@extends('layouts.app')
@php($title=$type==='photo'?'Galeri Foto':'Galeri Video')
@section('title',$title)
@section('content')
@include('partials.page-header',['title'=>$title,'subtitle'=>'Dokumentasi aktivitas, prestasi, dan kehidupan madrasah'])
<section class="section-space"><div class="container"><div class="row g-4">@forelse($galleries as $gallery)@php($img=$gallery->image?(str_starts_with($gallery->image,'demo/')?asset('images/'.$gallery->image):Storage::url($gallery->image)):asset('images/demo/gallery-1.svg'))<div class="col-md-6 col-lg-4"><a class="gallery-card" href="{{ $type==='video'?($gallery->video_url?:'#'):$img }}" target="_blank"><img src="{{ $img }}" alt="{{ $gallery->title }}"><div class="gallery-card-overlay"><i class="bi {{ $type==='video'?'bi-play-circle':'bi-arrows-fullscreen' }}"></i><h3>{{ $gallery->title }}</h3><p>{{ $gallery->description }}</p></div></a></div>@empty<div class="empty-state">Galeri belum tersedia.</div>@endforelse</div><div class="mt-5">{{ $galleries->links() }}</div></div></section>
@endsection