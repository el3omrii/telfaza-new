@extends('layouts.app')
@section('title', $channel->name)

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ $channel->name }}</div>
        <div class="page-subtitle">{{ $channel->country?->flag }} {{ $channel->country?->name }}</div>
    </div>
    <div style="display:flex;gap:10px">
        <a href="{{ route('channels.edit', $channel) }}" class="btn btn-secondary">Edit</a>
        <a href="{{ route('channels.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Views</div>
        <div class="stat-value amber">{{ number_format($channel->views) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Sources</div>
        <div class="stat-value blue">{{ $channel->sources->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Categories</div>
        <div class="stat-value">{{ $channel->categories->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Tags</div>
        <div class="stat-value">{{ $channel->tags->count() }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px">
    <div class="card">
        <div class="card-header"><strong>Details</strong></div>
        <div class="card-body">
            @if($channel->description)
                <p style="color:var(--muted);line-height:1.7;margin-bottom:16px">{{ $channel->description }}</p>
            @endif
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <div class="form-label">Logo</div>
                    @if($channel->logo)
                        <img src="{{ Storage::disk('uploads')->url($channel->logo) }}" alt="Logo"
                             style="max-height:60px;max-width:100%;border-radius:6px;border:1px solid var(--border);margin-top:4px">
                    @else
                        <span style="color:var(--muted);font-size:.85rem">—</span>
                    @endif
                </div>
                <div>
                    <div class="form-label">Channel Image</div>
                    @if($channel->image)
                        <img src="{{ Storage::disk('uploads')->url($channel->image,) }}" alt="Channel image"
                             style="max-height:60px;max-width:100%;border-radius:6px;border:1px solid var(--border);margin-top:4px">
                    @else
                        <span style="color:var(--muted);font-size:.85rem">—</span>
                    @endif
                </div>
                <div><div class="form-label">Created</div>{{ $channel->created_at->format('d M Y') }}</div>
                <div><div class="form-label">Updated</div>{{ $channel->updated_at->format('d M Y') }}</div>
            </div>
        </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-header"><strong>Categories</strong></div>
            <div class="card-body" style="display:flex;flex-wrap:wrap;gap:6px">
                @forelse($channel->categories as $cat)
                    <span class="badge badge-blue" @if($cat->color) style="background:{{ $cat->color }}22;color:{{ $cat->color }}" @endif>{{ $cat->name }}</span>
                @empty
                    <span style="color:var(--muted);font-size:.8rem">None</span>
                @endforelse
            </div>
        </div>
        <div class="card">
            <div class="card-header"><strong>Tags</strong></div>
            <div class="card-body" style="display:flex;flex-wrap:wrap;gap:6px">
                @forelse($channel->tags as $tag)
                    <span class="badge badge-amber">{{ $tag->name }}</span>
                @empty
                    <span style="color:var(--muted);font-size:.8rem">None</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <strong>Sources</strong>
        <a href="{{ route('channels.sources.create', $channel) }}" class="btn btn-primary btn-sm">+ Add Source</a>
    </div>
    <table>
        <thead>
            <tr><th>Type</th><th>Link</th><th>DRM</th><th>Clearkeys</th><th>Actions</th></tr>
        </thead>
        <tbody>
        @forelse($channel->sources as $source)
            <tr>
                <td><span class="badge badge-amber">{{ strtoupper($source->type) }}</span></td>
                <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <code style="font-size:.78rem;color:var(--muted)">{{ $source->link ?? '—' }}</code>
                </td>
                <td>
                    @if($source->drm)
                        <span class="badge badge-green">Yes</span>
                    @else
                        <span class="badge badge-gray">No</span>
                    @endif
                </td>
                <td>{{ $source->clearkeys ?? '—' }}</td>
                <td>
                    <a href="{{ route('channels.sources.edit', [$channel, $source]) }}" class="btn btn-secondary btn-sm">Edit</a>
                    <form action="{{ route('channels.sources.destroy', [$channel, $source]) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete source?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px">No sources yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection