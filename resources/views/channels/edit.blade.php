@extends('layouts.app')
@section('title', 'Edit ' . $channel->name)

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Edit Channel</h1>
        <p class="text-muted text-sm mt-0.5">{{ $channel->name }}</p>
    </div>
    <a href="{{ route('channels.show', $channel) }}"
       class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">← Back</a>
</div>

<form action="{{ route('channels.update', $channel) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left: 2 columns --}}
        <div class="lg:col-span-2 bg-surface border border-border rounded-[10px] overflow-hidden">
            <div class="px-5 py-4 border-b border-border font-semibold text-sm">Channel Info</div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Name *</label>
                    <input name="name" value="{{ old('name', $channel->name) }}" required
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors">
                </div>
                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Description</label>
                    <div class="relative">
                        <textarea name="description" id="descriptionField" rows="4"
                                  class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors resize-none">{{ old('description', $channel->description) }}</textarea>
                        <button type="button"
                                id="aiBtn"
                                onclick="generateDescription()"
                                title="Generate description with AI"
                                class="absolute bottom-2.5 right-2.5 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all
                                       bg-[#1a1f2e] hover:bg-[#1e4d8c] border border-accent2/30 hover:border-accent2 text-accent2 hover:text-white shadow-lg">
                            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
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
                    {{-- Logo --}}
                    <div>
                        <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">
                            Logo <span class="normal-case tracking-normal font-normal text-muted/70">(jpeg, png, webp, svg — max 2 MB)</span>
                        </label>
                        @if($channel->logo)
                            <img src="{{ Storage::disk('uploads')->url($channel->logo) }}" alt="Current logo" class="max-h-16 rounded-lg border border-border mb-2">
                            <label class="flex items-center gap-2 text-xs text-red-400 mb-2 cursor-pointer">
                                <input type="checkbox" name="remove_logo" value="1" class="w-4 h-4 accent-red-500 bg-bg border-border">
                                Remove current logo
                            </label>
                        @endif
                        <label for="logoInput"
                               class="flex items-center gap-2 w-full px-3.5 py-2.5 bg-bg border border-dashed border-border hover:border-accent text-muted hover:text-accent text-sm rounded-[10px] cursor-pointer transition-colors">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <span id="logoLabel">{{ $channel->logo ? 'Replace file…' : 'Choose file…' }}</span>
                        </label>
                        <input type="file" id="logoInput" name="logo" accept="image/jpeg,image/png,image/webp,image/svg+xml"
                               class="hidden" onchange="previewFile(this,'logoPreview','logoLabel')">
                        <div id="logoPreview" class="hidden mt-2 relative inline-block"></div>
                    </div>

                    {{-- Image --}}
                    <div>
                        <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">
                            Channel Image <span class="normal-case tracking-normal font-normal text-muted/70">(jpeg, png, webp — max 4 MB)</span>
                        </label>
                        @if($channel->image)
                            <img src="{{ Storage::disk('uploads')->url($channel->image) }}" alt="Current image" class="max-h-16 rounded-lg border border-border mb-2">
                            <label class="flex items-center gap-2 text-xs text-red-400 mb-2 cursor-pointer">
                                <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 accent-red-500 bg-bg border-border">
                                Remove current image
                            </label>
                        @endif
                        <label for="imageInput"
                               class="flex items-center gap-2 w-full px-3.5 py-2.5 bg-bg border border-dashed border-border hover:border-accent text-muted hover:text-accent text-sm rounded-[10px] cursor-pointer transition-colors">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <span id="imageLabel">{{ $channel->image ? 'Replace file…' : 'Choose file…' }}</span>
                        </label>
                        <input type="file" id="imageInput" name="image" accept="image/jpeg,image/png,image/webp"
                               class="hidden" onchange="previewFile(this,'imagePreview','imageLabel')">
                        <div id="imagePreview" class="hidden mt-2 relative inline-block"></div>
                    </div>
                </div>
                <div>
                    <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Country</label>
                    <select name="country_id"
                            class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors cursor-pointer">
                        <option value="">— No country —</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}" {{ old('country_id', $channel->country_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->flag }} {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Right --}}
        <div class="flex flex-col gap-5">
             {{-- Language andEPG --}}
            <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
                <div class="px-5 py-4 border-b border-border font-semibold text-sm">Language and EPG</div>
                <div class="p-4">
                    <input name="language" value="{{ old('language', $channel->language) }}" placeholder="e.g. English, Arabic, French…"
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-4 pb-2 items-end">
                    <div>
                        <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">EPG ID</label>
                        <input name="epgid" value="{{ old('epgid', $channel->epgid) }}" placeholder="e.g. EPG ID"
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                    </div>
                    <div>
                        <label class="relative inline-flex cursor-pointer items-center gap-3 text-muted text-sm">
                            <input type="checkbox" name="featured" value="1" {{ old('featured', $channel->featured) ? 'checked' : '' }} class="peer sr-only" />
                            <div class="peer h-7 w-12 rounded-full bg-slate-300 ring-offset-1 transition-colors duration-200 peer-checked:bg-accent peer-focus:ring-2 peer-focus:ring-yellow-400"></div>
                            <span class="dot absolute top-1 left-1 h-5 w-5 rounded-full bg-white transition-transform duration-200 ease-in-out peer-checked:translate-x-5"></span>
                            Featured ?
                        </label>           
                    </div>
                </div>
            </div>
            {{-- Language --}}
            <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
                <div class="px-5 py-4 border-b border-border font-semibold text-sm">Language</div>
                <div class="p-4">
                    <input name="language" value="{{ old('language', $channel->language) }}" placeholder="e.g. English, Arabic, French…"
                           class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                </div>
            </div>

            {{-- Quality --}}
            <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
                <div class="px-5 py-4 border-b border-border font-semibold text-sm">Quality</div>
                <div class="p-4 flex flex-wrap gap-2">
                    @foreach(['4K','1080p','720p','480p','360p'] as $q)
                        <label class="cursor-pointer">
                            <input type="radio" name="quality" value="{{ $q }}"
                                   {{ old('quality', $channel->quality) === $q ? 'checked' : '' }}
                                   class="hidden quality-rb">
                            <span class="quality-label px-3 py-1.5 rounded-lg text-xs font-semibold bg-border text-muted border border-transparent transition-all cursor-pointer">{{ $q }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Categories --}}
            <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
                <div class="px-5 py-4 border-b border-border font-semibold text-sm">Categories</div>
                <div class="p-4 flex flex-col gap-2 max-h-52 overflow-y-auto">
                    @foreach($categories as $cat)
                        <label class="flex items-center gap-2.5 cursor-pointer text-sm">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                   {{ $channel->categories->contains('id', $cat->id) ? 'checked' : '' }}
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
                <div class="p-4 flex flex-wrap gap-1.5 max-h-48 overflow-y-auto">
                    @foreach($tags as $tag)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                   {{ $channel->tags->contains('id', $tag->id) ? 'checked' : '' }}
                                   class="hidden tag-cb">
                            <span class="tag-label px-2.5 py-1 rounded-full text-[0.72rem] font-semibold bg-border text-muted border border-transparent transition-all cursor-pointer">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-accent hover:bg-yellow-400 text-black font-display font-bold text-sm rounded-[10px] transition-colors">
                Save Changes
            </button>

        </div>
    </div>
