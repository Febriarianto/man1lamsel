<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Infographic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InfographicController extends Controller
{
    use HandlesUploads;

    public function index(Request $request)
    {
        $infographics = Infographic::query()
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->q.'%'))
            ->orderBy('sort_order')->latest('published_at')->paginate(15)->withQueryString();

        return view('admin.infographics.index', compact('infographics'));
    }

    public function create()
    {
        return view('admin.infographics.form', ['infographic' => new Infographic]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image'] = $this->storeImage($request->file('image'), 'infographics');
        $data['active'] = $request->boolean('active');
        $data['featured'] = $request->boolean('featured');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        Infographic::create($data);

        return redirect()->route('admin.infographics.index')->with('success', 'Infografis berhasil ditambahkan.');
    }

    public function edit(Infographic $infographic)
    {
        return view('admin.infographics.form', compact('infographic'));
    }

    public function update(Request $request, Infographic $infographic)
    {
        $data = $this->validated($request, $infographic);
        $data['image'] = $this->storeImage($request->file('image'), 'infographics', $infographic->image);
        $data['active'] = $request->boolean('active');
        $data['featured'] = $request->boolean('featured');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $infographic->update($data);

        return redirect()->route('admin.infographics.index')->with('success', 'Infografis berhasil diperbarui.');
    }

    public function destroy(Infographic $infographic)
    {
        if ($infographic->image && ! str_starts_with($infographic->image, 'demo/')) {
            Storage::disk('public')->delete($infographic->image);
        }
        $infographic->delete();

        return back()->with('success', 'Infografis berhasil dihapus.');
    }

    private function validated(Request $request, ?Infographic $infographic = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('infographics', 'slug')->ignore($infographic?->id)],
            'description' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'image' => [$infographic?->exists ? 'nullable' : 'required', 'image', 'max:8192'],
            'source_name' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
    }
}
