@extends('layouts.app')
@section('title', 'Channels')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Channels</h1>
        <p class="text-muted text-sm mt-0.5">{{ $channels->total() }} channels total</p>
    </div>
    <a href="{{ route('channels.create') }}"
       class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-accent hover:bg-yellow-400 text-black text-sm font-medium rounded-[10px] transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        New Channel
    </a>
</div>

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-2.5 mb-5">
    <input name="search" value="{{ request('search') }}" placeholder="Search by name…"
           class="px-3 py-2 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors w-48">
    <select name="category"
            class="px-3 py-2 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors">
        <option value="">All categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <select name="country"
            class="px-3 py-2 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors">
        <option value="">All countries</option>
        @foreach($countries as $c)
            <option value="{{ $c->id }}" {{ request('country') == $c->id ? 'selected' : '' }}>{{ $c->flag }} {{ $c->name }}</option>
        @endforeach
    </select>
    <select name="sort"
            class="px-3 py-2 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors">
        <option value="views" {{ request('sort', 'newest') === 'views' ? 'selected' : '' }}>Sort by: Most Viewed</option>
        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Sort by: Newest</option>
        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Sort by: Name A-Z</option>
        <option value="status" {{ request('sort') === 'status' ? 'selected' : '' }}>Sort by: Status</option>
    </select>
    <button type="submit"
            class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">Filter</button>
    <a href="{{ route('channels.index') }}"
       class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">Reset</a>
</form>

{{-- Table --}}
<div class="bg-surface border border-border rounded-[10px] overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full border-collapse">
        <thead>
            <tr class="border-b border-border">
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Channel</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Country</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Categories</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Sources</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Status</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($channels as $channel)
            <tr class="border-b border-border last:border-0 hover:bg-white/[.02] transition-colors">
                <td class="px-4 py-3">
                    <span class="font-medium text-sm">{{ $channel->name }}</span>
                    @if($channel->description)
                        <p class="text-muted text-xs mt-0.5">{{ Str::limit($channel->description, 60) }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm flex items-center gap-1.5">
                    <img src="//flagcdn.com/{{ strtolower($channel->country?->flag ?? '') }}.svg" alt="{{ $channel->country?->name ?? '—' }}" class="w-8 h-8 rounded-xl">
                    {{ $channel->country?->name ?? '—' }}
                </td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-1">
                        @foreach($channel->categories->take(3) as $cat)
                            <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-accent2/15 text-accent2">{{ $cat->name }}</span>
                        @endforeach
                        @if($channel->categories->count() > 3)
                            <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-border text-muted">+{{ $channel->categories->count() - 3 }}</span>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-accent/15 text-accent">{{ $channel->sources_count }}</span>
                </td>
                <td class="px-4 py-3">
                    @if($channel->published)
                        <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-green-500/15 text-green-400">Published</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-red-500/15 text-red-400">Unpublished</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('channels.show', $channel) }}"
                           class="px-3 py-1.5 bg-border hover:bg-[#2e3748] text-gray-200 text-xs font-medium rounded-lg transition-colors">View</a>
                        <a href="{{ route('channels.edit', $channel) }}"
                           class="px-3 py-1.5 bg-border hover:bg-[#2e3748] text-gray-200 text-xs font-medium rounded-lg transition-colors">Edit</a>
                        <form action="{{ route('channels.destroy', $channel) }}" method="POST"
                              onsubmit="return confirm('Delete this channel?')">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-xs font-medium rounded-lg transition-colors">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-10 text-center text-muted text-sm">No channels found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

</div>
<div class="mt-5">{{ $channels->links() }}</div>
@endsection