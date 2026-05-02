@extends('layouts.app')
@section('title', 'Categories')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Categories</div>
        <div class="page-subtitle">{{ $categories->total() }} categories</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    {{-- Create form --}}
    <div class="card" style="align-self:start">
        <div class="card-header"><strong>Add Category</strong></div>
        <div class="card-body">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. News">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input name="description" class="form-control" value="{{ old('description') }}" placeholder="Optional description">
                </div>
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <div style="display:flex;gap:10px;align-items:center">
                        <input type="color" name="color" value="{{ old('color', '#3b82f6') }}" style="width:44px;height:38px;border-radius:8px;border:1px solid var(--border);background:var(--bg);cursor:pointer;padding:2px">
                        <input name="color_hex" class="form-control" value="{{ old('color', '#3b82f6') }}" placeholder="#3b82f6" style="font-family:monospace" maxlength="7">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Add Category</button>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="card">
        <div class="card-header"><strong>All Categories</strong></div>
        <table>
            <thead>
                <tr><th>Name</th><th>Channels</th><th>Actions</th></tr>
            </thead>
            <tbody>
            @forelse($categories as $cat)
                <tr>
                    <td>
                        @if($cat->color)
                            <span class="color-dot" style="background:{{ $cat->color }}"></span>
                        @endif
                        <strong>{{ $cat->name }}</strong>
                        @if($cat->description)
                            <div style="font-size:.75rem;color:var(--muted)">{{ $cat->description }}</div>
                        @endif
                    </td>
                    <td><span class="badge badge-blue">{{ $cat->channels_count }}</span></td>
                    <td>
                        <a href="{{ route('categories.show', $cat) }}" class="btn btn-secondary btn-sm">View</a>
                        <a href="{{ route('categories.edit', $cat) }}" class="btn btn-secondary btn-sm">Edit</a>
                        <form action="{{ route('categories.destroy', $cat) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete category?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Del</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:30px">No categories yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination">{{ $categories->links() }}</div>
@endsection