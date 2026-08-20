<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Testimonial extends Model
{
    protected $fillable = [
        'landing_page_id',
        'customer_name',
        'customer_image',
        'rating',
        'text',
        'video_url',
        'is_verified',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function imageUrl(): ?string
    {
        return $this->customer_image ? Storage::disk('public')->url($this->customer_image) : null;
    }
}
