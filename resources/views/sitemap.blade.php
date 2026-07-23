{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ url('/') }}</loc><lastmod>{{ now()->toAtomString() }}</lastmod><changefreq>daily</changefreq><priority>1.0</priority></url>
    @foreach([route('posts.news'),route('posts.articles'),route('posts.announcements'),route('posts.achievements'),route('infographics.index'),route('galleries.photos'),route('galleries.videos'),route('contact')] as $url)
    <url><loc>{{ $url }}</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>
    @endforeach
    @foreach($posts as $post)<url><loc>{{ route('posts.show',$post) }}</loc><lastmod>{{ $post->updated_at->toAtomString() }}</lastmod><changefreq>monthly</changefreq><priority>0.7</priority></url>@endforeach
    @foreach($pages as $page)<url><loc>{{ route('pages.show',$page) }}</loc><lastmod>{{ $page->updated_at->toAtomString() }}</lastmod><changefreq>monthly</changefreq><priority>0.7</priority></url>@endforeach
    @foreach($infographics as $item)<url><loc>{{ route('infographics.show',$item) }}</loc><lastmod>{{ $item->updated_at->toAtomString() }}</lastmod><changefreq>monthly</changefreq><priority>0.7</priority></url>@endforeach
</urlset>
