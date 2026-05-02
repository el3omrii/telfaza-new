@extends('layouts.app')
@section('title', 'New Channel')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">New Channel</div>
        <div class="page-subtitle">Add a new broadcast channel</div>
    </div>
    <a href="{{ route('channels.index') }}" class="btn btn-secondary">← Back</a>
</div>

<form action="{{ route('channels.store') }}" method="POST" id="channelForm" enctype="multipart/form-data">
    @csrf
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">

        {{-- ── Left column ── --}}
        <div style="display:flex;flex-direction:column;gap:16px">

            {{-- Channel info --}}
            <div class="card">
                <div class="card-header"><strong>Channel Info</strong></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Name *</label>
                        <input name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. BBC World News">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the channel…">{{ old('description') }}</textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                        <div class="form-group">
                            <label class="form-label">Logo <span style="color:var(--muted);font-weight:400;text-transform:none;letter-spacing:0">(jpeg, png, webp, svg — max 2 MB)</span></label>
                            <label class="file-upload-btn" for="logoInput">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span id="logoLabel">Choose file…</span>
                            </label>
                            <input type="file" id="logoInput" name="logo" accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                   style="display:none" onchange="previewFile(this,'logoPreview','logoLabel')">
                            <div id="logoPreview" class="img-preview" style="display:none"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Channel Image <span style="color:var(--muted);font-weight:400;text-transform:none;letter-spacing:0">(jpeg, png, webp — max 4 MB)</span></label>
                            <label class="file-upload-btn" for="imageInput">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span id="imageLabel">Choose file…</span>
                            </label>
                            <input type="file" id="imageInput" name="image" accept="image/jpeg,image/png,image/webp"
                                   style="display:none" onchange="previewFile(this,'imagePreview','imageLabel')">
                            <div id="imagePreview" class="img-preview" style="display:none"></div>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Country</label>
                        <select name="country_id" class="form-control">
                            <option value="">— No country —</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ old('country_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->flag }} {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ── Inline Sources ── --}}
            <div class="card">
                <div class="card-header">
                    <strong>Sources</strong>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addSourceRow()">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                        Add Source
                    </button>
                </div>
                <div id="sourceHeader" style="display:none;padding:6px 16px;border-bottom:1px solid var(--border)">
                    <div class="source-row-label">
                        <span>Type</span><span>Stream URL</span><span style="text-align:center">DRM</span><span>Clearkeys</span><span></span>
                    </div>
                </div>
                <div id="sourcesContainer"></div>
                <div id="sourcesEmpty" style="padding:24px;text-align:center;color:var(--muted);font-size:.85rem">
                    No sources yet — click <strong>Add Source</strong> to stream this channel.
                </div>
            </div>

        </div>

        {{-- ── Right column ── --}}
        <div style="display:flex;flex-direction:column;gap:16px">

            {{-- Categories --}}
            <div class="card">
                <div class="card-header"><strong>Categories</strong></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:8px;max-height:200px;overflow-y:auto">
                    @foreach($categories as $cat)
                        <label style="display:flex;align-items:center;gap:9px;cursor:pointer;font-size:.875rem">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                {{ in_array($cat->id, old('categories', [])) ? 'checked' : '' }}
                                style="accent-color:var(--accent);width:15px;height:15px">
                            @if($cat->color)
                                <span class="color-dot" style="background:{{ $cat->color }}"></span>
                            @endif
                            {{ $cat->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Tags --}}
            <div class="card">
                <div class="card-header"><strong>Tags</strong></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:12px">

                    {{-- Existing tags as toggles --}}
                    <div id="tagCloud" style="display:flex;flex-wrap:wrap;gap:6px;max-height:160px;overflow-y:auto;min-height:32px">
                        @foreach($tags as $tag)
                            <label style="cursor:pointer">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                    {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                                    style="display:none" class="tag-cb">
                                <span class="badge badge-gray tag-label">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                        @if($tags->isEmpty())
                            <span id="noTagsHint" style="color:var(--muted);font-size:.8rem">No tags yet — create one below.</span>
                        @endif
                    </div>

                    {{-- Quick-create new tag --}}
                    <div style="border-top:1px solid var(--border);padding-top:12px">
                        <div class="form-label" style="margin-bottom:6px">Create new tag</div>
                        <div style="display:flex;gap:8px">
                            <input id="newTagInput" class="form-control" placeholder="Tag name…" style="flex:1"
                                   onkeydown="if(event.key==='Enter'){event.preventDefault();createTag()}">
                            <button type="button" class="btn btn-secondary" onclick="createTag()" style="white-space:nowrap">+ Add</button>
                        </div>
                        <div id="tagFeedback" style="font-size:.75rem;margin-top:5px;min-height:16px"></div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
                Create Channel
            </button>
        </div>
    </div>
</form>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .tag-cb:checked + .tag-label { background:rgba(232,160,32,.2); color:var(--accent); border:1px solid var(--accent); }
    .tag-label { border:1px solid transparent; transition:all .15s; }

    /* File upload button */
    .file-upload-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 9px 14px; border-radius: var(--radius);
        background: var(--bg); border: 1px dashed var(--border);
        color: var(--muted); font-size: .85rem; cursor: pointer;
        transition: all .15s; width: 100%;
    }
    .file-upload-btn:hover { border-color: var(--accent); color: var(--accent); }

    /* Thumbnail preview */
    .img-preview { margin-top: 8px; position: relative; display: inline-block; }
    .img-preview img { max-height: 72px; max-width: 100%; border-radius: 6px; border: 1px solid var(--border); display: block; }
    .img-preview .clear-btn {
        position: absolute; top: -6px; right: -6px;
        background: var(--danger); color: #fff; border: none;
        border-radius: 50%; width: 18px; height: 18px;
        font-size: .7rem; cursor: pointer; line-height: 18px; text-align: center;
    }

    .source-row-label {
        display: grid;
        grid-template-columns: 90px 1fr 60px 110px 34px;
        gap: 8px;
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--muted);
    }
    .source-row {
        display: grid;
        grid-template-columns: 90px 1fr 60px 110px 34px;
        gap: 8px;
        align-items: center;
        padding: 10px 16px;
        border-bottom: 1px solid var(--border);
    }
    .source-row:last-child { border-bottom: none; }
    .source-row .form-control { margin-bottom: 0; }
    .drm-check { display:flex; justify-content:center; align-items:center; }
    .remove-src { background:none; border:none; cursor:pointer; color:var(--muted); padding:4px; transition:color .15s; }
    .remove-src:hover { color:var(--danger); }
