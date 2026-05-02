@extends('layouts.app')
@section('title', 'Countries')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Countries</div>
        <div class="page-subtitle">{{ $countries->total() }} countries</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:320px 1fr;gap:24px">
    <div class="card" style="align-self:start">
        <div class="card-header"><strong>Add Country</strong></div>
        <div class="card-body">
            <form action="{{ route('countries.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. France">
                </div>
                <div class="form-group">
                    <label class="form-label">Flag Emoji</label>
                    <input name="flag" class="form-control" value="{{ old('flag') }}" placeholder="🇫🇷" maxlength="10">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Add Country</button>
            </form>
        </div>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr><th>Flag</th><th>Name</th><th>Channels</th><th>Actions</th></tr>
            </thead>
            <tbody>
            @forelse($countries as $country)
                <tr>
                    <td style="font-size:1.5rem">{{ $country->flag }}</td>
                    <td><strong>{{ $country->name }}</strong></td>
                    <td><span class="badge badge-blue">{{ $country->channels_count }}</span></td>
                    <td>
                        <button onclick="openEdit({{ $country->country_id }}, '{{ addslashes($country->name) }}', '{{ $country->flag }}')"
                            class="btn btn-secondary btn-sm">Edit</button>
                        <form action="{{ route('countries.destroy', $country) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete country?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:30px">No countries yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination">{{ $countries->links() }}</div>

<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:100;align-items:center;justify-content:center">
    <div class="card" style="width:360px">
        <div class="card-header"><strong>Edit Country</strong></div>
        <div class="card-body">
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input id="editName" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Flag Emoji</label>
                    <input id="editFlag" name="flag" class="form-control" maxlength="10">
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
function openEdit(id, name, flag) {
    document.getElementById('editForm').action = '/countries/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editFlag').value = flag;
    document.getElementById('editModal').style.display = 'flex';
}
function closeEdit() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
@endpush
@endsection