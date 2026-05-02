@extends('layouts.app')
@section('title', $category->name)

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">
            @if($category->color)
                <span class="color-dot" style="background:{{ $category->color }};width:14px;height:14px"></span>
            @endif
            {{ $category->name }}
        </div>
        <div class="page-subtitle">{{ $category->description }}</div>
    </div>
    <div style="display:flex;gap:10px">
        <a href="{{ route('categories.edit', $category) }}" class="btn btn-secondary">Edit</a>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <strong>Channels in this category</strong>
        <span class="badge badge-blue">{{ $channels->total() }}</span>
    </div>
    <table>
        <thead>
            <tr><th>Channel</th><th>Country</th><th>Views</th><th></th></tr>
        </thead>
        <tbody>
        @forelse($channels as $channel)
            <tr>
                <td><strong>{{ $channel->name }}</strong></td>
                <td>{{ $channel->country?->flag }} {{ $channel->country?->name ?? '—' }}</td>
                <td>{{ number_format($channel->views) }}</td>
                <td><a href="{{ route('channels.show', $channel) }}" class="btn btn-secondary btn-sm">View</a></td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:30px">No channels in this category.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">{{ $channels->links() }}</div>
@endsection