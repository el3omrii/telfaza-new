@extends('layouts.app')
@section('title', 'Tags')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Tags</h1>
        <p class="text-muted text-sm mt-0.5">{{ $tags->total() }} tags</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-6">
    {{-- Add form --}}
    <div class="bg-surface border border-border rounded-[10px] overflow-hidden self-start">
        <div class="px-5 py-4 border-b border-border font-semibold text-sm">Add Tag</div>
        <div class="p-5">
            <form action="{{ route('tags.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Tag Name *</label>
                    <input name="name" value="{{ old('name') }}" required placeholder="e.g. Sports"
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                </div>
                <button type="submit"
                        class="w-full py-2.5 bg-accent hover:bg-yellow-400 text-black font-display font-bold text-sm rounded-[10px] transition-colors">
                    Add Tag
                </button>
            </form>
        </div>
    </div>

    <div class="flex flex-col gap-5">
        {{-- Tag cloud --}}
        <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
            <div class="px-5 py-4 border-b border-border font-semibold text-sm">Tag Cloud</div>
            <div class="p-4 flex flex-wrap gap-2">
                @foreach($tags as $tag)
                    <span class="px-3 py-1 rounded-full font-semibold bg-accent/15 text-accent"
                          style="font-size: {{ min(1.05, 0.72 + ($tag->channels_count / 25)) }}rem">
                        {{ $tag->name }}
                        <span class="opacity-50 text-[0.7em] ml-1">{{ $tag->channels_count }}</span>
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-surface border border-border rounded-[10px] overflow-hidden"><div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-border">
                        <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Tag</th>
                        <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Channels</th>
                        <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($tags as $tag)
                    <tr class="border-b border-border last:border-0 hover:bg-white/[.02] transition-colors">
                        <td class="px-4 py-3 font-medium text-sm">{{ $tag->name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-accent/15 text-accent">{{ $tag->channels_count }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1.5">
                                <button onclick="openEdit({{ $tag->id }}, '{{ addslashes($tag->name) }}')"
                                        class="px-3 py-1.5 bg-border hover:bg-[#2e3748] text-gray-200 text-xs font-medium rounded-lg transition-colors">Edit</button>
                                <form action="{{ route('tags.destroy', $tag) }}" method="POST"
                                      onsubmit="return confirm('Delete tag?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-xs font-medium rounded-lg transition-colors">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-10 text-center text-muted text-sm">No tags yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</div></div>
<div class="mt-5">{{ $tags->links() }}</div>

{{-- Edit modal --}}
<div id="editModal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center">
    <div class="bg-surface border border-border rounded-[10px] w-80 overflow-hidden">
        <div class="px-5 py-4 border-b border-border font-semibold text-sm">Edit Tag</div>
        <div class="p-5">
            <form id="editForm" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Name *</label>
                    <input id="editName" name="name" required
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors">
                </div>
                <div class="flex gap-2.5">
                    <button type="submit"
                            class="px-5 py-2.5 bg-accent hover:bg-yellow-400 text-black font-display font-bold text-sm rounded-[10px] transition-colors">Save</button>
                    <button type="button" onclick="closeEdit()"
                            class="px-5 py-2.5 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<script>
function openEdit(id, name) {
    document.getElementById('editForm').action = '/tags/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endpush
@endsection