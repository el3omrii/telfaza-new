<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanOldFiles extends Command
{
    // The name and signature of the console command
    protected $signature = 'storage:clean-old {disk=local} {directory=uploads}';

    // The console command description
    protected $description = 'Delete files older than 48 hours in a specific directory';

    public function handle()
    {
        $disk = $this->argument('disk');
        $directory = $this->argument('directory');
        $cutoff = Carbon::now()->subHours(48);
        $count = 0;

        if (!Storage::disk($disk)->exists($directory)) {
            $this->error("Directory '{$directory}' does not exist on disk '{$disk}'.");
            return Command::FAILURE;
        }

        // Get all files in the directory
        $files = Storage::disk($disk)->files($directory);

        foreach ($files as $file) {
            // Get last modified time
            $lastModified = Carbon::createFromTimestamp(Storage::disk($disk)->lastModified($file));

            // Check if file is older than 48 hours
            if ($lastModified->lt($cutoff)) {
                Storage::disk($disk)->delete($file);
                $count++;
            }
        }

        $this->info("Successfully deleted {$count} old files from '{$directory}'.");
        return Command::SUCCESS;
    }
}
