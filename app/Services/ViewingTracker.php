<?php
// app/Services/ViewingTracker.php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use App\Models\Channel;

class ViewingTracker
{
    private $expirySeconds = 60*60*2; // Consider user "watching" for 2 hours without activity

    private function viewersKey($channelId): string
    {
        return "channel:{$channelId}:viewers";
    }

    private function viewerStartedAtKey($channelId): string
    {
        return "channel:{$channelId}:viewer_started_at";
    }

    public function trackChannel($channelId, string $viewerToken)
    {
        $key = $this->viewersKey($channelId);
        $startedAtKey = $this->viewerStartedAtKey($channelId);
        $now = time();

        if (!Redis::hexists($startedAtKey, $viewerToken)) {
            Redis::hset($startedAtKey, $viewerToken, $now);
        }

        // Add this session to the channel's viewer set
        Redis::zadd($key, $now, $viewerToken);

        // Set expiry for the channel key (cleanup old data)
        Redis::expire($key, $this->expirySeconds);
        Redis::expire($startedAtKey, $this->expirySeconds);

        // Track user's current channel
        Redis::setex("user:{$viewerToken}:current_channel", $this->expirySeconds, $channelId);
        // Increment channel views count
        $channel = Channel::find($channelId);
            if($channel)
                $channel->incrementViews();

        return true;
    }

    public function getViewerStartTime($channelId, string $viewerToken): ?int
    {
        $startedAtKey = $this->viewerStartedAtKey($channelId);
        $startedAt = Redis::hget($startedAtKey, $viewerToken);

        if ($startedAt !== null) {
            return (int) $startedAt;
        }

        $score = Redis::zscore($this->viewersKey($channelId), $viewerToken);

        return $score !== null ? (int) $score : null;
    }

    public function getCurrentViewers($channelId)
    {
        $key = $this->viewersKey($channelId);
        $now = time();
        $cutoff = $now - $this->expirySeconds;

        // Remove stale entries
        Redis::zremrangebyscore($key, '-inf', $cutoff);

        // Get active viewers
        return Redis::zcard($key);
    }

    public function getWatchingNow()
    {
        // Get all channel keys
        $keys = Redis::keys('channel:*:viewers');
        $channels = [];

        foreach ($keys as $key) {
            preg_match('/channel:(\d+):viewers/', $key, $matches);
            if (isset($matches[1])) {
                $channelId = (int) $matches[1];
                $viewerCount = $this->getCurrentViewers($channelId);
                if ($viewerCount > 0) {
                    $startedAt = Redis::hvals($this->viewerStartedAtKey($channelId));
                    $channels[] = [
                        'channel_id' => $channelId,
                        'viewers' => $viewerCount,
                        'started_at' => $startedAt ? (int) min($startedAt) : null,
                    ];
                }
            }
        }

        // Sort by viewer count descending
        usort($channels, function($a, $b) {
            return $b['viewers'] <=> $a['viewers'];
        });

        return $channels;
    }

    public function stopTracking($channelId = null, string $viewerToken = null)
    {
        if (!$viewerToken) return;

	    if ($channelId) {
	        Redis::zrem($this->viewersKey($channelId), $viewerToken);
            Redis::hdel($this->viewerStartedAtKey($channelId), $viewerToken);
	    }
	
	    Redis::del("user:{$viewerToken}:current_channel");
	}
}
