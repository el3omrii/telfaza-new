<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    @foreach($channels as $channel)
    <url>
        <loc>{{ $channel['url'] }}</loc>
        @if(!empty($channel['logo']) || !empty($channel['image']))
        @if(!empty($channel['logo']))
        <image:image>
            <image:loc>{{ $channel['logo'] }}</image:loc>
        </image:image>
        @endif
        @if(!empty($channel['image']))
        <image:image>
            <image:loc>{{ $channel['image'] }}</image:loc>
        </image:image>
        @endif
        @endif
    </url>
    @endforeach
</urlset>