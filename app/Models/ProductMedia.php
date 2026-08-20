<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductMedia extends Model
{
    protected $table = 'product_media';

    protected $fillable = [
        'product_id',
        'type',
        'disk',
        'path',
        'url',
        'mime',
        'size',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function resolvedUrl(): ?string
    {
        if ($this->url) {
            return $this->url;
        }

        return $this->path ? Storage::disk($this->disk)->url($this->path) : null;
    }
}
