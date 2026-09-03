<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    // ─── GET /api/tags ───────────────────────────────────────────────────────
    public function index(): JsonResponse
    {
        $tags = Tag::withCount('channels')
            ->orderBy('name')
            ->get();

        return response()->json($tags);
    }

    // ─── GET /api/tags/{tag} ─────────────────────────────────────────────────
    public function show(Tag $tag): JsonResponse
    {
        $tag->loadCount('channels');

        return response()->json($tag);
    }

    // ─── GET /api/tags/{tag}/channels ────────────────────────────────────────
    // Query params: sort, order, per_page (same contract as /api/channels)
    public function channels(Request $request, Tag $tag): JsonResponse
    {
        $request->validate([
            'sort'     => 'nullable|in:views,name,created_at',
            'order'    => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'country'  => 'nullable|integer',
            'quality'  => 'nullable|in:4K,1080p,720p,480p,360p',
            'search'   => 'nullable|string|max:255',
        ]);

        $sort  = $request->input('sort', 'views');
        $order = $request->input('order', 'desc');

        $query = $tag->channels()
            ->where('published', true)
            ->with(['country:id,name,flag', 'categories:id,name,color'])
            ->withCount('sources');

        if ($request->filled('country')) {
            $query->where('country_id', $request->country);
        }

        if ($request->filled('quality')) {
            $query->where('quality', $request->quality);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $channels = $query->orderBy($sort, $order)
    ->paginate($request->integer('per_page', 24))
    ->withQueryString();

return response()->json([
    'tag'      => $tag,
    'channels' => [
        'data' =>$channels->items(), // Just the data array
        'meta'     => [
            'current_page' => $channels->currentPage(),
            'per_page'     => $channels->perPage(),
            'total'        => $channels->total(),
            'last_page'    => $channels->lastPage(),
            'from'         => $channels->firstItem(),
            'to'           => $channels->lastItem(),
        ],
        'links'    => [
            'first' => $channels->url(1),
            'last'  => $channels->url($channels->lastPage()),
            'prev'  => $channels->previousPageUrl(),
            'next'  => $channels->nextPageUrl(),
        ],
    ],
]);
    }
}
