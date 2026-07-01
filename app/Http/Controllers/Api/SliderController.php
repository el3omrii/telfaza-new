<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SlideGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Channel;

class SliderController extends Controller
{
    public function index(Request $request, SlideGeneratorService $slideService): JsonResponse
    {
        // Option A: Get slide for a specific channel
        if ($request->has('channel_id')) {
            // Eager load category here too just in case
            $channel = Channel::with('category')->find($request->input('channel_id'));
            
            if ($channel) {
                $slide = $slideService->getSlideForChannel($channel);
                
                return response()->json([
                    'success' => true, 
                    // Return as an array so the frontend always expects an array of slides
                    'data' => $slide ? [$slide] : [] 
                ]);
            }
        }

        // Option B: Default behavior -> Get 1 slide per channel for up to X channels
        $limit = (int) $request->input('limit', 10);
        $slides = $slideService->getCombinedSlides($limit);

        return response()->json([
            'success' => true,
            'data'    => $slides
        ]);
    }
}