<?php

namespace App\Models;

use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    /** @use HasFactory<OfferFactory> */
    use HasFactory;

    protected $fillable = [
        'landing_page_id',
        'name',
        'quantity',
        'price',
        'compare_at_price',
        'badge_text',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }
}
