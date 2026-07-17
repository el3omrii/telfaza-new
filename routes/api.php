<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes here are public (no auth middleware).
| Prefix: /api  (set in bootstrap/app.php or RouteServiceProvider)
|
*/

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\PusherController;
use App\Http\Controllers\Api\ScraperController;
use Illuminate\Support\Facades\Route;


Route::domain(env('APP_API_DOMAIN'))->group(function () {
// ─── Global search ────────────────────────────────────────────────────────────
// GET /api/search?q=bbc
Route::get('/search', SearchController::class);

// ─── Slides ────────────────────────────────────────────────────────────────

// GET  /api/slides
Route::get('/slides', [SliderController::class, 'index']);

// ─── Channels ────────────────────────────────────────────────────────────────

// GET  /api/channels
//      ?search=bbc
//      &category=1          (or category[]=1&category[]=2 for multiple)
//      &tag=5               (or tag[]=5&tag[]=6)
//      &country=12
//      &language=English
//      &quality=1080p
//      &featured=1
//      &sort=views|name|created_at   (default: views)
//      &order=desc|asc               (default: desc)
//      &per_page=24                  (default: 24, max: 100)
Route::get('/channels', [ChannelController::class, 'index']);

// Named sub-collection endpoints (must be declared before {channel} param)
Route::get('/channels/featured',      [ChannelController::class, 'featured']);
Route::get('/channels/trending',      [ChannelController::class, 'trending']);
Route::get('/channels/watching-now',  [ChannelController::class, 'watchingNow']);
Route::get('/channels/filters/meta',  [ChannelController::class, 'filtersMeta']);
Route::get('/channels/favorites',     [ChannelController::class, 'favorites']);

// GET  /api/channels/{slug}         — full channel detail (increments views)
Route::get('/channels/{channel:slug}',    [ChannelController::class, 'show']);

// POST /api/channels/{id}/track   — heartbeat for live viewer count (Redis)
Route::post('/channels/{channel}/track', [ChannelController::class, 'track']);
// POST /api/channels/{id}/report  — report a channel issue
Route::post('/video-reports', [ReportController::class, 'store']);
// ─── Categories ───────────────────────────────────────────────────────────────

// GET  /api/categories            — list all with channel count
Route::get('/categories', [CategoryController::class, 'index']);

// GET  /api/categories/{id}       — single category metadata
Route::get('/categories/{category:slug}', [CategoryController::class, 'show']);

// GET  /api/categories/{id}/channels
//      Same filter/sort/per_page params as /api/channels (country, quality, search)
Route::get('/categories/{category:slug}/channels', [CategoryController::class, 'channels']);

// ─── Tags ─────────────────────────────────────────────────────────────────────

// GET  /api/tags                  — list all with channel count
Route::get('/tags', [TagController::class, 'index']);

// GET  /api/tags/{id}             — single tag metadata
Route::get('/tags/{tag:slug}', [TagController::class, 'show']);

// GET  /api/tags/{id}/channels
//      Same filter/sort/per_page params as /api/channels (country, quality, search)
Route::get('/tags/{tag:slug}/channels', [TagController::class, 'channels']);

// ─── Countries ───────────────────────────────────────────────────────────────

// GET  /api/countries             — list all with channel count
Route::get('/countries', [CountryController::class, 'index']);

// GET  /api/countries/{id}/channels
//      ?category=1  &quality=1080p  &sort=views  &order=desc  &per_page=24
Route::get('/countries/{country:slug}/channels', [CountryController::class, 'channels']);
Route::post('/pusher/webhook', [PusherController::class, 'webhook']);
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
Route::get('/scraper/glwiz/{channelName}', [ScraperController::class, 'getGlwizStreamUrl']);
});
