<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'visitor_id',
        'first_seen_at',
        'last_seen_at',
        'user_agent',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }
}