</style>

<script>
    // ── File preview ──────────────────────────────────────────────────────────
    function previewFile(input, previewId, labelId) {
        const preview = document.getElementById(previewId);
        const label   = document.getElementById(labelId);
        const file    = input.files[0];

        if (!file) {
            preview.style.display = 'none';
            label.textContent = 'Choose file…';
            return;
        }

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
        const input = document.getElementById(inputId);
        input.value = '';
        document.getElementById(previewId).style.display = 'none';
        document.getElementById(labelId).textContent = 'Choose file…';
    }
    let sourceCount = 0;

    function addSourceRow() {
        const container = document.getElementById('sourcesContainer');
        const empty     = document.getElementById('sourcesEmpty');
        const header    = document.getElementById('sourceHeader');

        empty.style.display  = 'none';
        header.style.display = 'block';

        const i   = sourceCount++;
        const row = document.createElement('div');
        row.className = 'source-row';
        row.id = `src-${i}`;
        row.innerHTML = `
            <select name="sources[${i}][type]" class="form-control">
                <option value="hls">HLS</option>
                <option value="dash">DASH</option>
                <option value="mp4">MP4</option>
            </select>
            <input name="sources[${i}][link]" class="form-control" placeholder="https://…/stream.m3u8">
            <div class="drm-check">
                <input type="checkbox" name="sources[${i}][drm]" value="1"
                    style="accent-color:var(--accent);width:16px;height:16px" title="DRM protected">
            </div>
            <input name="sources[${i}][clearkeys]" type="number" class="form-control" placeholder="Key ID">
            <button type="button" class="remove-src" onclick="removeSource(${i})" title="Remove">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        `;
        container.appendChild(row);
    }

    function removeSource(i) {
        const row = document.getElementById(`src-${i}`);
        if (row) row.remove();
        const container = document.getElementById('sourcesContainer');
        if (!container.querySelector('.source-row')) {
            document.getElementById('sourcesEmpty').style.display = '';
            document.getElementById('sourceHeader').style.display = 'none';
            sourceCount = 0;
        }
    }

    async function createTag() {
        const input    = document.getElementById('newTagInput');
        const feedback = document.getElementById('tagFeedback');
        const name     = input.value.trim();
        if (!name) return;

        feedback.style.color = 'var(--muted)';
        feedback.textContent = 'Creating…';

        try {
            const res = await fetch('{{ route('tags.quick-create') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ name }),
            });

            if (!res.ok) throw new Error('Server error');
            const tag = await res.json();

            // Remove "no tags" hint if present
            const hint = document.getElementById('noTagsHint');
            if (hint) hint.remove();

            const existing = document.querySelector(`input.tag-cb[value="${tag.tag_id}"]`);
            if (existing) {
                existing.checked = true;
                feedback.style.color = 'var(--accent)';
                feedback.textContent = `"${tag.name}" already exists — selected ✓`;
            } else {
                const cloud = document.getElementById('tagCloud');
                const label = document.createElement('label');
                label.style.cursor = 'pointer';
                label.innerHTML = `
                    <input type="checkbox" name="tags[]" value="${tag.tag_id}" checked
                        style="display:none" class="tag-cb">
                    <span class="badge badge-gray tag-label" style="background:rgba(232,160,32,.2);color:var(--accent);border:1px solid var(--accent)">${tag.name}</span>
                `;
                cloud.appendChild(label);
                feedback.style.color = 'var(--success)';
                feedback.textContent = `Tag "${tag.name}" created and selected ✓`;
            }

            input.value = '';
        } catch (e) {
            feedback.style.color = 'var(--danger)';
            feedback.textContent = 'Failed to create tag. Please try again.';
        }

        setTimeout(() => { feedback.textContent = ''; }, 3500);
    }
</script>
@endpush
@endsection