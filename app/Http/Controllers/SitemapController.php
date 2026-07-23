<?php

namespace App\Http\Controllers;

use App\Models\Infographic;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $posts = Post::published()->latest('updated_at')->get();
        $pages = Page::published()->latest('updated_at')->get();
        $infographics = Infographic::published()->latest('updated_at')->get();

        return response()
            ->view('sitemap', compact('posts', 'pages', 'infographics'))
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $indexing = Setting::value('seo_indexing', '1') === '1';
        $content = "User-agent: *\n";
        $content .= $indexing ? "Allow: /\n" : "Disallow: /\n";
        $content .= "Disallow: /admin\n";
        $content .= 'Sitemap: '.url('/sitemap.xml')."\n";

        return response($content, 200)->header('Content-Type', 'text/plain');
    }
}
