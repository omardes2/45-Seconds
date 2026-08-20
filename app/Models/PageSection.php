<?php

namespace App\Models;

use App\Enums\SectionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    protected $fillable = [
        'landing_page_id',
        'type',
        'position',
        'is_enabled',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'type' => SectionType::class,
            'position' => 'integer',
            'is_enabled' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    /**
     * A single setting value with a fallback.
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
