<?php

namespace App\Console\Commands;

use App\Models\Channel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

#[Signature('app:grab-epg')]
#[Description('grab epg data from elcinema.com')]
class GrabEPGData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Use cursor() to avoid loading all records into memory at once
        $channels = Channel::whereNotNull('epgid')
            ->where('epgid', '!=', '')
            ->cursor();

        $total = 0;
        $success = 0;
        $failed = 0;

        foreach ($channels as $channel) {
            $total++;

            try {
                // Replace with your actual EPG API endpoint
                // Tip: Store base URL in .env: EPG_API_URL=https://api.example.com/epg
                $url = config('services.epg.base_url') . '?channel=' . $channel->epgid;

                $response = Http::timeout(10)
                    ->retry(2, 100) // Retry twice with 100ms delay on failure
                    ->get($url);

                if ($response->successful()) {
                    $cacheKey = "epg:channel:{$channel->id}";
                    // Cache for 24 hours (adjust as needed)
                    Cache::put($cacheKey, $response->json(), now()->addHours(4));
                    $success++;
                } else {
                    $this->warn("⚠️  {$channel->name} (EPGID: {$channel->epgid}): HTTP {$response->status()}");
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->error("❌ {$channel->name}: {$e->getMessage()}");
                $failed++;
            }
            sleep(1); // Optional: Sleep for 1 second to avoid overwhelming the API
        }

        $this->newLine();
        $this->info("✅ Completed | Total: {$total} | Success: {$success} | Failed: {$failed}");

        return Command::SUCCESS;
    }
}
