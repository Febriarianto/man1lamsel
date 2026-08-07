@extends('layouts.app')
@section('title','Hasil Pencarian')
@section('content')
@include('partials.page-header',['title'=>'Hasil Pencarian','subtitle'=>$query?'Kata kunci: '.$query:'Masukkan kata kunci pencarian'])
<section class="section-space">
    <div class="container">
        <form action="{{ route('search') }}" class="search-page-form mb-5"><input name="q" value="{{ $query }}" class="form-control form-control-lg" placeholder="Cari berita, artikel, pengumuman..."><button class="btn btn-primary btn-lg"><i class="bi bi-search"></i> Cari</button></form>
        <div class="list-group list-group-flush search-results">@forelse($posts as $post)<a class="list-group-item" href="{{ route('posts.show',$post) }}"><small>{{ ucfirst($post->category) }} • {{ optional($post->published_at)->translatedFormat('d M Y') }}</small>
                <h3>{{ $post->title }}</h3>
                <p>{{ $post->excerpt }}</p>
            </a>@empty<div class="empty-state">Tidak ada hasil yang ditemukan.</div>@endforelse</div>
        <div class="mt-4">{{ $posts->links() }}</div>
    </div>
</section>
@endsection