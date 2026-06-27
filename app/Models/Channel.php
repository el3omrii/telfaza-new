<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Traits\ClearsNextjsCache;

class Channel extends Model
{
	use ClearsNextjsCache;
    protected $fillable = [
        'name', 'slug', 'description', 'logo', 'image', 'views', 'epgid', 'featured', 'country_id', 'language', 'quality',
    ];

    protected $casts = ['views' => 'integer', 'featured' => 'boolean'];

	protected $hidden = ['epgid'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(Source::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    protected static function booted(): void
    {
        static::creating(function ($channel) {
            if (empty($channel->slug)) {
                $channel->slug = self::generateUniqueSlug($channel->name);
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
