@extends('layouts.app')
@section('title', 'Sources')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Sources</h1>
        <p class="text-muted text-sm mt-0.5">{{ $sources->total() }} stream sources across all channels</p>
    </div>
</div>

<div class="bg-surface border border-border rounded-[10px] overflow-hidden">
    <div class="border-b border-border px-4 py-3">
        <form method="GET" class="flex flex-wrap items-center gap-2.5">
            <input name="channel" value="{{ request('channel') }}" placeholder="Search by channel…"
                   class="px-3 py-2 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors w-48">
            <input name="link" value="{{ request('link') }}" placeholder="Search by link…"
                   class="px-3 py-2 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors w-56">
            <button type="submit"
                    class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">Filter</button>
            @if(request()->hasAny(['channel', 'link']))
                <a href="{{ route('sources.index') }}"
                   class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
        <thead>
            <tr class="border-b border-border">
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Channel</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Type</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Stream URL</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">DRM</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($groupedSources as $group)
            @php($channel = $group->first()->channel)
            @foreach($group as $index => $source)
                <tr class="border-b border-border last:border-0 hover:bg-white/[.02] transition-colors">
                    @if($index === 0)
                        <td rowspan="{{ $group->count() }}" class="px-4 py-3 align-top">
                            <a href="{{ route('channels.show', $channel) }}" class="text-accent hover:underline font-semibold text-sm">
                                {{ $channel->name }}
                            </a>
                        </td>
                    @endif
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-accent/15 text-accent">{{ strtoupper($source->type) }}</span>
                    </td>
                    <td class="px-4 py-3 max-w-xs">
                        <code class="text-xs text-muted truncate block">{{ $source->link ?? '—' }}</code>
                    </td>
                    <td class="px-4 py-3">
                        @if($source->drm)
                            <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-green-500/15 text-green-400">Yes</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-border text-muted">No</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <form action="{{ route('sources.toggle', $source) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="enabled" value="1" class="sr-only peer"
                                           {{ $source->enabled ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="relative h-5 w-10 rounded-full bg-border transition-colors peer-checked:bg-green-500/30">
                                        <span class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
                                    </span>
                                    <span class="ml-2 text-[0.72rem] font-medium {{ $source->enabled ? 'text-green-400' : 'text-muted' }}">{{ $source->enabled ? 'On' : 'Off' }}</span>
                                </label>
                            </form>
                            <a href="{{ route('channels.sources.create', $source->channel) }}"
                               class="px-3 py-1.5 bg-border hover:bg-[#2e3748] text-gray-200 text-xs font-medium rounded-lg transition-colors" title="Add source to channel">+ Source</a>
                            <a href="{{ route('sources.edit', $source) }}"
                               class="px-3 py-1.5 bg-border hover:bg-[#2e3748] text-gray-200 text-xs font-medium rounded-lg transition-colors">Edit</a>
                            <form action="{{ route('sources.destroy', $source) }}" method="POST"
                                  onsubmit="return confirm('Delete this source?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-xs font-medium rounded-lg transition-colors">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @empty
            <tr><td colspan="5" class="px-4 py-10 text-center text-muted text-sm">No sources yet. Add one from a channel page.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

</div></div>
<div class="mt-5">{{ $sources->links() }}</div>
@endsection