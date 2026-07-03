@extends('layouts.app')
@section('title', $channel->name)

@section('content')
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">{{ $channel->name }}</h1>
        <p class="text-muted text-sm mt-0.5">{{ $channel->country?->flag }} {{ $channel->country?->name }}</p>
    </div>
    <div class="flex gap-2.5">
        <a href="{{ route('channels.edit', $channel) }}"
           class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">Edit</a>
        <a href="{{ route('channels.index') }}"
           class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">← Back</a>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total Views',  number_format($channel->views),         'text-accent'],
        ['Sources',      $channel->sources->count(),             'text-accent2'],
        ['Categories',   $channel->categories->count(),          'text-gray-200'],
        ['Tags',         $channel->tags->count(),                'text-gray-200'],
    ] as [$label, $val, $color])
    <div class="bg-surface border border-border rounded-[10px] p-5">
        <p class="text-[0.72rem] uppercase tracking-wider text-muted">{{ $label }}</p>
        <p class="font-display font-bold text-3xl mt-1.5 {{ $color }}">{{ $val }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    {{-- Details --}}
    <div class="lg:col-span-2 bg-surface border border-border rounded-[10px] overflow-hidden">
        <div class="px-5 py-4 border-b border-border font-semibold text-sm">Details</div>
        <div class="p-5">
            @if($channel->description)
                <p class="text-muted text-sm leading-relaxed mb-5">{{ $channel->description }}</p>
            @endif
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[0.72rem] uppercase tracking-wider text-muted mb-1.5">Logo</p>
                    @if($channel->logo)
                        <img src="{{ Storage::disk('uploads')->url($channel->logo) }}" alt="Logo" class="max-h-16 max-w-full rounded-lg border border-border">
                    @else
                        <span class="text-muted text-sm">—</span>
                    @endif
                </div>
                <div>
                    <p class="text-[0.72rem] uppercase tracking-wider text-muted mb-1.5">Channel Image</p>
                    @if($channel->image)
                        <img src="{{ Storage::disk('uploads')->url($channel->image) }}" alt="Image" class="max-h-16 max-w-full rounded-lg border border-border">
                    @else
                        <span class="text-muted text-sm">—</span>
                    @endif
                </div>
                <div>
                    <p class="text-[0.72rem] uppercase tracking-wider text-muted mb-1">Created</p>
                    <p class="text-sm">{{ $channel->created_at->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-[0.72rem] uppercase tracking-wider text-muted mb-1">Updated</p>
                    <p class="text-sm">{{ $channel->updated_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="flex flex-col gap-4">
        {{-- Language & Quality --}}
        <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
            <div class="px-5 py-4 border-b border-border font-semibold text-sm">Stream Info</div>
            <div class="p-4 space-y-3">
                <div>
                    <p class="text-[0.68rem] uppercase tracking-wider text-muted mb-1">Language</p>
                    <p class="text-sm">{{ $channel->language ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[0.68rem] uppercase tracking-wider text-muted mb-1">Quality</p>
                    @if($channel->quality)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-accent/15 text-accent">{{ $channel->quality }}</span>
                    @else
                        <span class="text-sm text-muted">—</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
            <div class="px-5 py-4 border-b border-border font-semibold text-sm">Categories</div>
            <div class="p-4 flex flex-wrap gap-1.5">
                @forelse($channel->categories as $cat)
                    <span class="px-2.5 py-1 rounded-full text-[0.72rem] font-semibold bg-accent2/15 text-accent2"
                          @if($cat->color) style="background:{{ $cat->color }}22;color:{{ $cat->color }}" @endif>
                        {{ $cat->name }}
                    </span>
                @empty
                    <span class="text-muted text-xs">None</span>
                @endforelse
            </div>
        </div>
        <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
            <div class="px-5 py-4 border-b border-border font-semibold text-sm">Tags</div>
            <div class="p-4 flex flex-wrap gap-1.5">
                @forelse($channel->tags as $tag)
                    <span class="px-2.5 py-1 rounded-full text-[0.72rem] font-semibold bg-accent/15 text-accent">{{ $tag->name }}</span>
                @empty
                    <span class="text-muted text-xs">None</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Sources --}}
<div class="bg-surface border border-border rounded-[10px] overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <span class="font-semibold text-sm">Sources</span>
        <a href="{{ route('channels.sources.create', $channel) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-accent hover:bg-yellow-400 text-black text-xs font-medium rounded-lg transition-colors">
            + Add Source
        </a>
    </div>
    <div class="overflow-x-auto"><table class="w-full border-collapse">
        <thead>
            <tr class="border-b border-border">
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Type</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Link</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">DRM</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Clearkeys</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($channel->sources as $source)
            <tr class="border-b border-border last:border-0 hover:bg-white/[.02] transition-colors">
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
                <td class="px-4 py-3 max-w-xs">
                    <code class="text-xs text-muted truncate block">{!! $source->clearkeys_formatted ?? '—' !!}</code>
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
                        <a href="{{ route('sources.edit', $source) }}"
                           class="px-3 py-1.5 bg-border hover:bg-[#2e3748] text-gray-200 text-xs font-medium rounded-lg transition-colors">Edit</a>
                        <form action="{{ route('sources.destroy', $source) }}" method="POST"
                              onsubmit="return confirm('Delete source?')">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-xs font-medium rounded-lg transition-colors">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted text-sm">No sources yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection