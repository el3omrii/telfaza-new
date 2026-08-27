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
        'enabled',
        'p2penabled',
        'channel_id',
    ];
 
    protected $casts = [
        'drm'       => 'boolean',
        'enabled'   => 'boolean',
        'p2penabled'=> 'boolean',
        'clearkeys' => 'array',
    ];
 
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    private function base64urlToHex($b64url) {
        $b64 = str_replace(['-', '_'], ['+', '/'], $b64url);
        while (strlen($b64) % 4 !== 0) $b64 .= '=';
        $bytes = base64_decode($b64);
        return bin2hex($bytes);
    }

    public function getClearkeysFormattedAttribute() {
        if (!$this->clearkeys) return '—';
        $formatted = [];
        foreach ($this->clearkeys as $kid => $key) {
            $formatted[] = $this->base64urlToHex($kid) . ':' . $this->base64urlToHex($key);
        }
        return implode('<br>', $formatted);
    }
}
