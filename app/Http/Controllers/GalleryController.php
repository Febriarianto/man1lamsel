<?php

namespace App\Http\Controllers;

use App\Models\Gallery;

class GalleryController extends Controller
{
    public function photos() { return $this->index('photo'); }
    public function videos() { return $this->index('video'); }

    public function index(string $type = 'photo')
    {
        abort_unless(in_array($type, ['photo', 'video'], true), 404);
        $galleries = Gallery::query()->where('active', true)->where('type', $type)->latest('published_at')->paginate(12);
        return view('galleries.index', compact('galleries', 'type'));
    }
}
