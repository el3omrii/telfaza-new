<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'flag',
    ];
 
    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }
}
