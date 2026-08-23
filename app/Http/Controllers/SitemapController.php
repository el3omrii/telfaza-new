<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Country;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends Controller
{
    public function index(Request $request): View
    {
        $frontendUrl = $request->input('frontend_url', env('FRONTEND_URL', url('/')));
        $sitemapExists = file_exists(public_path('sitemap.xml'));
        $videoSitemapExists = file_exists(public_path('video-sitemap.xml'));
        $imageSitemapExists = file_exists(public_path('image-sitemap.xml'));

        return view('sitemaps.index', compact('frontendUrl', 'sitemapExists', 'videoSitemapExists', 'imageSitemapExists'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $frontendUrl = $this->normalizeFrontendUrl($request->input('frontend_url', env('FRONTEND_URL', url('/'))));

        $this->writeSitemap($frontendUrl);
        $this->writeVideoSitemap($frontendUrl);
        $this->writeImageSitemap($frontendUrl);

        return redirect()->route('sitemaps.index', ['frontend_url' => $frontendUrl])
            ->with('success', 'Sitemaps generated successfully.');
    }

    public function sitemap(): Response
    {
        $path = public_path('sitemap.xml');

        if (!file_exists($path)) {
            return response('Sitemap not generated yet.', 404);
        }

        return response()->file($path, ['Content-Type' => 'application/xml']);
    }

    public function videoSitemap(): Response
    {
        $path = public_path('video-sitemap.xml');

        if (!file_exists($path)) {
            return response('Video sitemap not generated yet.', 404);
        }

        return response()->file($path, ['Content-Type' => 'application/xml']);
    }

    public function imageSitemap(): Response
    {
        $path = public_path('image-sitemap.xml');

        if (!file_exists($path)) {
            return response('Image sitemap not generated yet.', 404);
        }

        return response()->file($path, ['Content-Type' => 'application/xml']);
    }

    private function writeSitemap(string $frontendUrl): void
    {
        $channels = Channel::query()
            ->with(['categories' => function ($query) {
                $query->whereNotNull('slug')->orderBy('name');
            }, 'tags' => function ($query) {
                $query->whereNotNull('slug')->orderBy('name');
            }])
            ->where('published', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        $categories = Category::query()->whereNotNull('slug')->orderBy('name')->get();
        $countries = Country::query()->whereNotNull('slug')->orderBy('name')->get();
        $tags = Tag::query()->whereNotNull('slug')->orderBy('name')->get();

        $urls = [];

        foreach ($channels as $channel) {
            $urls[] = [
                'loc'        => $this->buildUrl($frontendUrl, '/channels/' . $channel->slug),
                'lastmod'    => $channel->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            ];

            /*foreach ($channel->categories as $category) {
                $urls[] = [
                    'loc'        => $this->buildUrl($frontendUrl, '/' . $category->slug . '/' . $channel->slug),
                    'lastmod'    => $channel->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.75',
                ];
            }

            foreach ($channel->tags as $tag) {
                $urls[] = [
                    'loc'        => $this->buildUrl($frontendUrl, '/' . $tag->slug . '/' . $channel->slug),
                    'lastmod'    => $channel->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.7',
                ];
            }*/
        }

        foreach ($categories as $category) {
            $urls[] = [
                'loc'        => $this->buildUrl($frontendUrl, '/categories/' . $category->slug),
                'lastmod'    => $category->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority'   => '0.7',
            ];
        }

        foreach ($countries as $country) {
            $urls[] = [
                'loc'        => $this->buildUrl($frontendUrl, '/countries/' . $country->slug),
                'lastmod'    => $country->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority'   => '0.7',
            ];
        }

        foreach ($tags as $tag) {
            $urls[] = [
                'loc'        => $this->buildUrl($frontendUrl, '/tags/' . $tag->slug),
                'lastmod'    => $tag->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority'   => '0.6',
            ];
        }

        $xml = view('sitemaps.xml', compact('urls'))->render();
        file_put_contents(public_path('sitemap.xml'), $xml);
    }

    private function writeVideoSitemap(string $frontendUrl): void
    {
        $channels = Channel::query()
            ->where('published', true)
            ->whereNotNull('image')
            ->with(['sources' => function ($query) {
                $query->where('enabled', true);
            }])
            ->orderBy('updated_at', 'desc')
            ->get();

        $videos = [];

        foreach ($channels as $channel) {
            $videos[] = [
                'title'       => $channel->name,
                'description' => Str::limit(strip_tags($channel->description ?? ''), 160),
                'thumbnail'   => $channel->image ? env('APP_STORAGE_URL') . $channel->image : null,
                'url'         => $this->buildUrl($frontendUrl, '/channels/' . $channel->slug),
                'content_loc' => $channel->sources->first()?->link,
                'stream_loc'  => $this->buildUrl($frontendUrl, '/embed/' . $channel->slug),
                'publication' => $channel->created_at?->toAtomString(),
            ];
        }

        $xml = view('sitemaps.video-xml', compact('videos'))->render();
        file_put_contents(public_path('video-sitemap.xml'), $xml);
    }

    private function writeImageSitemap(string $frontendUrl): void
    {
        $channels = Channel::query()
            ->where('published', true)
            ->where(function($query) {
                $query->whereNotNull('logo')->orWhereNotNull('image');
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($channels as $channel) {
            $channel->formattedLogo = $channel->logo ? 'https://cdn.telfazalive.com/' . $channel->logo : null;
            $channel->formattedImage = $channel->image ? 'https://cdn.telfazalive.com/' . $channel->image : null;
            $channel->pageUrl = $this->buildUrl($frontendUrl, '/channels/' . $channel->slug);
        }

        $xml = view('sitemaps.image-xml', compact('channels'))->render();
        file_put_contents(public_path('image-sitemap.xml'), $xml);
    }

    private function normalizeFrontendUrl(?string $frontendUrl): string
    {
        $value = trim((string) ($frontendUrl ?? env('FRONTEND_URL', url('/'))));

        return rtrim($value, '/');
    }

    private function buildUrl(string $frontendUrl, string $path): string
    {
        return rtrim($frontendUrl, '/') . '/' . ltrim($path, '/');
    }
}
