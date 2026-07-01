<?php
// app/Http/Middleware/TrackChannelView.php

namespace App\Http\Middleware;

use App\Services\ViewingTracker;
use Closure;

class TrackChannelView
{
    protected $tracker;
    
    public function __construct(ViewingTracker $tracker)
    {
        $this->tracker = $tracker;
    }
    
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        // Track if we're on a channel viewing route
        /*if ($request->route('channel')) {
            $this->tracker->trackChannel($request->route('channel'));
        }*/
        
        return $response;
    }
}