<?php namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait ClearsNextjsCache
{
	protected static function bootClearsNextjsCache(): void
	{
	    foreach (['created', 'updated', 'deleted'] as $event) {
	        static::$event(function ($model) {
	            $className = class_basename($model);
	            $tag = Str::plural(Str::kebab($className));
	
	            $payload = [
	                'tags' => [$tag],
	            ];
	
	            // Channel-specific invalidation
	            if ($tag === 'channels') {
	                $oldSlug = $model->getOriginal('slug');
	                $newSlug = $model->slug;
	
	                $slugs = array_values(array_unique(array_filter([
	                    $oldSlug,
	                    $newSlug,
	                ])));
	
	                $payload['channelSlugs'] = $slugs;
	
	                // Channel changes can affect channel listings too.
	                $payload['tags'] = [
	                    'channels',
	                    'channels:featured',
	                    'channels:trending',
	                ];
	            }
	
	            Http::withoutVerifying()
	                ->withHeaders([
	                    'x-revalidate-secret' => env('NEXTJS_REVALIDATION_SECRET'),
	                    'Accept' => 'application/json',
	                ])
	                ->timeout(10)
	                ->post(
	                    rtrim(env('NEXTJS_URL'), '/') . '/api/revalidate',
	                    $payload
	                );
	        });
	    }
	}
}
