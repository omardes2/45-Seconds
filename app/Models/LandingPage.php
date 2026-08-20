<?php

namespace App\Models;

use App\Enums\PageStatus;
use Database\Factories\LandingPageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandingPage extends Model
{
    /** @use HasFactory<LandingPageFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'name',
        'slug',
        'status',
        'title',
        'meta_description',
        'og_title',
        'og_description',
        'og_image',
        'published_at',
        'settings',
        'options',
        'published_snapshot',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'published_at' => 'datetime',
            'settings' => 'array',
            'options' => 'array',
            'published_snapshot' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('position');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class)->orderBy('sort_order');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class)->orderBy('sort_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPublished(): bool
    {
        return $this->status === PageStatus::Published;
    }

    public function hasSnapshot(): bool
    {
        return ! empty($this->published_snapshot);
    }

    public function publicUrl(): string
    {
        return url('/p/'.$this->slug);
    }
}