</form>
<hr class="border-border my-6">
<form action="{{ route('channels.destroy', $channel) }}" method="POST"
      onsubmit="return confirm('Permanently delete this channel?')">
    @csrf @method('DELETE')
    <button type="submit"
            class="w-full py-3 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 font-medium text-sm rounded-[10px] transition-colors">
        Delete Channel
    </button>
</form>

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .tag-cb:checked + .tag-label { background: rgba(232,160,32,.2); color: #e8a020; border-color: #e8a020; }
    .quality-rb:checked + .quality-label { background: rgba(232,160,32,.2); color: #e8a020; border-color: #e8a020; }
    .img-preview-wrap { position:relative; display:inline-block; }
    .img-preview-wrap img { max-height:72px; max-width:100%; border-radius:6px; border:1px solid #252b38; display:block; }
    .img-clear { position:absolute; top:-6px; right:-6px; background:#ef4444; color:#fff; border:none; border-radius:50%; width:18px; height:18px; font-size:.65rem; cursor:pointer; line-height:18px; text-align:center; }
</style>
<script>
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

    const btn      = document.getElementById('aiBtn');
    const spinner  = document.getElementById('aiSpinner');
    const btnText  = document.getElementById('aiBtnText');
    const errEl    = document.getElementById('aiError');
    const textarea = document.getElementById('descriptionField');

    btn.disabled = true;
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
        if (!res.ok || data.error) throw new Error(data.error ?? 'Unknown error');

        textarea.value = '';
        let i = 0;
        const text = data.description;
        const type = () => { if (i < text.length) { textarea.value += text[i++]; setTimeout(type, 12); } };
        type();

    } catch (err) {
        errEl.textContent = err.message;
        errEl.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
        btnText.textContent = 'Generate with AI';
    }
}

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
    document.getElementById(labelId).textContent = 'Replace file…';
}
</script>
@endpush
@endsection