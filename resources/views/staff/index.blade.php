@extends('layouts.app')
@php($title=$type==='guru'?'Guru':'Pegawai')
@section('title',$title)
@section('content')
@include('partials.page-header',['title'=>$title,'subtitle'=>'GTK yang berdedikasi untuk layanan pendidikan terbaik'])
<section class="section-space">
    <div class="container"><div class="row g-4">@forelse($staff as $member)@php($img=$member->photo?(str_starts_with($member->photo,'demo/')?asset('images/'.$member->photo):Storage::url($member->photo)):asset('images/demo/person-1.svg'))
    <div class="col-6 col-md-4 col-lg-3"><article class="staff-card"><img src="{{ $img }}" alt="{{ $member->name }}">
    <div><h3>{{ $member->name }}</h3><strong>{{ $member->subject }} | {{ $member->position }}</strong>@if($member->simpeg_gol_ruang)<span>Pangkat | Gol : {{ $member->simpeg_pangkat }} {{ $member->simpeg_gol_ruang }}</span>@endif</div></article></div>@empty<div class="empty-state">Data belum tersedia.</div>@endforelse</div><div class="mt-5">{{ $staff->links() }}</div></div>
</section>
@endsection