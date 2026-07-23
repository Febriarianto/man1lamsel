<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    use HandlesUploads;
    public function index() { $pages = Page::latest()->paginate(15); return view('admin.pages.index', compact('pages')); }
    public function create() { return view('admin.pages.form', ['page' => new Page]); }
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image'] = $this->storeImage($request->file('image'), 'pages');
        Page::create($data);
        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil ditambahkan.');
    }
    public function edit(Page $page) { return view('admin.pages.form', compact('page')); }
    public function update(Request $request, Page $page)
    {
        $data = $this->validated($request, $page);
        $data['image'] = $this->storeImage($request->file('image'), 'pages', $page->image);
        $page->update($data);
        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil diperbarui.');
    }
    public function destroy(Page $page)
    {
        if ($page->image && ! str_starts_with($page->image, 'demo/')) Storage::disk('public')->delete($page->image);
        $page->delete();
        return back()->with('success', 'Halaman berhasil dihapus.');
    }
    private function validated(Request $request, ?Page $page = null): array
    {
        return $request->validate([
            'title'=>['required','string','max:255'],
            'meta_title'=>['nullable','string','max:255'],
            'slug'=>['nullable','string','max:255',Rule::unique('pages','slug')->ignore($page?->id)],
            'excerpt'=>['nullable','string','max:500'],
            'meta_description'=>['nullable','string','max:500'],
            'meta_keywords'=>['nullable','string','max:500'],
            'content'=>['required','string'],
            'image'=>['nullable','image','max:4096'],
            'status'=>['required',Rule::in(['draft','published'])],
            'published_at'=>['nullable','date'],
        ]);
    }
}
