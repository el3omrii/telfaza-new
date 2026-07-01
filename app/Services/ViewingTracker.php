<?php
// app/Services/ViewingTracker.php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class ViewingTracker
{
    private $expirySeconds = 60; // Consider user "watching" for 60 seconds without activity
    
    public function trackChannel($channelId, string $viewerToken)
    {
        $key = "channel:{$channelId}:viewers";
        
        // Add this session to the channel's viewer set
        Redis::zadd($key, time(), $viewerToken);
        
        // Set expiry for the channel key (cleanup old data)
        Redis::expire($key, $this->expirySeconds);
        
        // Track user's current channel
        Redis::setex("user:{$viewerToken}:current_channel", $this->expirySeconds, $channelId);
        
        return true;
    }
    
    public function getCurrentViewers($channelId)
    {
        $key = "channel:{$channelId}:viewers";
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
                $viewerCount = $this->getCurrentViewers($matches[1]);
                if ($viewerCount > 0) {
                    $channels[] = [
                        'channel_id' => $matches[1],
                        'viewers' => $viewerCount
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
	        Redis::zrem("channel:{$channelId}:viewers", $viewerToken);
	    }
	
	    Redis::del("user:{$viewerToken}:current_channel");
	}
}