<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    protected $fillable = [
        'name',
        'description',
        'logo',
        'image',
        'views',
        'country_id',
    ];
 
    protected $casts = [
        'views' => 'integer',
    ];
 
    // ─── Relationships ───────────────────────────────────────────────────────────
 
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
 
    // ─── Helpers ─────────────────────────────────────────────────────────────────
 
    public function incrementViews(): void
    {
        $this->increment('views');
    }
}
