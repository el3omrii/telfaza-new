@extends('layouts.app')

@section('title', 'Sitemaps')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="rounded-[18px] border border-border bg-surface p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.28em] text-accent">Settings</p>
                <h1 class="mt-2 text-2xl font-display font-bold tracking-tight text-white">Sitemaps</h1>
                <p class="mt-2 text-sm text-muted">Generate and review the sitemap endpoints for search engines.</p>
            </div>
        </div>
    </div>

    <div class="rounded-[18px] border border-border bg-surface p-6">
        <form method="GET" action="{{ route('sitemaps.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="frontend_url" class="mb-2 block text-sm font-medium text-white">Frontend base URL</label>
                <input id="frontend_url" name="frontend_url" type="url" value="{{ $frontendUrl }}"
                       class="w-full rounded-[10px] border border-border bg-bg px-3 py-2 text-sm text-white outline-none ring-0 focus:border-accent" />
            </div>
            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-accent px-4 py-2 text-sm font-semibold text-black transition hover:opacity-90">
                Apply
            </button>
        </form>

        <form method="POST" action="{{ route('sitemaps.generate') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf
            <input type="hidden" name="frontend_url" value="{{ $frontendUrl }}">
            <button type="submit" class="inline-flex items-center justify-center rounded-full border border-accent px-4 py-2 text-sm font-semibold text-accent transition hover:bg-accent/10">
                Generate sitemaps
            </button>
        </form>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-[18px] border border-border bg-surface p-6">
            <h2 class="text-lg font-semibold text-white">Standard sitemap</h2>
            <p class="mt-2 text-sm text-muted">Includes channels, categories, countries, and tags using slug-based URLs.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('sitemap.xml') }}" target="_blank" class="inline-flex items-center rounded-full bg-accent px-4 py-2 text-sm font-semibold text-black transition hover:opacity-90">
                    Open sitemap.xml
                </a>
                <span class="inline-flex items-center rounded-full border border-border px-3 py-2 text-xs font-medium text-muted">
                    {{ $sitemapExists ? 'Generated' : 'Not generated yet' }}
                </span>
            </div>
        </div>

        <div class="rounded-[18px] border border-border bg-surface p-6">
            <h2 class="text-lg font-semibold text-white">Video sitemap</h2>
            <p class="mt-2 text-sm text-muted">Publishes video metadata for channels that include an image.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('video-sitemap.xml') }}" target="_blank" class="inline-flex items-center rounded-full border border-border px-4 py-2 text-sm font-semibold text-white transition hover:bg-border">
                    Open video-sitemap.xml
                </a>
                <span class="inline-flex items-center rounded-full border border-border px-3 py-2 text-xs font-medium text-muted">
                    {{ $videoSitemapExists ? 'Generated' : 'Not generated yet' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-[18px] border border-border bg-surface p-6">
            <h2 class="text-lg font-semibold text-white">Image sitemap</h2>
            <p class="mt-2 text-sm text-muted">Publishes image metadata for channels that include a logo or image.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('image-sitemap.xml') }}" target="_blank" class="inline-flex items-center rounded-full border border-border px-4 py-2 text-sm font-semibold text-white transition hover:bg-border">
                    Open image-sitemap.xml
                </a>
                <span class="inline-flex items-center rounded-full border border-border px-3 py-2 text-xs font-medium text-muted">
                    {{ $imageSitemapExists ? 'Generated' : 'Not generated yet' }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
