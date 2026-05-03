@extends('layouts.app')
@section('title', 'Edit Source')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
    <div>
        <h1 class="font-display font-bold text-2xl">Edit Source</h1>
        <p class="text-muted text-sm mt-0.5">Channel: <span class="text-accent">{{ $channel->name }}</span></p>
    </div>
    <a href="{{ route('channels.show', $channel) }}"
       class="px-4 py-2 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">← Back</a>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    {{-- ── Form ── --}}
    <div class="flex flex-col gap-4">
        <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
            <div class="px-5 py-4 border-b border-border font-semibold text-sm">Source Details</div>
            <div class="p-5">
                <form action="{{ route('sources.update', $source) }}" method="POST"
                      id="sourceForm" class="space-y-4">
                    @csrf @method('PUT')

                    <div>
                        <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Type *</label>
                        <select name="type" id="sourceType" required
                                class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 focus:outline-none focus:border-accent transition-colors cursor-pointer"
                                onchange="onTypeChange()">
                            <option value="hls"  {{ old('type', $source->type) == 'hls'  ? 'selected' : '' }}>HLS (.m3u8)</option>
                            <option value="dash" {{ old('type', $source->type) == 'dash' ? 'selected' : '' }}>DASH (.mpd)</option>
                            <option value="mp4"  {{ old('type', $source->type) == 'mp4'  ? 'selected' : '' }}>MP4</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[0.72rem] font-medium uppercase tracking-wider text-muted mb-1.5">Stream URL</label>
                        <input name="link" id="sourceLink" value="{{ old('link', $source->link) }}"
                               placeholder="https://example.com/stream.m3u8"
                               class="w-full px-3.5 py-2.5 bg-bg border border-border rounded-[10px] text-sm text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors">
                    </div>

                    {{-- DRM toggle --}}
                    <div>
                        <label class="flex items-center gap-2.5 cursor-pointer text-sm select-none">
                            <input type="checkbox" name="drm" id="drmToggle" value="1"
                                   {{ old('drm', $source->drm) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded accent-accent bg-bg border-border"
                                   onchange="onDrmChange()">
                            DRM Protected
                        </label>
                    </div>

                    {{-- Clearkeys — shown only when DRM is on --}}
                    <div id="clearkeysSection" class="{{ old('drm', $source->drm) ? '' : 'hidden' }} space-y-3">
                        <div class="p-3 rounded-lg bg-bg border border-border/60">
                            <p class="text-[0.68rem] uppercase tracking-wider text-muted mb-2">
                                Clearkey Pairs
                                <span class="normal-case tracking-normal font-normal">(key-id:key, one per line)</span>
                            </p>
                            <textarea name="clearkeys" id="clearkeysInput" rows="4"
                                      placeholder="abc123:def456&#10;ghi789:jkl012"
                                      class="w-full px-3 py-2 bg-surface border border-border rounded-lg text-xs font-mono text-gray-200 placeholder-muted focus:outline-none focus:border-accent transition-colors resize-none">{{ old('clearkeys', $source->clearkeys) }}</textarea>
                            <p class="text-[0.65rem] text-muted mt-1.5">Each line: <code class="text-accent">keyId:key</code> (hex strings)</p>
                        </div>
                    </div>

                    <div class="flex gap-2.5 pt-2">
                        <button type="submit"
                                class="px-5 py-2.5 bg-accent hover:bg-yellow-400 text-black font-display font-bold text-sm rounded-[10px] transition-colors">
                            Save Changes
                        </button>
                        <a href="{{ route('channels.show', $channel) }}"
                           class="px-5 py-2.5 bg-border hover:bg-[#2e3748] text-gray-200 text-sm font-medium rounded-[10px] transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Danger zone --}}
        <div class="bg-surface border border-red-500/20 rounded-[10px] overflow-hidden">
            <div class="px-5 py-4 border-b border-red-500/20">
                <p class="font-semibold text-sm text-red-400">Danger Zone</p>
            </div>
            <div class="p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium">Delete this source</p>
                    <p class="text-xs text-muted mt-0.5">This action cannot be undone.</p>
                </div>
                <form action="{{ route('sources.destroy', $source) }}" method="POST"
                      onsubmit="return confirm('Delete this source permanently?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-sm font-medium rounded-[10px] transition-colors">
                        Delete Source
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Shaka Player ── --}}
    <div class="flex flex-col gap-4">
        <div class="bg-surface border border-border rounded-[10px] overflow-hidden">
            <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                <span class="font-semibold text-sm">Stream Preview</span>
                <div class="flex items-center gap-2">
                    <div id="playerStatus" class="flex items-center gap-1.5 text-xs text-muted">
                        <span class="w-2 h-2 rounded-full bg-border"></span>
                        <span>Idle</span>
                    </div>
                    <button type="button" onclick="testSource()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-accent hover:bg-yellow-400 text-black text-xs font-semibold rounded-lg transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <polygon points="5 3 19 12 5 21 5 3" fill="currentColor" stroke="none"/>
                        </svg>
                        Test Source
                    </button>
                </div>
            </div>

            {{-- Video container --}}
            <div class="relative bg-black" style="aspect-ratio:16/9">
                <video id="shakaVideo" class="w-full h-full" controls playsinline></video>
                <div id="playerOverlay"
                     class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-bg/90 pointer-events-none">
                    <svg class="w-12 h-12 text-border" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <polygon points="10 8 16 12 10 16 10 8" fill="currentColor" stroke="none"/>
                    </svg>
                    <p class="text-muted text-sm">Click <strong class="text-gray-200">Test Source</strong> to preview</p>
                </div>
            </div>

            {{-- Log panel --}}
            <div class="border-t border-border">
                <div class="px-4 py-2 flex items-center justify-between">
                    <span class="text-[0.68rem] uppercase tracking-wider text-muted">Player Log</span>
                    <button onclick="clearLog()" class="text-[0.68rem] text-muted hover:text-gray-200 transition-colors">Clear</button>
                </div>
                <div id="playerLog"
                     class="px-4 pb-3 space-y-1 max-h-36 overflow-y-auto font-mono text-xs"
                     style="scrollbar-width:thin">
                    <p class="text-muted">Ready.</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/shaka-player/4.10.6/controls.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/shaka-player/4.10.6/shaka-player.compiled.js"></script>

