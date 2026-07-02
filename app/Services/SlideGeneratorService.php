<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Channel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SlideGeneratorService
{
    /**
     * Generate a combined list of slides (one per channel).
     */
    public function getCombinedSlides(int $totalLimit = 10): array
    {
        $channels = Channel::with('categories')
            ->whereNotNull('epgid')
            ->inRandomOrder()
            ->limit($totalLimit * 2)
            ->get();

        $allSlides = [];

        foreach ($channels as $channel) {
            $slide = $this->generateSlideForChannel($channel);

            if ($slide) {
                $allSlides[] = $slide;
            }

            if (count($allSlides) >= $totalLimit) {
                break;
            }
        }

        foreach ($allSlides as $index => &$slide) {
            $slide['id'] = $index + 1;
        }

        return $allSlides;
    }

    /**
     * Generate a single slide for a specific channel.
     */
    public function getSlideForChannel(Channel $channel): ?array
    {
        $slide = $this->generateSlideForChannel($channel);
        if ($slide) {
            $slide['id'] = 1;
        }
        return $slide;
    }

    /**
     * Core logic to find the current show and map it to the slide format.
     */
    private function generateSlideForChannel(Channel $channel): ?array
    {
        $cacheKey = "epg:channel:{$channel->id}";
        $epgData  = Cache::get($cacheKey);

        if (!$epgData || empty($epgData['shows'])) {
            return null;
        }

        // Build a flat list of shows with resolved Carbon start/end times
        $resolved = $this->resolveShowTimes($epgData['shows'], $epgData['timestamp'] ?? null);

        $currentShow = $this->findCurrentShow($resolved)
            ?? $this->findNextShow($resolved)
            ?? ($epgData['shows'][0] ?? null); // final fallback

        if (!$currentShow) {
            return null;
        }

        $accent = $channel->categories->last()->color ?? '#3b82f6';

        return [
            'id'          => 0,
            'title'       => $currentShow['name'],
            'type'        => $currentShow['type'] ?? 'TV',
            'genres'      => [],
            'description' => $currentShow['description'] ?? 'No description available.',
            'rating'      => isset($currentShow['rating']) && $currentShow['rating'] !== null
                                ? number_format((float) $currentShow['rating'], 1)
                                : '?',
            'release'     => $currentShow['year'] ?? 'N/A',
            'quality'     => 'HD',
            'cc'          => 1,
            'ep'          => 1,
            'image'       => $currentShow['imageUrl'] ? $this->storeImage($currentShow['imageUrl'], 'guide', str($currentShow['name'])->slug()) : 'https://via.placeholder.com/1400x800?text=No+Image',
            'accent'      => $accent,
            'channel'     => $channel->only(['slug', 'logo']),
        ];
    }

    private function storeImage($remoteFile, $path, $name): string
    {
        $filename = $name . '.' . pathinfo($remoteFile, PATHINFO_EXTENSION);
        $filePath = $path . '/' . $filename;
        //check if file already exists
        if (Storage::disk('uploads')->exists($filePath)) {
            return $filePath;
        }
        // grab remote image and store it in the uploads disk
        $fileContents = file_get_contents($remoteFile);

        // Store the image on the 'uploads' disk
        Storage::disk('uploads')->put($filePath, $fileContents);

        // Return the constructed path
        return $filePath;
    }

    /**
     * Resolves each show's raw time string into Carbon start/end timestamps,
     * correctly handling midnight rollovers across the schedule.
     *
     * Returns an array of show data enriched with '_start' and '_end' Carbon instances.
     */
    private function resolveShowTimes(array $shows, ?string $timestamp): array
    {
        // Anchor date comes from the API timestamp (UTC). The 'time' field in the
        // response is already timezone-adjusted by the API, so we work in local
        // server time. Adjust here if your API returns a specific timezone.
        $baseDate         = $timestamp ? Carbon::parse($timestamp)->startOfDay() : Carbon::today();
        $previousSeconds  = -1;
        $resolved         = [];

        foreach ($shows as $show) {
            $parsed = $this->parseTimeString($show['time']);

            if ($parsed === null) {
                continue;
            }

            [$hour, $minute] = $parsed;
            $currentSeconds  = $hour * 3600 + $minute * 60;

            // If the schedule wraps past midnight, advance the base date once.
            // We use a threshold of 0 (strictly less than) rather than an arbitrary
            // offset, so any backward jump in seconds is treated as a day rollover.
            if ($previousSeconds !== -1 && $currentSeconds < $previousSeconds) {
                $baseDate = $baseDate->copy()->addDay();
            }

            $start = $baseDate->copy()->setTime($hour, $minute, 0);
            $end   = $start->copy()->addMinutes($this->parseDuration($show['duration']));

            $resolved[] = array_merge($show, [
                '_start' => $start,
                '_end'   => $end,
            ]);

            $previousSeconds = $currentSeconds;
        }

        return $resolved;
    }

    /**
     * Returns the show whose window contains the current time.
     */
    private function findCurrentShow(array $resolved): ?array
    {
        $now = Carbon::now();

        foreach ($resolved as $show) {
            if ($now->between($show['_start'], $show['_end'])) {
                return $show;
            }
        }

        return null;
    }

    /**
     * Returns the next show that hasn't started yet.
     */
    private function findNextShow(array $resolved): ?array
    {
        $now = Carbon::now();

        foreach ($resolved as $show) {
            if ($show['_start']->isFuture()) {
                return $show;
            }
        }

        return null;
    }

    /**
     * Parses "8:00 AM" / "11:30 PM" into [hour24, minute].
     * Returns null on malformed input.
     */
    private function parseTimeString(string $timeStr): ?array
    {
        if (!preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', trim($timeStr), $m)) {
            return null;
        }

        $hour   = (int) $m[1];
        $minute = (int) $m[2];
        $ampm   = strtoupper($m[3]);

        if ($ampm === 'PM' && $hour < 12) $hour += 12;
        if ($ampm === 'AM' && $hour === 12) $hour = 0;

        return [$hour, $minute];
    }

    /**
     * Parses "120 minutes" into an integer number of minutes.
     */
    private function parseDuration(string $durationStr): int
    {
        if (preg_match('/(\d+)\s*min/i', $durationStr, $matches)) {
            return (int) $matches[1];
        }
        return 0;
    }
}