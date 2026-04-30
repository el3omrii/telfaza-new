<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Source extends Model
{
    protected $fillable = [
        'type',
        'link',
        'drm',
        'clearkeys',
        'channel_id',
    ];
 
    protected $casts = [
        'drm'       => 'boolean',
    ];
 
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}
