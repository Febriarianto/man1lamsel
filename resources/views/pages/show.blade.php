@extends('layouts.app')
@php($seoImage=$page->image?(str_starts_with($page->image,'demo/')?asset('images/'.$page->image):Storage::url($page->image)):null)
@section('title',$page->meta_title ?: $page->title)
@section('meta_description',$page->meta_description ?: $page->excerpt)
@section('meta_keywords',$page->meta_keywords)
@section('meta_image',$seoImage)
@section('content')
@include('partials.page-header',['title'=>$page->title,'subtitle'=>$page->excerpt])
<section class="section-space"><div class="container"><div class="row justify-content-center"><div class="col-lg-10">@if($page->image)@php($img=str_starts_with($page->image,'demo/')?asset('images/'.$page->image):Storage::url($page->image))<img src="{{ $img }}" class="page-cover" alt="{{ $page->title }}">@endif<div class="page-content">{!! $page->content !!}</div></div></div></div></section>
@endsection