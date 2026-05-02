@extends('layouts.app')
@section('title', 'Edit ' . $category->name)

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Edit Category</div>
        <div class="page-subtitle">{{ $category->name }}</div>
    </div>
    <a href="{{ route('categories.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:520px">
    <div class="card-header"><strong>Edit Details</strong></div>
    <div class="card-body">
        <form action="{{ route('categories.update', $category) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <input name="description" class="form-control" value="{{ old('description', $category->description) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Color</label>
                <div style="display:flex;gap:10px;align-items:center">
                    <input type="color" name="color" value="{{ old('color', $category->color ?? '#3b82f6') }}" style="width:44px;height:38px;border-radius:8px;border:1px solid var(--border);background:var(--bg);cursor:pointer;padding:2px">
                    <input name="color_hex" class="form-control" value="{{ old('color', $category->color) }}" placeholder="#3b82f6" style="font-family:monospace" maxlength="7">
                </div>
            </div>
            <div style="display:flex;gap:10px">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

        <hr style="border:none;border-top:1px solid var(--border);margin:24px 0">

        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete Category</button>
        </form>
    </div>
</div>
@endsection