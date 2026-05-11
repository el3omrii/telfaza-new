<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ChannelResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // ─── GET /api/categories ─────────────────────────────────────────────────
    public function index(): JsonResponse
    {
        $categories = Category::withCount('channels')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    // ─── GET /api/categories/{category} ──────────────────────────────────────
    public function show(Category $category): JsonResponse
    {
        $category->loadCount('channels');

        return response()->json($category);
    }

    // ─── GET /api/categories/{category}/channels ─────────────────────────────
    // Query params: sort, order, per_page (same contract as /api/channels)
    public function channels(Request $request, Category $category): PaginatedResource
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

        $query = $category->channels()
            ->with(['country:id,name,flag', 'tags:id,name'])
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
        $channels = new PaginatedResource($channels, ChannelResource::class);
        return $channels;
        return response()->json([
            "category" => $category,
            "channels" => new PaginatedResource($channels, ChannelResource::class)
        ]);
    }
}
