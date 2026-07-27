<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
    @foreach($videos as $video)
    <url>
        <loc>{{ $video['content_loc'] }}</loc>
        @if(!empty($video['thumbnail']))
        <video:video>
            <video:title>{{ $video['title'] }}</video:title>
            <video:description>{{ $video['description'] }}</video:description>
            <video:thumbnail_loc>{{ $video['thumbnail'] }}</video:thumbnail_loc>
            <video:content_loc>{{ $video['content_loc'] }}</video:content_loc>
            @if(!empty($video['publication']))<video:publication_date>{{ $video['publication'] }}</video:publication_date>@endif
            <video:live>yes</video:live>
        </video:video>
        @endif
    </url>
    @endforeach
</urlset>
