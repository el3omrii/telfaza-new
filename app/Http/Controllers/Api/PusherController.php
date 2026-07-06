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

        // 1. Format the headers exactly how the Pusher SDK expects them
        $webhook_headers = [
            'X-Pusher-Key' => $request->header('X-Pusher-Key'),
            'X-Pusher-Signature' => $request->header('X-Pusher-Signature'),
            'X-Pusher-Webhook-Id' => $request->header('X-Pusher-Webhook-Id'),
        ];

        // 2. Pass the formatted headers and the raw body
        $webhook = $pusher->webhook($webhook_headers, $request->getContent());

        // 3. Verify it actually came from Pusher
        if (!$webhook->isValid()) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // 4. Process the events
        $events = $webhook->getEvents();

        foreach ($events as $event) {
            // Channel name looks like "presence-stream-123"
            $channelId = str_replace('presence-stream-', '', $event['channel']);

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
