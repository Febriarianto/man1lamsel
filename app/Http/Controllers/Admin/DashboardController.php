<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Gallery;
use App\Models\Infographic;
use App\Models\Page;
use App\Models\Post;
use App\Models\Staff;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            $ownPosts = Post::where('author_id', $user->id);
            $stats = [
                'posts' => (clone $ownPosts)->count(),
                'drafts' => (clone $ownPosts)->where('status', 'draft')->count(),
                'published' => (clone $ownPosts)->where('status', 'published')->count(),
            ];
            $latestPosts = $ownPosts->latest()->take(8)->get();
            $latestMessages = collect();

            return view('admin.dashboard', compact('stats', 'latestPosts', 'latestMessages'));
        }

        $stats = [
            'posts' => Post::count(),
            'pages' => Page::count(),
            'staff' => Staff::count(),
            'galleries' => Gallery::count(),
            'infographics' => Infographic::count(),
            'messages' => ContactMessage::whereNull('read_at')->count(),
        ];
        $latestPosts = Post::with('author')->latest()->take(6)->get();
        $latestMessages = ContactMessage::latest()->take(6)->get();

        return view('admin.dashboard', compact('stats', 'latestPosts', 'latestMessages'));
    }
}
