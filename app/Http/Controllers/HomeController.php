<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\Link;
use App\Models\Infographic;
use App\Models\Post;
use App\Models\Staff;

class HomeController extends Controller
{
    public function __invoke()
    {
        $banners = Banner::query()->where('active', true)->orderBy('sort_order')->get();
        $featured = Post::published()->where('featured', true)->latest('published_at')->first()
            ?? Post::published()->latest('published_at')->first();
        $news = Post::published()->where('category', 'berita')->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))->latest('published_at')->take(6)->get();
        $articles = Post::published()->where('category', 'artikel')->latest('published_at')->take(3)->get();
        $announcements = Post::published()->where('category', 'pengumuman')->latest('published_at')->take(4)->get();
        $achievements = Post::published()->where('category', 'prestasi')->latest('published_at')->take(4)->get();
        $events = Event::query()->where('active', true)->where('starts_at', '>=', now()->startOfDay())->orderBy('starts_at')->take(4)->get();
        $galleries = Gallery::query()->where('active', true)->latest('published_at')->take(8)->get();
        $links = Link::query()->where('active', true)->orderBy('sort_order')->get();
        $infographics = Infographic::published()->orderBy('sort_order')->latest('published_at')->take(4)->get();
        $principal = Staff::query()->where('active', true)->where('type', 'principal')->first();

        return view('home', compact('banners', 'featured', 'news', 'articles', 'announcements', 'achievements', 'events', 'galleries', 'links', 'infographics', 'principal'));
    }
}
