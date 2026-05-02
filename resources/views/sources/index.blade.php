@extends('layouts.app')
@section('title', 'Sources')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Sources</div>
        <div class="page-subtitle">{{ $sources->total() }} stream sources across all channels</div>
    </div>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Channel</th>
                <th>Type</th>
                <th>Stream URL</th>
                <th>DRM</th>
                <th>Clearkeys</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($sources as $source)
            <tr>
                <td>
                    <a href="{{ route('channels.show', $source->channel) }}"
                       style="color:var(--accent);text-decoration:none;font-weight:500">
                        {{ $source->channel->name }}
                    </a>
                </td>
                <td><span class="badge badge-amber">{{ strtoupper($source->type) }}</span></td>
                <td style="max-width:320px">
                    @if($source->link)
                        <code style="font-size:.75rem;color:var(--muted);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            {{ $source->link }}
                        </code>
                    @else
                        <span style="color:var(--muted)">—</span>
                    @endif
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
                    <a href="{{ route('channels.sources.create', $source->channel) }}"
                       class="btn btn-secondary btn-sm" title="Add source to this channel">+ Source</a>
                    <a href="{{ route('sources.edit', $source) }}" class="btn btn-secondary btn-sm">Edit</a>
                    <form action="{{ route('sources.destroy', $source) }}" method="POST"
                          style="display:inline" onsubmit="return confirm('Delete this source?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center;color:var(--muted);padding:40px">
                    No sources yet. Add one from a channel page.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">{{ $sources->links() }}</div>
@endsection