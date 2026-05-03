@extends('layouts.app')
@section('title', 'Countries')

@section('content')
<div class="flex items-center justify-between mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Countries</h1>
        <p class="text-muted text-sm mt-0.5">{{ $countries->total() }} countries</p>
    </div>
</div>

<div class="grid grid-cols-[320px_1fr] gap-6">
    <div class="bg-surface border border-border rounded-[10px] overflow-hidden self-start">
        <div class="px-5 py-4 border-b border-border font-semibold text-sm">Add Country</div>
        <div class="p-5">
            <form action="{{ route('countries.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Name *</label>
                    <input name="name" value="{{ old('name') }}" required placeholder="e.g. France"
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                </div>
                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Flag Emoji</label>
                    <input name="flag" value="{{ old('flag') }}" placeholder="🇫🇷" maxlength="10"
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                </div>
                <button type="submit"
                        class="w-full py-2.5 bg-accent hover:bg-yellow-400 text-black font-display font-bold text-sm rounded-[10px] transition-colors">
                    Add Country
                </button>
            </form>
        </div>
    </div>

    <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
        <table class="w-full border-collapse">
            <thead>
                <tr class="border-b border-border">
                    <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Flag</th>
                    <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Name</th>
                    <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Channels</th>
                    <th class="px-4 py-3 text-left text-[0.72rem] uppercase tracking-wider text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($countries as $country)
                <tr class="border-b border-border last:border-0 hover:bg-white/[.02] transition-colors">
                    <td class="px-4 py-3">
                        <img src="//flagcdn.com/{{ strtolower($country->flag) }}.svg" alt="{{ $country->name }}" class="w-8 h-8 rounded-full">
                    </td>
                    <td class="px-4 py-3 font-medium text-sm">{{ $country->name }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-[0.72rem] font-semibold bg-accent2/15 text-accent2">{{ $country->channels_count }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1.5">
                            <button onclick="openEdit({{ $country->id }}, '{{ addslashes($country->name) }}', '{{ $country->flag }}')"
                                    class="px-3 py-1.5 bg-border hover:bg-[#2e3748] text-gray-200 text-xs font-medium rounded-lg transition-colors">Edit</button>
                            <form action="{{ route('countries.destroy', $country) }}" method="POST"
                                  onsubmit="return confirm('Delete country?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-xs font-medium rounded-lg transition-colors">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-10 text-center text-muted text-sm">No countries yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $countries->links() }}</div>

{{-- Edit modal --}}
<div id="editModal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center">
    <div class="bg-surface border border-border rounded-[10px] w-80 overflow-hidden">
        <div class="px-5 py-4 border-b border-border font-semibold text-sm">Edit Country</div>
        <div class="p-5">
            <form id="editForm" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Name *</label>
                    <input id="editName" name="name" required
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors">
                </div>
                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Flag</label>
                    <input id="editFlag" name="flag" maxlength="10"
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
function openEdit(id, name, flag) {
    document.getElementById('editForm').action = '/countries/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editFlag').value = flag;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endpush
@endsection
