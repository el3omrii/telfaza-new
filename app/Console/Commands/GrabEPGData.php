<?php

namespace App\Console\Commands;

use App\Models\Channel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
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

        $revalidationFailed = false;
        if ($success > 0) {
            try {
                $response = Http::withHeaders([
                    'x-revalidate-secret' => env('NEXTJS_REVALIDATION_SECRET'),
                    'Accept' => 'application/json',
                ])
                    ->timeout(10)
                    ->post(
                        rtrim((string) env('NEXTJS_URL'), '/') . '/api/revalidate',
                        ['tags' => ['slides']]
                    );

                if (!$response->successful()) {
                    $this->error("❌ Slides cache revalidation failed: HTTP {$response->status()}");
                    $revalidationFailed = true;
                }
            } catch (ConnectionException $e) {
                $this->error("❌ Slides cache revalidation failed: {$e->getMessage()}");
                $revalidationFailed = true;
            }
        }

        $this->newLine();
        $this->info("✅ Completed | Total: {$total} | Success: {$success} | Failed: {$failed}");

        return $revalidationFailed ? Command::FAILURE : Command::SUCCESS;
    }
}
