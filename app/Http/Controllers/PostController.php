<?php

namespace App\Http\Controllers;

use App\Models\Infographic;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function news(Request $request) { return $this->index($request, 'berita'); }
    public function articles(Request $request) { return $this->index($request, 'artikel'); }
    public function announcements(Request $request) { return $this->index($request, 'pengumuman'); }
    public function achievements(Request $request) { return $this->index($request, 'prestasi'); }
    public function information(Request $request) { return $this->index($request, 'informasi'); }

    public function index(Request $request, string $category = 'berita')
    {
        abort_unless(in_array($category, ['berita', 'artikel', 'pengumuman', 'prestasi', 'informasi'], true), 404);
        $posts = Post::published()->with('author')->where('category', $category)->latest('published_at')->paginate(9);

        return view('posts.index', compact('posts', 'category'));
    }

    public function show(Post $post)
    {
        $post->load('author');
        abort_unless($post->status === 'published' && (! $post->published_at || $post->published_at->lte(now())), 404);
        $post->increment('views');
        $related = Post::published()->where('category', $post->category)->where('id', '!=', $post->id)->latest('published_at')->take(4)->get();
        $sidebarInfographics = Infographic::published()
            ->orderBy('sort_order')
            ->latest('published_at')
            ->take(6)
            ->get();

        $latestPosts = Post::published()
            ->where('category', 'berita')
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->take(5)
            ->get();

        return view('posts.show', compact('post', 'related', 'sidebarInfographics', 'latestPosts'));
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->get('q'));
        $posts = Post::published()->with('author')
            ->when($query, fn ($q) => $q->where(fn ($sub) => $sub->where('title', 'like', "%{$query}%")->orWhere('content', 'like', "%{$query}%")))
            ->latest('published_at')->paginate(10)->withQueryString();

        return view('posts.search', compact('posts', 'query'));
    }
}
