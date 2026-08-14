<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    @foreach($channels as $channel)
    <url>
        <loc>{{ $channel->pageUrl }}</loc>
        @if(!empty($channel->formattedLogo) || !empty($channel->formattedImage))
        @if(!empty($channel->formattedLogo))
        <image:image>
            <image:loc>{{ $channel->formattedLogo }}</image:loc>
        </image:image>
        @endif
        @if(!empty($channel->formattedImage))
        <image:image>
            <image:loc>{{ $channel->formattedImage }}</image:loc>
        </image:image>
        @endif
        @endif
    </url>
    @endforeach
</urlset>