<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    use HandlesUploads;

    public function index(Request $request)
    {
        $user = $request->user();
        $posts = Post::query()
            ->with('author')
            ->when(! $user->isAdmin(), fn ($q) => $q->where('author_id', $user->id))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create(Request $request)
    {
        return view('admin.posts.form', ['post' => new Post]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $user = $request->user();

        if (! $user->isAdmin()) {
            $data['category'] = 'artikel';
            $data['status'] = 'draft';
            $data['featured'] = false;
            $data['published_at'] = null;
        } else {
            $data['featured'] = $request->boolean('featured');
        }

        $data['image'] = $this->storeImage($request->file('image'), 'posts');
        $data['author_id'] = $user->id;
        $data['author_name'] = $user->name;
        Post::create($data);

        $message = $user->isAdmin()
            ? 'Konten berhasil ditambahkan.'
            : 'Artikel berhasil disimpan sebagai draft dan siap ditinjau administrator.';

        return redirect()->route('admin.posts.index')->with('success', $message);
    }

    public function edit(Request $request, Post $post)
    {
        $this->authorizeManagement($request, $post);
        return view('admin.posts.form', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorizeManagement($request, $post);
        $data = $this->validated($request, $post);
        $user = $request->user();

        if (! $user->isAdmin()) {
            $data['category'] = 'artikel';
            $data['status'] = 'draft';
            $data['featured'] = false;
            $data['published_at'] = null;
        } else {
            $data['featured'] = $request->boolean('featured');
        }

        $data['image'] = $this->storeImage($request->file('image'), 'posts', $post->image);
        $data['author_name'] = $post->author_name ?: $post->author?->name ?: $user->name;
        $post->update($data);

        return redirect()->route('admin.posts.index')->with('success', 'Konten berhasil diperbarui.');
    }

    public function destroy(Request $request, Post $post)
    {
        $this->authorizeManagement($request, $post);
        if ($post->image && ! str_starts_with($post->image, 'demo/')) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete();

        return back()->with('success', 'Konten berhasil dihapus.');
    }

    private function authorizeManagement(Request $request, Post $post): void
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return;
        }

        abort_unless($post->author_id === $user->id, 403, 'Anda hanya dapat mengelola artikel milik sendiri.');
        abort_unless($post->status === 'draft', 403, 'Artikel yang sudah diterbitkan hanya dapat diubah oleh administrator.');
    }

    private function validated(Request $request, ?Post $post = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($post?->id)],
            'category' => ['required', Rule::in(['berita', 'artikel', 'pengumuman', 'prestasi'])],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ]);
    }
}
