<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Category;
use App\Models\Country;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\ChannelResource;
use App\Services\ViewingTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    // ─── GET /api/channels ────────────────────────────────────────────────────
    // Query params:
    //   search      string         partial name match
    //   category    int|int[]      filter by category id(s)
    //   tag         int|int[]      filter by tag id(s)
    //   country     int            filter by country id
    //   language    string         filter by language
    //   quality     string         4K|1080p|720p|480p|360p
    //   featured    bool           1 = featured only
    //   sort        string         views|name|created_at  (default: views)
    //   order       string         desc|asc               (default: desc)
    //   per_page    int            1-100                  (default: 24)
    public function index(Request $request): PaginatedResource
    {
        $request->validate([
            'search'   => 'nullable|string|max:255',
            'category' => 'nullable',          // int or array
            'tag'      => 'nullable',
            'country'  => 'nullable|integer',
            'language' => 'nullable|string|max:100',
            'quality'  => 'nullable|in:4K,1080p,720p,480p,360p',
            'featured' => 'nullable|boolean',
            'sort'     => 'nullable|in:views,name,created_at',
            'order'    => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Channel::with(['country:id,name,flag', 'categories:id,name,color', 'tags:id,name'])
            ->withCount('sources');

        // ── Filters ──────────────────────────────────────────────────────────
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $ids = (array) $request->category;
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids));
        }

        if ($request->filled('tag')) {
            $ids = (array) $request->tag;
            $query->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $ids));
        }

        if ($request->filled('country')) {
            $query->where('country_id', $request->country);
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('quality')) {
            $query->where('quality', $request->quality);
        }

        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        // ── Sorting ───────────────────────────────────────────────────────────
        $sort  = $request->input('sort', 'views');
        $order = $request->input('order', 'desc');
        $query->orderBy($sort, $order);

        $channels = $query->paginate($request->integer('per_page', 24))->withQueryString();

        return new PaginatedResource($channels, ChannelResource::class);
    }

    // ─── GET /api/channels/{channel} ─────────────────────────────────────────
    public function show(Channel $channel): JsonResponse
    {
        $channel->load(['country', 'categories', 'tags', 'sources']);
        $channel->incrementViews();

        return response()->json($channel);
    }

    // ─── GET /api/channels/featured ──────────────────────────────────────────
    public function featured(): JsonResponse
    {
        $channels = Channel::with(['country:id,name,flag', 'categories:id,name,color', 'tags:id,name'])
            ->where('featured', true)
            ->orderByDesc('views')
            ->limit(20)
            ->get();

        return response()->json($channels);
    }

    // ─── GET /api/channels/trending ──────────────────────────────────────────
    // Top channels by views (last 30 days by created_at for now — swap for
    // a dedicated views_log table if you add time-windowed tracking later)
    public function trending(): JsonResponse
    {
        $channels = Channel::with(['country:id,name,flag', 'categories:id,name,color', 'tags:id,name'])
            ->orderByDesc('views')
            ->limit(20)
            ->get();

        return response()->json($channels);
    }

    // ─── GET /api/channels/watching-now ──────────────────────────────────────
    // Live viewers from Redis via ViewingTracker
    public function watchingNow(ViewingTracker $tracker): JsonResponse
    {
        $watching = $tracker->getWatchingNow();

        $channels = Channel::with(['country:id,name,flag'])
            ->whereIn('id', collect($watching)->pluck('channel_id'))
            ->get()
            ->map(function ($channel) use ($watching) {
                $info = collect($watching)->firstWhere('channel_id', (string) $channel->id);
                $channel->active_viewers = $info['viewers'] ?? 0;
                return $channel;
            })
            ->sortByDesc('active_viewers')
            ->values();

        return response()->json($channels);
    }

    // ─── POST /api/channels/{channel}/track ──────────────────────────────────
    // Called by the frontend player to register a viewer heartbeat
    public function track(Channel $channel, ViewingTracker $tracker): JsonResponse
    {
        $tracker->trackChannel($channel->id);

        return response()->json(['viewers' => $tracker->getCurrentViewers($channel->id)]);
    }

    // ─── GET /api/channels/filters/meta ──────────────────────────────────────
    // Returns all available filter options so the frontend can build
    // dropdowns without separate requests
    public function filtersMeta(): JsonResponse
    {
        return response()->json([
            'languages' => Channel::select('language')
                ->whereNotNull('language')
                ->distinct()
                ->orderBy('language')
                ->pluck('language'),
            'qualities' => ['4K', '1080p', '720p', '480p', '360p'],
        ]);
    }
}
