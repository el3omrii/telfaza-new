<?php namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait ClearsNextjsCache
{
    protected static function bootClearsNextjsCache()
    {
        $events = ['saved', 'deleted', 'created', 'updated'];

        foreach ($events as $event) {
            static::$event(function ($model) {
                // Determine tag automatically based on model name (e.g., Product -> products)
                $className = class_basename($model);
                $tag = Str::plural(Str::kebab($className));

                // Send the webhook to Next.js
                Http::withoutVerifying() // Optional: if using local self-signed SSL
					 ->withHeaders([
                    	'x-revalidate-secret' => env('NEXTJS_REVALIDATION_SECRET'),
                		])
                    ->post(env('NEXTJS_URL') . '/api/revalidate', [
                        'tag' => $tag,
                    ]);
            });
        }
    }
}
