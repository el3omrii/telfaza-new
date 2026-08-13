<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\ClearsNextjsCache;

class Country extends Model
{
    use ClearsNextjsCache;
    protected $fillable = [
        'name',
        'flag',
        'slug'
    ];
 
    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }
}
