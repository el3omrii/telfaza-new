@extends('layouts.app')
@section('title', 'Tags')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Tags</div>
        <div class="page-subtitle">{{ $tags->total() }} tags</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:320px 1fr;gap:24px">
    {{-- Add form --}}
    <div class="card" style="align-self:start">
        <div class="card-header"><strong>Add Tag</strong></div>
        <div class="card-body">
            <form action="{{ route('tags.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Tag Name *</label>
                    <input name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Sports">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Add Tag</button>
            </form>
        </div>
    </div>

    {{-- Tag cloud + table --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-header"><strong>Tag Cloud</strong></div>
            <div class="card-body" style="display:flex;flex-wrap:wrap;gap:8px">
                @foreach($tags as $tag)
                    <span class="badge badge-amber" style="font-size:{{ min(1.1, 0.7 + ($tag->channels_count / 20)) }}rem;padding:5px 12px">
                        {{ $tag->name }}
                        <span style="opacity:.6;font-size:.7em;margin-left:4px">{{ $tag->channels_count }}</span>
                    </span>
                @endforeach
            </div>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr><th>Tag</th><th>Channels</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($tags as $tag)
                    <tr>
                        <td><strong>{{ $tag->name }}</strong></td>
                        <td><span class="badge badge-amber">{{ $tag->channels_count }}</span></td>
                        <td>
                            <button onclick="openEdit({{ $tag->tag_id }}, '{{ addslashes($tag->name) }}')" class="btn btn-secondary btn-sm">Edit</button>
                            <form action="{{ route('tags.destroy', $tag) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete tag?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:30px">No tags yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="pagination">{{ $tags->links() }}</div>

{{-- Inline edit modal --}}
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:100;align-items:center;justify-content:center">
    <div class="card" style="width:360px">
        <div class="card-header"><strong>Edit Tag</strong></div>
        <div class="card-body">
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input id="editName" name="name" class="form-control" required>
                </div>
                <div style="display:flex;gap:10px">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" onclick="closeEdit()" class="btn btn-secondary">Cancel</button>
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
    document.getElementById('editModal').style.display = 'flex';
}
function closeEdit() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
@endpush
@endsection