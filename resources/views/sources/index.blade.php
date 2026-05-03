@extends('layouts.app')
@section('title', 'Sources')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Sources</h1>
        <p class="text-muted text-sm mt-0.5">{{ $sources->total() }} stream sources across all channels</p>
    </div>
</div>

<div class="bg-surface border border-border rounded-[10px] overflow-hidden"><div class="overflow-x-auto">
    <table class="w-full border-collapse">
        <thead>
            <tr class="border-b border-border">
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Channel</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Type</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Stream URL</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">DRM</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Clearkeys</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($sources as $source)
            <tr class="border-b border-border last:border-0 hover:bg-white/[.02] transition-colors">
                <td class="px-4 py-3">
                    <a href="{{ route('channels.show', $source->channel) }}" class="text-accent hover:underline font-medium text-sm">
                        {{ $source->channel->name }}
                    </a>
                </td>
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
                <td class="px-4 py-3 text-sm">{{ $source->clearkeys ?? '—' }}</td>
                <td class="px-4 py-3">
                    <div class="flex gap-1.5">
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
        @empty
            <tr><td colspan="6" class="px-4 py-10 text-center text-muted text-sm">No sources yet. Add one from a channel page.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

</div></div>
<div class="mt-5">{{ $sources->links() }}</div>
@endsection