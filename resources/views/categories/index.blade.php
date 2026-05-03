@extends('layouts.app')
@section('title', 'Categories')

@section('content')
<div class="flex items-center justify-between mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Categories</h1>
        <p class="text-muted text-sm mt-0.5">{{ $categories->total() }} categories</p>
    </div>
</div>

<div class="grid grid-cols-2 gap-6">
    {{-- Add form --}}
    <div class="bg-surface border border-border rounded-[10px] overflow-hidden self-start">
        <div class="px-5 py-4 border-b border-border font-semibold text-sm">Add Category</div>
        <div class="p-5">
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Name *</label>
                    <input name="name" value="{{ old('name') }}" required placeholder="e.g. News"
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                </div>
                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Description</label>
                    <input name="description" value="{{ old('description') }}" placeholder="Optional description"
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                </div>
                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Color</label>
                    <div class="flex gap-2.5 items-center">
                        <input type="color" name="color" value="{{ old('color', '#3b82f6') }}"
                               class="w-11 h-10 rounded-lg border border-border bg-bg cursor-pointer p-1">
                        <input name="color_hex" value="{{ old('color', '#3b82f6') }}" placeholder="#3b82f6" maxlength="7"
                               class="flex-1 px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm font-mono text-gray-200 focus:outline-none focus:border-accent transition-colors">
                    </div>
                </div>
                <button type="submit"
                        class="w-full py-2.5 bg-accent hover:bg-yellow-400 text-black font-display font-bold text-sm rounded-[10px] transition-colors">
                    Add Category
                </button>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
        <table class="w-full border-collapse">
            <thead>
                <tr class="border-b border-border">
                    <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Name</th>
                    <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Channels</th>
                    <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($categories as $cat)
                <tr class="border-b border-border last:border-0 hover:bg-white/[.02] transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if($cat->color)
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $cat->color }}"></span>
                            @endif
                            <span class="font-medium text-sm">{{ $cat->name }}</span>
                        </div>
                        @if($cat->description)
                            <p class="text-muted text-xs mt-0.5 ml-4">{{ $cat->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-accent2/15 text-accent2">{{ $cat->channels_count }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1.5">
                            <a href="{{ route('categories.show', $cat) }}"
                               class="px-3 py-1.5 bg-border hover:bg-[#2e3748] text-gray-200 text-xs font-medium rounded-lg transition-colors">View</a>
                            <a href="{{ route('categories.edit', $cat) }}"
                               class="px-3 py-1.5 bg-border hover:bg-[#2e3748] text-gray-200 text-xs font-medium rounded-lg transition-colors">Edit</a>
                            <form action="{{ route('categories.destroy', $cat) }}" method="POST"
                                  onsubmit="return confirm('Delete category?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-xs font-medium rounded-lg transition-colors">Del</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-4 py-10 text-center text-muted text-sm">No categories yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $categories->links() }}</div>
@endsection
