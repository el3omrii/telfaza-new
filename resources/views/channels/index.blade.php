@extends('layouts.app')
@section('title', 'Channels')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Channels</div>
        <div class="page-subtitle">{{ $channels->total() }} channels total</div>
    </div>
    <a href="{{ route('channels.create') }}" class="btn btn-primary">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        New Channel
    </a>
</div>

<form method="GET" class="filters">
    <input name="search" class="form-control" placeholder="Search by name…" value="{{ request('search') }}">
    <select name="category" class="form-control">
        <option value="">All categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->category_id }}" {{ request('category') == $cat->category_id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <select name="country" class="form-control">
        <option value="">All countries</option>
        @foreach($countries as $c)
            <option value="{{ $c->country_id }}" {{ request('country') == $c->country_id ? 'selected' : '' }}>{{ $c->flag }} {{ $c->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary">Filter</button>
    <a href="{{ route('channels.index') }}" class="btn btn-secondary">Reset</a>
</form>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Channel</th>
                <th>Country</th>
                <th>Categories</th>
                <th>Sources</th>
                <th>Views</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($channels as $channel)
            <tr>
                <td>
                    <strong>{{ $channel->name }}</strong>
                    @if($channel->description)
                        <div style="color:var(--muted);font-size:.78rem;margin-top:2px">{{ Str::limit($channel->description, 60) }}</div>
                    @endif
                </td>
                <td>{{ $channel->country?->flag }} {{ $channel->country?->name ?? '—' }}</td>
                <td>
                    @foreach($channel->categories->take(3) as $cat)
                        <span class="badge badge-blue" style="margin-right:3px">{{ $cat->name }}</span>
                    @endforeach
                    @if($channel->categories->count() > 3)
                        <span class="badge badge-gray">+{{ $channel->categories->count() - 3 }}</span>
                    @endif
                </td>
                <td><span class="badge badge-amber">{{ $channel->sources_count }}</span></td>
                <td>{{ number_format($channel->views) }}</td>
                <td>
                    <a href="{{ route('channels.show', $channel) }}" class="btn btn-secondary btn-sm">View</a>
                    <a href="{{ route('channels.edit', $channel) }}" class="btn btn-secondary btn-sm">Edit</a>
                    <form action="{{ route('channels.destroy', $channel) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this channel?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:40px">No channels found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">{{ $channels->links() }}</div>
@endsection