<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAttribution extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'landing_page_id',
        'visitor_id',
        'session_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'fbclid',
        'ttclid',
        'referrer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
