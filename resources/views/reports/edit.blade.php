@extends('layouts.app')
@section('title', 'Review Report')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Review Report</h1>
        <p class="text-muted text-sm mt-0.5">{{ $report->channel?->name ?? 'Deleted channel' }}</p>
    </div>
    <a href="{{ route('reports.index') }}"
       class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">← Back</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[1.2fr_0.8fr] gap-6">
    <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
        <div class="px-5 py-4 border-b border-border font-semibold text-sm">Report Details</div>
        <div class="p-5 space-y-4">
            <div>
                <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Issue Type</label>
                <input name="issue_type" value="{{ old('issue_type', $report->issue_type) }}" readonly
                       class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none">
            </div>
            <div>
                <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Details</label>
                <textarea rows="6" readonly
                          class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 resize-none">{{ old('details', $report->details) }}</textarea>
            </div>
            <div>
                <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">User Agent</label>
                <input value="{{ $report->user_agent ?? '—' }}" readonly
                       class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200">
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
            <div class="px-5 py-4 border-b border-border font-semibold text-sm">Update Status</div>
            <div class="p-5">
                <form action="{{ route('reports.update', $report) }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Issue Type</label>
                        <input name="issue_type" value="{{ old('issue_type', $report->issue_type) }}" required
                               class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors">
                    </div>
                    <div>
                        <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Notes</label>
                        <textarea name="details" rows="5"
                                  class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors resize-none">{{ old('details', $report->details) }}</textarea>
                    </div>
                    <label class="flex items-center gap-2.5 text-sm text-gray-200 cursor-pointer">
                        <input type="checkbox" name="treated" value="1" {{ old('treated', $report->treated) ? 'checked' : '' }} class="w-4 h-4 rounded accent-accent bg-bg border-border">
                        Mark as handled
                    </label>
                    <button type="submit"
                            class="w-full py-2.5 bg-accent hover:bg-yellow-400 text-black font-display font-bold text-sm rounded-[10px] transition-colors">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
            <div class="px-5 py-4 border-b border-border font-semibold text-sm">Quick Actions</div>
            <div class="p-5">
                <form action="{{ route('reports.destroy', $report) }}" method="POST"
                      onsubmit="return confirm('Delete this report?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full py-2.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-sm font-medium rounded-[10px] transition-colors">
                        Delete Report
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
