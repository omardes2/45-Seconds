<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    protected $fillable = [
        'session_id',
        'visitor_id',
        'landing_page_id',
        'first_seen_at',
        'last_seen_at',
        'user_agent',
        'ip_address',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'fbclid',
        'ttclid',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    /**
     * The attribution subset, for snapshotting onto an order.
     *
     * @return array<string, mixed>
     */
    public function attributionData(): array
    {
        return [
            'landing_page_id' => $this->landing_page_id,
            'visitor_id' => $this->visitor_id,
            'session_id' => $this->session_id,
            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign,
            'utm_content' => $this->utm_content,
            'utm_term' => $this->utm_term,
            'fbclid' => $this->fbclid,
            'ttclid' => $this->ttclid,
            'referrer' => $this->referrer,
        ];
    }
}
