<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\ChannelResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    // ─── GET /api/countries ──────────────────────────────────────────────────
    public function index(): JsonResponse
    {
        $countries = Country::withCount('channels')
            ->orderBy('name')
            ->get();

        return response()->json($countries);
    }

    // ─── GET /api/countries/{country}/channels ────────────────────────────────
    public function channels(Request $request, Country $country): JsonResponse
    {
        $request->validate([
            'sort'     => 'nullable|in:views,name,created_at',
            'order'    => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'category' => 'nullable|integer',
            'quality'  => 'nullable|in:4K,1080p,720p,480p,360p',
        ]);

        $sort  = $request->input('sort', 'views');
        $order = $request->input('order', 'desc');

        $query = $country->channels()
            ->with(['categories:id,name,color', 'tags:id,name'])
            ->withCount('sources');

        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $request->category));
        }

        if ($request->filled('quality')) {
            $query->where('quality', $request->quality);
        }

        $channels = $query->orderBy($sort, $order)
            ->paginate($request->integer('per_page', 24))
            ->withQueryString();

		  $channels = new PaginatedResource($channels, ChannelResource::class);
        // Resolve the full paginated structure (data + meta + links)
        $channelsData = $channels->toResponse(request())->getData(true);

        return response()->json([
            'country'  => $country,
            'channels' => $channelsData,
        ]);
    }
}
