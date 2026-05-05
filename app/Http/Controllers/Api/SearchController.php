<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Channel;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // ─── GET /api/search?q=... ───────────────────────────────────────────────
    // Returns channels, categories, and tags that match the query.
    // Useful for a global search bar or autocomplete on the frontend.
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'q'        => 'required|string|min:1|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $q       = $request->input('q');
        $perPage = $request->integer('per_page', 10);

        $channels = Channel::with(['country:id,name,flag', 'categories:id,name,color'])
            ->where('name', 'like', "%{$q}%")
            ->orderByDesc('views')
            ->limit($perPage)
            ->get();

        $categories = Category::where('name', 'like', "%{$q}%")
            ->withCount('channels')
            ->orderBy('name')
            ->limit(10)
            ->get();

        $tags = Tag::where('name', 'like', "%{$q}%")
            ->withCount('channels')
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json([
            'query'      => $q,
            'channels'   => $channels,
            'categories' => $categories,
            'tags'       => $tags,
        ]);
    }
}
