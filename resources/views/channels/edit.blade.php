@extends('layouts.app')
@section('title', 'Edit ' . $channel->name)

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Edit Channel</div>
        <div class="page-subtitle">{{ $channel->name }}</div>
    </div>
    <a href="{{ route('channels.show', $channel) }}" class="btn btn-secondary">← Back</a>
</div>

<form id="edit-form" action="{{ route('channels.update', $channel) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
        <div class="card">
            <div class="card-header"><strong>Channel Info</strong></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input name="name" class="form-control" value="{{ old('name', $channel->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $channel->description) }}</textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                        <div class="form-group">
                            <label class="form-label">Logo <span style="color:var(--muted);font-weight:400;text-transform:none;letter-spacing:0">(jpeg, png, webp, svg — max 2 MB)</span></label>
                            @if($channel->logo)
                                <div class="img-preview" style="display:inline-block;margin-bottom:8px">
                                    <img src="{{ Storage::disk('uploads')->url($channel->logo) }}" alt="Current logo">
                                </div>
                                <label style="display:flex;align-items:center;gap:8px;font-size:.8rem;margin-bottom:8px;cursor:pointer">
                                    <input type="checkbox" name="remove_logo" value="1"
                                           style="accent-color:var(--danger);width:14px;height:14px">
                                    <span style="color:var(--danger)">Remove current logo</span>
                                </label>
                            @endif
                            <label class="file-upload-btn" for="logoInput">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span id="logoLabel">{{ $channel->logo ? 'Replace file…' : 'Choose file…' }}</span>
                            </label>
                            <input type="file" id="logoInput" name="logo" accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                   style="display:none" onchange="previewFile(this,'logoPreview','logoLabel')">
                            <div id="logoPreview" class="img-preview" style="display:none"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Channel Image <span style="color:var(--muted);font-weight:400;text-transform:none;letter-spacing:0">(jpeg, png, webp — max 4 MB)</span></label>
                            @if($channel->image)
                                <div class="img-preview" style="display:inline-block;margin-bottom:8px">
                                    <img src="{{ Storage::disk('uploads')->url($channel->image) }}" alt="Current image">
                                </div>
                                <label style="display:flex;align-items:center;gap:8px;font-size:.8rem;margin-bottom:8px;cursor:pointer">
                                    <input type="checkbox" name="remove_image" value="1"
                                           style="accent-color:var(--danger);width:14px;height:14px">
                                    <span style="color:var(--danger)">Remove current image</span>
                                </label>
                            @endif
                            <label class="file-upload-btn" for="imageInput">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span id="imageLabel">{{ $channel->image ? 'Replace file…' : 'Choose file…' }}</span>
                            </label>
                            <input type="file" id="imageInput" name="image" accept="image/jpeg,image/png,image/webp"
                                   style="display:none" onchange="previewFile(this,'imagePreview','imageLabel')">
                            <div id="imagePreview" class="img-preview" style="display:none"></div>
                        </div>
                    </div>
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <select name="country_id" class="form-control">
                        <option value="">— No country —</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}"
                                {{ old('country_id', $channel->id) == $c->id ? 'selected' : '' }}>
                                {{ $c->flag }} {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="card">
                <div class="card-header"><strong>Categories</strong></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:8px;max-height:220px;overflow-y:auto">
                    @foreach($categories as $cat)
                        <label style="display:flex;align-items:center;gap:9px;cursor:pointer;font-size:.875rem">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                {{ $channel->categories->contains('category_id', $cat->id) ? 'checked' : '' }}
                                style="accent-color:var(--accent);width:15px;height:15px">
                            @if($cat->color)
                                <span class="color-dot" style="background:{{ $cat->color }}"></span>
                            @endif
                            {{ $cat->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="card-header"><strong>Tags</strong></div>
                <div class="card-body" style="display:flex;flex-wrap:wrap;gap:8px;max-height:200px;overflow-y:auto">
                    @foreach($tags as $tag)
                        <label style="cursor:pointer">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                {{ $channel->tags->contains('tag_id', $tag->id) ? 'checked' : '' }}
                                style="display:none" class="tag-cb">
                            <span class="badge badge-gray tag-label">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</form>

<div style="display:flex;gap:16px;justify-content:flex-end;margin-top:20px">
    <form action="{{ route('channels.destroy', $channel) }}" method="POST" onsubmit="return confirm('Permanently delete this channel?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger">
            Delete Channel
        </button>
    </form>
    <button type="submit" form="edit-form" class="btn btn-primary">
        Save Changes
    </button>
</div>

@push('styles')
<style>
    .tag-cb:checked + .tag-label { background: rgba(232,160,32,.2); color: var(--accent); border: 1px solid var(--accent); }
    .tag-label { border: 1px solid transparent; transition: all .15s; }

    .file-upload-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 9px 14px; border-radius: var(--radius);
        background: var(--bg); border: 1px dashed var(--border);
        color: var(--muted); font-size: .85rem; cursor: pointer;
        transition: all .15s; width: 100%;
    }
    .file-upload-btn:hover { border-color: var(--accent); color: var(--accent); }

    .img-preview { position: relative; display: inline-block; }
    .img-preview img { max-height: 72px; max-width: 100%; border-radius: 6px; border: 1px solid var(--border); display: block; }
    .img-preview .clear-btn {
        position: absolute; top: -6px; right: -6px;
        background: var(--danger); color: #fff; border: none;
        border-radius: 50%; width: 18px; height: 18px;
        font-size: .7rem; cursor: pointer; line-height: 18px; text-align: center;
    }
</style>
<script>
    function previewFile(input, previewId, labelId) {
        const preview = document.getElementById(previewId);
        const label   = document.getElementById(labelId);
        const file    = input.files[0];
        if (!file) { preview.style.display = 'none'; label.textContent = 'Choose file…'; return; }
        label.textContent = file.name;
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `
                <img src="${e.target.result}" alt="preview">
                <button type="button" class="clear-btn" title="Remove"
                    onclick="clearFile('${input.id}','${previewId}','${labelId}')">✕</button>
            `;
            preview.style.display = 'inline-block';
        };
        reader.readAsDataURL(file);
    }
    function clearFile(inputId, previewId, labelId) {
        document.getElementById(inputId).value = '';
        document.getElementById(previewId).style.display = 'none';
        document.getElementById(labelId).textContent = 'Replace file…';
    }
</script>
@endpush
@endsection