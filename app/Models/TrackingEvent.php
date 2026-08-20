<?php

namespace App\Models;

use App\Enums\TrackingEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'landing_page_id',
        'visitor_id',
        'session_id',
        'type',
        'event_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => TrackingEventType::class,
            'metadata' => 'array',
        ];
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }
}
