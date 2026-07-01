@extends('layouts.app')
@section('title', 'New Channel')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">New Channel</h1>
        <p class="text-muted text-sm mt-0.5">Add a new broadcast channel</p>
    </div>
    <a href="{{ route('channels.index') }}"
       class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">← Back</a>
</div>

<form action="{{ route('channels.store') }}" method="POST" id="channelForm" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left: 2 columns --}}
        <div class="lg:col-span-2 flex flex-col gap-5">

            {{-- Channel Info --}}
            <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
                <div class="px-5 py-4 border-b border-border font-semibold text-sm">Channel Info</div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Name *</label>
                        <input name="name" value="{{ old('name') }}" required placeholder="e.g. BBC World News"
                               class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                    </div>
                    <div>
                        <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Description</label>
                        <div class="relative">
                            <textarea name="description" id="descriptionField" rows="3"
                                      placeholder="Brief description…"
                                      class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors resize-none">{{ old('description') }}</textarea>
                            <button type="button"
                                    id="aiBtn"
                                    onclick="generateDescription()"
                                    title="Generate description with AI"
                                    class="absolute bottom-2.5 right-2.5 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all
                                           bg-[#1a1f2e] hover:bg-[#1e4d8c] border border-accent2/30 hover:border-accent2 text-accent2 hover:text-white shadow-lg">
                                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                                    <path d="M9 8h2v8H9zm4 0h2v8h-2z" fill="none"/>
                                    <path d="M12 2a10 10 0 100 20A10 10 0 0012 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                                </svg>
                                <svg id="aiSpinner" class="hidden w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span id="aiBtnText">Generate with AI</span>
                            </button>
                        </div>
                        <p id="aiError" class="hidden text-xs text-red-400 mt-1.5"></p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach([['logo','logoInput','logoPreview','logoLabel','image/jpeg,image/png,image/webp,image/svg+xml','Logo','jpeg, png, webp, svg — max 2 MB'],
                                   ['image','imageInput','imagePreview','imageLabel','image/jpeg,image/png,image/webp','Channel Image','jpeg, png, webp — max 4 MB']] as [$field,$inputId,$previewId,$labelId,$accept,$title,$hint])
                        <div>
                            <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">
                                {{ $title }} <span class="normal-case tracking-normal font-normal text-muted/70">({{ $hint }})</span>
                            </label>
                            <label for="{{ $inputId }}"
                                   class="flex items-center gap-2 w-full px-3.5 py-2.5 bg-bg border border-dashed border-border hover:border-accent text-muted hover:text-accent text-sm rounded-[10px] cursor-pointer transition-colors">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                                <span id="{{ $labelId }}">Choose file…</span>
                            </label>
                            <input type="file" id="{{ $inputId }}" name="{{ $field }}" accept="{{ $accept }}"
                                   class="hidden" onchange="previewFile(this,'{{ $previewId }}','{{ $labelId }}')">
                            <div id="{{ $previewId }}" class="hidden mt-2 relative inline-block"></div>
                        </div>
                        @endforeach
                    </div>
                    <div>
                        <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Country</label>
                        <select name="country_id"
                                class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors cursor-pointer">
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

            {{-- Inline Sources --}}
            <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
                <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                    <span class="font-semibold text-sm">Sources</span>
                    <button type="button" onclick="addSourceRow()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-accent hover:bg-yellow-400 text-black text-xs font-medium rounded-lg transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                        Add Source
                    </button>
                </div>
                <div id="sourceHeader" class="hidden grid grid-cols-[90px_1fr_60px_110px_34px] gap-2 px-4 py-2 border-b border-border text-[0.68rem] uppercase tracking-wider text-muted">
                    <span>Type</span><span>Stream URL</span><span class="text-center">DRM</span><span>Clearkeys</span><span></span>
                </div>
                <div id="sourcesContainer"></div>
                <div id="sourcesEmpty" class="px-4 py-6 text-center text-muted text-sm">
                    No sources yet — click <strong>Add Source</strong> to stream this channel.
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="flex flex-col gap-5">

            {{-- Language andEPG --}}
            <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
                <div class="px-5 py-4 border-b border-border font-semibold text-sm">Language and EPG</div>
                <div class="p-4">
                    <input name="language" value="{{ old('language') }}" placeholder="e.g. English, Arabic, French…"
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-4 pb-2 items-end">
                    <div>
                        <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">EPG ID</label>
                        <input name="epgid" value="{{ old('epgid') }}" placeholder="e.g. EPG ID"
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                    </div>
                    <div class="flex flex-col gap-3">
                        <label class="relative inline-flex cursor-pointer items-center gap-3 text-muted text-sm">
                            <input type="checkbox" name="featured" value="1" {{ old('featured') ? 'checked' : '' }} class="peer sr-only" />
                            <div class="peer h-7 w-12 rounded-full bg-slate-300 ring-offset-1 transition-colors duration-200 peer-checked:bg-accent peer-focus:ring-2 peer-focus:ring-yellow-400"></div>
                            <span class="dot absolute top-1 left-1 h-5 w-5 rounded-full bg-white transition-transform duration-200 ease-in-out peer-checked:translate-x-5"></span>
                            Featured ?
                        </label>
                        <label class="relative inline-flex cursor-pointer items-center gap-3 text-muted text-sm">
                            <input type="checkbox" name="published" value="1" {{ old('published') ? 'checked' : '' }} class="peer sr-only" />
                            <div class="peer h-7 w-12 rounded-full bg-slate-300 ring-offset-1 transition-colors duration-200 peer-checked:bg-accent peer-focus:ring-2 peer-focus:ring-yellow-400"></div>
                            <span class="dot absolute top-1 left-1 h-5 w-5 rounded-full bg-white transition-transform duration-200 ease-in-out peer-checked:translate-x-5"></span>
                            Published ?
                        </label>
                    </div>
                </div>
            </div>

            {{-- Quality --}}
            <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
                <div class="px-5 py-4 border-b border-border font-semibold text-sm">Quality</div>
                <div class="p-4 flex flex-wrap gap-2">
                    @foreach(['4K','1080p','720p','480p','360p'] as $q)
                        <label class="cursor-pointer">
                            <input type="radio" name="quality" value="{{ $q }}"
                                   {{ old('quality') === $q ? 'checked' : '' }}
                                   class="hidden quality-rb">
                            <span class="quality-label px-3 py-1.5 rounded-lg text-xs font-semibold bg-border text-muted border border-transparent transition-all cursor-pointer">{{ $q }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Categories --}}
            <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
                <div class="px-5 py-4 border-b border-border font-semibold text-sm">Categories</div>                <div class="p-4 flex flex-col gap-2 max-h-48 overflow-y-auto">
                    @foreach($categories as $cat)
                        <label class="flex items-center gap-2.5 cursor-pointer text-sm">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                   {{ in_array($cat->id, old('categories', [])) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded accent-accent bg-bg border-border">
                            @if($cat->color)
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $cat->color }}"></span>
                            @endif
                            {{ $cat->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Tags --}}
            <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
                <div class="px-5 py-4 border-b border-border font-semibold text-sm">Tags</div>
                <div class="p-4 flex flex-col gap-3">
                    <div id="tagCloud" class="flex flex-wrap gap-1.5 max-h-40 overflow-y-auto min-h-8">
                        @foreach($tags as $tag)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                       {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                                       class="hidden tag-cb">
                                <span class="tag-label px-2.5 py-1 rounded-full text-[0.72rem] font-semibold bg-border text-muted border border-transparent transition-all cursor-pointer">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                        @if($tags->isEmpty())
                            <span id="noTagsHint" class="text-muted text-xs">No tags yet — create one below.</span>
                        @endif
                    </div>
                    <div class="border-t border-border pt-3">
                        <p class="text-[0.72rem] uppercase tracking-wider text-muted mb-1.5">Create new tag</p>
                        <div class="flex gap-2">
                            <input id="newTagInput" placeholder="Tag name…"
                                   onkeydown="if(event.key==='Enter'){event.preventDefault();createTag()}"
                                   class="flex-1 px-3 py-2 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                            <button type="button" onclick="createTag()"
                                    class="px-3 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors whitespace-nowrap">+ Add</button>
                        </div>
                        <div id="tagFeedback" class="text-xs mt-1.5 min-h-4"></div>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-accent hover:bg-yellow-400 text-black font-display font-bold text-sm rounded-[10px] transition-colors">
                Create Channel
            </button>
        </div>
    </div>
</form>

@push('styles')
<style>
    .tag-cb:checked + .tag-label { background: rgba(232,160,32,.2); color: #e8a020; border-color: #e8a020; }
    .quality-rb:checked + .quality-label { background: rgba(232,160,32,.2); color: #e8a020; border-color: #e8a020; }
    .source-input { width:100%; padding:8px 10px; background:#0d0f14; border:1px solid #252b38; border-radius:8px; color:#e2e8f0; font-size:.8rem; }
    .source-input:focus { outline:none; border-color:#e8a020; }
    .source-row { display:grid; grid-template-columns:90px 1fr 60px 110px 34px; gap:8px; align-items:center; padding:10px 16px; border-bottom:1px solid #252b38; }
    .source-row:last-child { border-bottom:none; }
    .img-preview-wrap { position:relative; display:inline-block; margin-top:8px; }
    .img-preview-wrap img { max-height:72px; max-width:100%; border-radius:6px; border:1px solid #252b38; display:block; }
    .img-clear { position:absolute; top:-6px; right:-6px; background:#ef4444; color:#fff; border:none; border-radius:50%; width:18px; height:18px; font-size:.65rem; cursor:pointer; line-height:18px; text-align:center; }
</style>
<script>
function previewFile(input, previewId, labelId) {
    const preview = document.getElementById(previewId);
    const label   = document.getElementById(labelId);
    const file    = input.files[0];
    if (!file) { preview.classList.add('hidden'); label.textContent = 'Choose file…'; return; }
    label.textContent = file.name;
    const reader = new FileReader();
    reader.onload = e => {
        preview.innerHTML = `<div class="img-preview-wrap"><img src="${e.target.result}" alt="preview"><button type="button" class="img-clear" onclick="clearFile('${input.id}','${previewId}','${labelId}')">✕</button></div>`;
        preview.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}
function clearFile(inputId, previewId, labelId) {
    document.getElementById(inputId).value = '';
    document.getElementById(previewId).classList.add('hidden');
    document.getElementById(labelId).textContent = 'Choose file…';
}

let sourceCount = 0;
function addSourceRow() {
    document.getElementById('sourcesEmpty').classList.add('hidden');
    document.getElementById('sourceHeader').classList.remove('hidden');
    const i   = sourceCount++;
    const row = document.createElement('div');
    row.className = 'source-row';
    row.id = `src-${i}`;
    row.innerHTML = `
        <select name="sources[${i}][type]" class="source-input">
            <option value="hls">HLS</option>
            <option value="dash">DASH</option>
            <option value="mp4">MP4</option>
        </select>
        <input name="sources[${i}][link]" class="source-input" placeholder="https://…/stream.m3u8">
        <div class="flex justify-center">
            <input type="checkbox" name="sources[${i}][drm]" value="1"
                   class="w-4 h-4 accent-[#e8a020]" title="DRM protected">
        </div>
        <input name="sources[${i}][clearkeys]" type="number" class="source-input" placeholder="Key ID">
        <button type="button" onclick="removeSource(${i})"
                class="text-[#64748b] hover:text-red-400 p-1 transition-colors flex items-center justify-center">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>`;
    document.getElementById('sourcesContainer').appendChild(row);
}
function removeSource(i) {
    document.getElementById(`src-${i}`)?.remove();
    if (!document.querySelector('.source-row')) {
        document.getElementById('sourcesEmpty').classList.remove('hidden');
        document.getElementById('sourceHeader').classList.add('hidden');
        sourceCount = 0;
    }
}

async function generateDescription() {
    const name    = document.querySelector('input[name="name"]').value.trim();
    const lang    = document.querySelector('input[name="language"]')?.value.trim() ?? '';
    const country = document.querySelector('select[name="country_id"] option:checked')?.textContent.trim() ?? '';
    const cats    = [...document.querySelectorAll('input[name="categories[]"]:checked')]
                        .map(el => el.closest('label').textContent.trim()).join(', ');

    if (!name) {
        const err = document.getElementById('aiError');
        err.textContent = 'Please enter a channel name first.';
        err.classList.remove('hidden');
        setTimeout(() => err.classList.add('hidden'), 3000);
        return;
    }

    const btn     = document.getElementById('aiBtn');
    const spinner = document.getElementById('aiSpinner');
    const btnText = document.getElementById('aiBtnText');
    const errEl   = document.getElementById('aiError');
    const textarea = document.getElementById('descriptionField');

    btn.disabled    = true;
    spinner.classList.remove('hidden');
    btnText.textContent = 'Generating…';
    errEl.classList.add('hidden');

    try {
        const res = await fetch('{{ route('ai.generate-description') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ name, category: cats, language: lang, country }),
        });

        const data = await res.json();

        if (!res.ok || data.error) {
            throw new Error(data.error ?? 'Unknown error');
        }

        // Typewriter effect
        textarea.value = '';
        let i = 0;
        const text = data.description;
        const type = () => {
            if (i < text.length) {
                textarea.value += text[i++];
                setTimeout(type, 12);
            }
        };
        type();

    } catch (err) {
        errEl.textContent = err.message;
        errEl.classList.remove('hidden');
    } finally {
        btn.disabled    = false;
        spinner.classList.add('hidden');
        btnText.textContent = 'Generate with AI';
    }
}
async function createTag() {
    const input    = document.getElementById('newTagInput');
    const feedback = document.getElementById('tagFeedback');
    const name     = input.value.trim();
    if (!name) return;
    feedback.className = 'text-xs mt-1.5 min-h-4 text-muted';
    feedback.textContent = 'Creating…';
    try {
        const res = await fetch('{{ route('tags.quick-create') }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ name }),
        });
        if (!res.ok) throw new Error();
        const tag = await res.json();
        document.getElementById('noTagsHint')?.remove();
        const existing = document.querySelector(`.tag-cb[value="${tag.id}"]`);
        if (existing) {
            existing.checked = true;
            feedback.className = 'text-xs mt-1.5 min-h-4 text-accent';
            feedback.textContent = `"${tag.name}" already exists — selected ✓`;
        } else {
            const label = document.createElement('label');
            label.className = 'cursor-pointer';
            label.innerHTML = `<input type="checkbox" name="tags[]" value="${tag.id}" checked class="hidden tag-cb">
                <span class="tag-label px-2.5 py-1 rounded-full text-[0.72rem] font-semibold border border-transparent transition-all cursor-pointer" style="background:rgba(232,160,32,.2);color:#e8a020;border-color:#e8a020">${tag.name}</span>`;
            document.getElementById('tagCloud').appendChild(label);
            feedback.className = 'text-xs mt-1.5 min-h-4 text-green-400';
            feedback.textContent = `Tag "${tag.name}" created and selected ✓`;
        }
        input.value = '';
    } catch {
        feedback.className = 'text-xs mt-1.5 min-h-4 text-red-400';
        feedback.textContent = 'Failed to create tag. Please try again.';
    }
    setTimeout(() => { feedback.textContent = ''; }, 3500);
}
</script>
@endpush
@endsection