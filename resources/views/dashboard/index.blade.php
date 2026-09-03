@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Dashboard</h1>
        <p class="text-muted text-sm mt-0.5">Overview of your streaming panel</p>
    </div>
    <a href="{{ route('channels.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-[10px] bg-accent text-black text-sm font-semibold hover:opacity-90 transition-opacity">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14"/>
        </svg>
        New channel
    </a>
</div>

{{-- Soketi realtime server --}}
<div class="mb-8 border-b border-border pb-7" @unless($soketi['enabled']) hidden @endunless>
    <div class="flex items-center gap-3 mb-3">
        <div>
            <h2 class="font-display font-bold text-lg">Realtime server</h2>
            <p class="text-muted text-xs mt-0.5">Soketi activity and runtime health</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full border text-[11px] font-medium {{ $soketi['online'] ? 'border-emerald-400/30 bg-emerald-500/10 text-emerald-400' : 'border-red-400/30 bg-red-500/10 text-red-400' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $soketi['online'] ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
            {{ $soketi['online'] ? 'Online' : 'Offline' }}
        </span>
    </div>

    @if($soketi['online'])
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4 font-sans">
            <div class="bg-surface border border-border rounded-[10px] p-3 transition duration-300 hover:scale-105 hover:border-accent/75">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="text-muted text-sm">Open connections</p>
                    <svg class="w-6 h-6 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
                </div>
                <p class="font-bold text-xl">{{ number_format($soketi['connections']) }}</p>
            </div>

            <div class="bg-surface border border-border rounded-[10px] p-3 transition duration-300 hover:scale-105 hover:border-accent/75">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="text-muted text-sm">Server started</p>
                    <svg class="w-6 h-6 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>
                </svg>
                </div>
                <p class="font-bold text-lg">{{ $soketi['startedAt']?->diffForHumans() ?? '—' }}</p>
            </div>

            <div class="bg-surface border border-border rounded-[10px] p-3 transition duration-300 hover:scale-105 hover:border-accent/75">
                <div class="flex items-center justify-between gap-3 mb-3">
                <p class="text-muted text-sm">Memory usage</p>
                <svg class="w-6 h-6 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="6" y="6" width="12" height="12" rx="1"/><path d="M9 2v4M15 2v4M9 18v4M15 18v4M2 9h4M2 15h4M18 9h4M18 15h4"/>
                </svg>
                </div>
                <p class="font-bold text-lg">{{ round($soketi['memory']['percent']) }}% of {{ $soketi['memory']['totalHuman'] }}</p>
            </div>

            <div class="bg-surface border border-border rounded-[10px] p-3 transition duration-300 hover:scale-105 hover:border-accent/75">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="text-muted text-sm">Messages sent</p>
                    <svg class="w-6 h-6 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
                    <path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/>
                </svg>
                </div>
                <p class="font-bold text-lg">{{ number_format($soketi['messagesSent']) }}</p>
            </div>

            <div class="bg-surface border border-border rounded-[10px] p-3 transition duration-300 hover:scale-105 hover:border-accent/75">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="text-muted text-sm">Messages received</p>
                    <svg class="w-6 h-6 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
                    <path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11L2 12v6a2 2 0 002 2h16a2 2 0 002-2v-6l-3.45-6.89A2 2 0 0016.76 4H7.24a2 2 0 00-1.79 1.11z"/>
                </svg>
                </div>
                <p class="font-bold text-lg">{{ number_format($soketi['messagesReceived']) }}</p>
            </div>

            <div class="bg-surface border border-border rounded-[10px] p-3 transition duration-300 hover:scale-105 hover:border-accent/75">
                <div class="flex items-center justify-between gap-3 mb-3">
                <p class="text-muted text-sm">Total connections</p>
                <svg class="w-6 h-6 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
                </div>
                <p class="font-bold text-lg">{{ number_format($soketi['connectionsTotal']) }}</p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-surface border border-red-500/20 rounded-[10px] px-5 py-4 text-sm text-muted">
            Error getting stats. Is Soketi running?
        </div>
    @endif
</div>

<div class="border-t border-border pt-6">
    <div class="flex items-end justify-between gap-3 mb-3">
        <div>
            <h2 class="font-display font-bold text-lg">Database overview</h2>
            <p class="text-muted text-xs mt-0.5">Content and moderation at a glance</p>
        </div>
        <span class="text-[0.65rem] uppercase tracking-widest text-muted">Local data</span>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
    <a href="{{ route('channels.index') }}"
    class="bg-surface border border-border rounded-[10px] p-3 hover:border-accent/40 transition-colors">
        <svg class="w-4 h-4 text-accent mb-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
        </svg>
        <p class="font-display font-bold text-lg">{{ $channels }}</p>
        <p class="text-muted text-xs mt-0.5">Channels · {{ $publishedChannels }} published</p>
    </a>

    <a href="{{ route('sources.index') }}"
    class="bg-surface border border-border rounded-[10px] p-3 hover:border-accent/40 transition-colors">
     <svg class="w-4 h-4 text-accent mb-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14M15.54 8.46a5 5 0 010 7.07M8.46 8.46a5 5 0 000 7.07"/>
        </svg>
        <p class="font-display font-bold text-lg">{{ $sources }}</p>
        <p class="text-muted text-xs mt-0.5">Sources · {{ $enabledSources }} enabled</p>
    </a>

    <a href="{{ route('categories.index') }}"
    class="bg-surface border border-border rounded-[10px] p-3 hover:border-accent/40 transition-colors">
     <svg class="w-4 h-4 text-accent mb-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <p class="font-display font-bold text-lg">{{ $categories }}</p>
        <p class="text-muted text-xs mt-0.5">Categories</p>
    </a>

    <a href="{{ route('tags.index') }}"
    class="bg-surface border border-border rounded-[10px] p-3 hover:border-accent/40 transition-colors">
     <svg class="w-4 h-4 text-accent mb-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><circle cx="7" cy="7" r="1"/>
        </svg>
        <p class="font-display font-bold text-lg">{{ $tags }}</p>
        <p class="text-muted text-xs mt-0.5">Tags</p>
    </a>

    <a href="{{ route('channels.index', ['sort' => 'views']) }}"
    class="bg-surface border border-border rounded-[10px] p-3 hover:border-accent/40 transition-colors">
     <svg class="w-4 h-4 text-accent mb-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
        <p class="font-display font-bold text-lg">{{ number_format($totalViews) }}</p>
        <p class="text-muted text-xs mt-0.5">Total views</p>
    </a>

    <a href="{{ route('reports.index') }}"
    class="bg-surface border border-border rounded-[10px] p-3 hover:border-accent/40 transition-colors">
     <svg class="w-4 h-4 mb-2.5 {{ $pendingReports > 0 ? 'text-red-400' : 'text-accent' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 22V4a1 1 0 0 1 .4-.8A6 6 0 0 1 8 2c3 0 5 2 7.333 2q2 0 3.067-.8A1 1 0 0 1 20 4v10a1 1 0 0 1-.4.8A6 6 0 0 1 16 16c-3 0-5-2-8-2a6 6 0 0 0-4 1.528"/>
        </svg>
        <p class="font-display font-bold text-lg">{{ $pendingReports }}</p>
        <p class="text-muted text-xs mt-0.5">Pending reports</p>
    </a>
</div>

<div class="mt-8">
    <p class="text-[0.68rem] uppercase tracking-widest text-muted mb-3">Quick actions</p>
    <div class="grid sm:grid-cols-3 gap-4">
        <a href="{{ route('channels.create') }}"
           class="flex items-center justify-between bg-surface border border-border rounded-[10px] px-4 py-3.5 text-sm font-medium hover:border-accent/40 hover:text-accent transition-colors">
            Create a channel
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </a>
        <a href="{{ route('sources.index') }}"
           class="flex items-center justify-between bg-surface border border-border rounded-[10px] px-4 py-3.5 text-sm font-medium hover:border-accent/40 hover:text-accent transition-colors">
            Manage sources
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </a>
        <a href="{{ route('sitemaps.index') }}"
           class="flex items-center justify-between bg-surface border border-border rounded-[10px] px-4 py-3.5 text-sm font-medium hover:border-accent/40 hover:text-accent transition-colors">
            Generate sitemaps
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </a>
    </div>
</div>
@endsection
