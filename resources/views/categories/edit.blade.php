@extends('layouts.app')
@section('title', 'Edit ' . $category->name)

@section('content')
<div class="flex items-center justify-between mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Edit Category</h1>
        <p class="text-muted text-sm mt-0.5">{{ $category->name }}</p>
    </div>
    <a href="{{ route('categories.index') }}"
       class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">← Back</a>
</div>

<div class="bg-surface border border-border rounded-[10px] overflow-hidden max-w-lg">
    <div class="px-5 py-4 border-b border-border font-semibold text-sm">Edit Details</div>
    <div class="p-5">
        <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Name *</label>
                <input name="name" value="{{ old('name', $category->name) }}" required
                       class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors">
            </div>
            <div>
                <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Description</label>
                <input name="description" value="{{ old('description', $category->description) }}"
                       class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors">
            </div>
            <div>
                <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Color</label>
                <div class="flex gap-2.5 items-center">
                    <input type="color" name="color" value="{{ old('color', $category->color ?? '#3b82f6') }}"
                           class="w-11 h-10 rounded-lg border border-border bg-bg cursor-pointer p-1">
                    <input name="color_hex" value="{{ old('color', $category->color) }}" placeholder="#3b82f6" maxlength="7"
                           class="flex-1 px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm font-mono text-gray-200 focus:outline-none focus:border-accent transition-colors">
                </div>
            </div>
            <div class="flex gap-2.5 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-accent hover:bg-yellow-400 text-black font-display font-bold text-sm rounded-[10px] transition-colors">Save Changes</button>
                <a href="{{ route('categories.index') }}"
                   class="px-5 py-2.5 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">Cancel</a>
            </div>
        </form>

        <hr class="border-border my-6">

        <form action="{{ route('categories.destroy', $category) }}" method="POST"
              onsubmit="return confirm('Delete this category?')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="px-5 py-2.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-sm font-medium rounded-[10px] transition-colors">
                Delete Category
            </button>
        </form>
    </div>
</div>
@endsection
