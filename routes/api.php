<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\ViewingTracker;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/watching-now', function (ViewingTracker $tracker) {
    $watchingNow = $tracker->getWatchingNow();
    
    // Fetch channel details from database
    $channels = \App\Models\Channel::whereIn('id', collect($watchingNow)->pluck('channel_id'))
        ->get()
        ->map(function ($channel) use ($watchingNow) {
            $viewerInfo = collect($watchingNow)->firstWhere('channel_id', $channel->id);
            $channel->active_viewers = $viewerInfo['viewers'];
            return $channel;
        })
        ->sortByDesc('active_viewers')
        ->values();
    
    return response()->json($channels);
});