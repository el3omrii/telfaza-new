@extends('layouts.app')
@section('title', $category->name)

@section('content')
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-7">
    <div class="flex items-center gap-2">
        @if($category->color)
            <span class="w-3.5 h-3.5 rounded-full shrink-0 mt-1" style="background:{{ $category->color }}"></span>
        @endif
        <div>
            <h1 class="font-display font-bold text-2xl">{{ $category->name }}</h1>
            @if($category->description)<p class="text-muted text-sm mt-0.5">{{ $category->description }}</p>@endif
        </div>
    </div>
    <div class="flex gap-2.5">
        <a href="{{ route('categories.edit', $category) }}"
           class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">Edit</a>
        <a href="{{ route('categories.index') }}"
           class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">← Back</a>
    </div>
</div>

<div class="bg-surface border border-border rounded-[10px] overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <span class="font-semibold text-sm">Channels in this category</span>
        <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-accent2/15 text-accent2">{{ $channels->total() }}</span>
    </div>
    <div class="overflow-x-auto"><table class="w-full border-collapse">
        <thead>
            <tr class="border-b border-border">
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Channel</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Country</th>
                <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Views</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
        @forelse($channels as $channel)
            <tr class="border-b border-border last:border-0 hover:bg-white/[.02] transition-colors">
                <td class="px-4 py-3 font-medium text-sm">{{ $channel->name }}</td>
                <td class="px-4 py-3 text-sm">{{ $channel->country?->flag }} {{ $channel->country?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm">{{ number_format($channel->views) }}</td>
                <td class="px-4 py-3">
                    <a href="{{ route('channels.show', $channel) }}"
                       class="px-3 py-1.5 bg-border hover:bg-[#2e3748] text-gray-200 text-xs font-medium rounded-lg transition-colors">View</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-4 py-10 text-center text-muted text-sm">No channels in this category.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</table></div></div>
<div class="mt-5">{{ $channels->links() }}</div>
@endsection