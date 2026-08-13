<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\ClearsNextjsCache;

class Category extends Model
{
    use ClearsNextjsCache;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
    ];
 
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class);
    }
}