<script>
let shakaPlayer = null;

async function initShaka() {
    shaka.polyfill.installAll();
    if (!shaka.Player.isBrowserSupported()) {
        log('⚠ Browser does not support Shaka Player', 'warn');
        return;
    }
    shakaPlayer = new shaka.Player();
    await shakaPlayer.attach(document.getElementById('shakaVideo'));

    shakaPlayer.addEventListener('error', e => {
        const err = e.detail;
        log(`✕ Shaka error [${err.code}]: ${err.message ?? 'Unknown'}`, 'error');
        setStatus('error', 'Error');
    });
    shakaPlayer.addEventListener('buffering', e => {
        if (e.buffering) setStatus('buffering', 'Buffering…');
    });

    const v = document.getElementById('shakaVideo');
    v.addEventListener('playing', () => setStatus('playing', 'Playing'));
    v.addEventListener('pause',   () => setStatus('idle',    'Paused'));
    v.addEventListener('stalled', () => setStatus('buffering', 'Stalled…'));
}

async function testSource() {
    const url   = document.getElementById('sourceLink').value.trim();
    const type  = document.getElementById('sourceType').value;
    const drmOn = document.getElementById('drmToggle').checked;
    const ckRaw = document.getElementById('clearkeysInput')?.value.trim() ?? '';

    if (!url) { log('⚠ Please enter a stream URL first', 'warn'); return; }

    clearLog();
    log(`▶ Testing ${type.toUpperCase()} stream…`);
    setStatus('buffering', 'Loading…');
    document.getElementById('playerOverlay').style.display = 'none';

    if (!shakaPlayer) await initShaka();
    if (!shakaPlayer) return;

    try { await shakaPlayer.unload(); } catch (_) {}

    const config = buildConfig(type, drmOn, ckRaw);
    shakaPlayer.configure(config);
    log(`⚙ Config: type=${type}, drm=${drmOn}`);
    if (drmOn && ckRaw) log(`⚙ Clearkeys loaded: ${ckRaw.split('\n').filter(Boolean).length} pair(s)`);

    try {
        const mimeMap = { hls: 'application/x-mpegURL', dash: 'application/dash+xml', mp4: 'video/mp4' };
        await shakaPlayer.load(url, null, mimeMap[type]);
        log('✓ Stream loaded successfully', 'success');
        document.getElementById('shakaVideo').play().catch(() => {});
    } catch (err) {
        log(`✕ Load failed [${err.code ?? '?'}]: ${err.message ?? err}`, 'error');
        setStatus('error', 'Failed');
    }
}

function buildConfig(type, drmOn, ckRaw) {
    const config = { streaming: { bufferingGoal: 30, rebufferingGoal: 2 } };

    if (type === 'hls') {
        config.manifest = { hls: { ignoreTextStreamFailures: true } };
    }

    if (drmOn && ckRaw) {
        const keys = {};
        ckRaw.split('\n').forEach(line => {
            const [kid, key] = line.trim().split(':');
            if (kid && key) keys[kid.trim()] = key.trim();
        });
        if (Object.keys(keys).length > 0) {
            config.drm = {
                clearKeys: {
                    // Convert hex to base64url as required by ClearKey spec
                    ...Object.fromEntries(Object.entries(keys).map(([kid, key]) => [hexToBase64url(kid), hexToBase64url(key)]))
                }
            };
        }
    }

    return config;
}

function hexToBase64url(hex) {
    const bytes = new Uint8Array(hex.match(/.{1,2}/g).map(b => parseInt(b, 16)));
    const b64   = btoa(String.fromCharCode(...bytes));
    return b64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

function onDrmChange() {
    document.getElementById('clearkeysSection').classList.toggle('hidden', !document.getElementById('drmToggle').checked);
}

function onTypeChange() {
    const hints = { hls: 'https://…/stream.m3u8', dash: 'https://…/stream.mpd', mp4: 'https://…/video.mp4' };
    document.getElementById('sourceLink').placeholder = hints[document.getElementById('sourceType').value];
}

function setStatus(state, label) {
    const dot   = { playing: 'bg-green-400', buffering: 'bg-yellow-400 animate-pulse', error: 'bg-red-400', idle: 'bg-border' };
    const color = { playing: 'text-green-400', buffering: 'text-yellow-400', error: 'text-red-400', idle: 'text-muted' };
    document.getElementById('playerStatus').innerHTML =
        `<span class="w-2 h-2 rounded-full ${dot[state] ?? dot.idle}"></span><span class="${color[state] ?? color.idle}">${label}</span>`;
}

function log(msg, level = 'info') {
    const colors = { info: 'text-gray-400', success: 'text-green-400', warn: 'text-yellow-400', error: 'text-red-400' };
    const el  = document.getElementById('playerLog');
    const row = document.createElement('p');
    row.className   = colors[level] ?? colors.info;
    row.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
    el.appendChild(row);
    el.scrollTop = el.scrollHeight;
}

function clearLog() {
    document.getElementById('playerLog').innerHTML = '<p class="text-muted">Log cleared.</p>';
}

document.addEventListener('DOMContentLoaded', initShaka);
</script>
@endpush