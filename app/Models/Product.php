<?php

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Enums\ProductStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'base_price',
        'compare_at_price',
        'currency',
        'status',
        'main_image',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'currency' => CurrencyEnum::class,
            'status' => ProductStatus::class,
        ];
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    public function landingPages(): HasMany
    {
        return $this->hasMany(LandingPage::class);
    }

    public function mainImageUrl(): ?string
    {
        if (! $this->main_image) {
            return null;
        }

        return Storage::disk('public')->url($this->main_image);
    }

    public function isActive(): bool
    {
        return $this->status === ProductStatus::Active;
    }
}
