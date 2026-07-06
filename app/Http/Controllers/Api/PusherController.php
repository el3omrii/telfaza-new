<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\ViewingTracker;
use Pusher\Pusher;

use Illuminate\Http\Request;

class PusherController extends Controller
{
    public function webhook (Request $request,  ViewingTracker $tracker) {
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            ['cluster' => env('PUSHER_APP_CLUSTER')]
        );

        // 1. Get the raw body and headers to verify the webhook signature
        $webhook = $pusher->webhook($request->headers->all(), $request->getContent());

        // 2. Verify it actually came from Pusher
        if (!$webhook->isValid()) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // 3. Process the events
        $events = $webhook->getEvents();

        foreach ($events as $event) {
            // Channel name looks like "presence-stream-123"
            $channelId = str_replace('presence-', '', $event['channel']);

            if ($event['name'] === 'member_added') {
                $tracker->trackChannel($channelId, $event['user_id']);
            } 
            elseif ($event['name'] === 'member_removed') {
                $tracker->stopTracking($channelId, $event['user_id']);
            }
        }

        return response()->json(['success' => true]);
    }
}
