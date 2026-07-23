<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function show(Page $page)
    {
        abort_unless($page->status === 'published' && (! $page->published_at || $page->published_at->lte(now())), 404);
        return view('pages.show', compact('page'));
    }
}
