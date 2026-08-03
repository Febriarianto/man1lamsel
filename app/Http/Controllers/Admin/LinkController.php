<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function index(Request $request)
    {
        $term = $request->string('q')->trim();
        $links = Link::query()
            ->when($term->isNotEmpty(), fn ($query) => $query->where(function ($search) use ($term): void {
                $value = '%'.$term.'%';
                $search->where('name', 'like', $value)
                    ->orWhere('url', 'like', $value)
                    ->orWhere('icon', 'like', $value);
            }))
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        return view('admin.links.index', compact('links'));
    }

    public function create()
    {
        return view('admin.links.form', ['link' => new Link]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['active'] = $request->boolean('active');
        Link::create($data);

        return redirect()->route('admin.links.index')->with('success', 'Tautan berhasil ditambahkan.');
    }

    public function edit(Link $link)
    {
        return view('admin.links.form', compact('link'));
    }

    public function update(Request $request, Link $link)
    {
        $data = $this->validated($request);
        $data['active'] = $request->boolean('active');
        $link->update($data);

        return redirect()->route('admin.links.index')->with('success', 'Tautan berhasil diperbarui.');
    }

    public function destroy(Link $link)
    {
        $link->delete();

        return back()->with('success', 'Tautan berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:150'], 'url' => ['required', 'string', 'max:500'], 'icon' => ['required', 'string', 'max:100'], 'sort_order' => ['required', 'integer', 'min:0']]);
    }
}
