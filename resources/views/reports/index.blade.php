@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Reports</h1>
        <p class="text-muted text-sm mt-0.5">{{ $reports->total() }} reports</p>
    </div>
</div>

<div class="bg-surface border border-border rounded-[10px] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="border-b border-border">
                    <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Channel</th>
                    <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Issue</th>
                    <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Details</th>
                    <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Status</th>
                    <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($reports as $report)
                <tr class="border-b border-border last:border-0 hover:bg-white/[.02] transition-colors">
                    <td class="px-4 py-3">
                        <div class="font-medium text-sm">{{ $report->channel?->name ?? 'Deleted channel' }}</div>
                        <p class="text-muted text-xs mt-0.5">{{ $report->created_at->format('M d, Y H:i') }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2.5 py-1 rounded-full text-[0.72rem] font-semibold bg-accent/15 text-accent">{{ str_replace('_', ' ', $report->issue_type) }}</span>
                    </td>
                    <td class="px-4 py-3 max-w-md">
                        <p class="text-sm text-gray-200">{{ Str::limit($report->details ?: 'No details provided.', 120) }}</p>
                        @if($report->user_agent)
                            <p class="text-muted text-xs mt-1">{{ Str::limit($report->user_agent, 90) }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($report->treated)
                            <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-green-500/15 text-green-400">Handled</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-red-500/15 text-red-400">Pending</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1.5">
                            <a href="{{ route('reports.edit', $report) }}"
                               class="px-3 py-1.5 bg-border hover:bg-[#2e3748] text-gray-200 text-xs font-medium rounded-lg transition-colors">Review</a>
                            <form action="{{ route('reports.destroy', $report) }}" method="POST"
                                  onsubmit="return confirm('Delete this report?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-xs font-medium rounded-lg transition-colors">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-muted text-sm">No reports found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $reports->links() }}</div>
@endsection
