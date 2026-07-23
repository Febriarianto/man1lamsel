<?php

namespace App\Http\Controllers;

use App\Models\Infographic;

class InfographicController extends Controller
{
    public function index()
    {
        $featured = Infographic::published()->where('featured', true)
            ->orderBy('sort_order')->latest('published_at')->first();
        $infographics = Infographic::published()
            ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))
            ->orderBy('sort_order')->latest('published_at')->paginate(12);

        return view('infographics.index', compact('featured', 'infographics'));
    }

    public function show(Infographic $infographic)
    {
        abort_unless($infographic->active && (! $infographic->published_at || $infographic->published_at->lte(now())), 404);
        $infographic->increment('views');
        $related = Infographic::published()->where('id', '!=', $infographic->id)
            ->orderBy('sort_order')->latest('published_at')->take(4)->get();

        return view('infographics.show', compact('infographic', 'related'));
    }
}
