<?php

namespace App\Services\Pages;

use App\Enums\DemoType;
use App\Enums\MediaType;
use App\Enums\SectionType;
use App\Models\LandingPage;
use Illuminate\Support\Facades\Storage;

/**
 * Builds a fully-resolved, self-contained array representation of a landing
 * page. The SAME builder feeds both the live preview (from draft models) and
 * the stored published snapshot, so what an editor previews is exactly what a
 * visitor sees after publishing. See docs/DECISIONS.md (Publishing).
 */
class PageSnapshotService
{
    /**
     * Setting keys, per section type, that hold stored image paths and should
     * be resolved to public URLs (exposed as `<key>_url`).
     *
     * @var array<string, array<int, string>>
     */
    private const IMAGE_KEYS = [
        'hero' => ['main_image', 'background_image'],
        'problem' => ['image'],
        'demo' => ['image', 'before_image', 'after_image'],
    ];

    /**
     * @return array<string, mixed>
     */
    public function build(LandingPage $page): array
    {
        $page->loadMissing(['product.media', 'sections', 'offers', 'testimonials', 'faqs']);

        $currency = $page->product->currency->value;

        $offers = $page->offers
            ->where('is_active', true)
            ->sortBy('sort_order')
            ->values();

        // Guarantee exactly one default offer in the snapshot.
        $defaultOffer = $offers->firstWhere('is_default', true) ?? $offers->first();

        return [
            'page' => [
                'id' => $page->id,
                'name' => $page->name,
                'slug' => $page->slug,
                'title' => $page->title ?: $page->product->name,
            ],
            'seo' => [
                'title' => $page->title ?: $page->product->name,
                'description' => $page->meta_description,
                'og_title' => $page->og_title ?: $page->title,
                'og_description' => $page->og_description ?: $page->meta_description,
                'og_image' => $page->og_image ? Storage::disk('public')->url($page->og_image) : $page->product->mainImageUrl(),
            ],
            'product' => [
                'id' => $page->product->id,
                'name' => $page->product->name,
                'currency' => $currency,
                'base_price' => (string) $page->product->base_price,
                'compare_at_price' => $page->product->compare_at_price ? (string) $page->product->compare_at_price : null,
                'main_image_url' => $page->product->mainImageUrl(),
                'gallery' => $page->product->media
                    ->where('type', MediaType::Image)
                    ->map(fn ($m) => $m->resolvedUrl())
                    ->filter()
                    ->values()
                    ->all(),
            ],
            'currency' => $currency,
            'sections' => $page->sections
                ->where('is_enabled', true)
                ->sortBy('position')
                ->map(fn ($section) => $this->resolveSection($section))
                ->values()
                ->all(),
            'offers' => $offers->map(fn ($offer) => [
                'id' => $offer->id,
                'name' => $offer->name,
                'quantity' => $offer->quantity,
                'price' => (string) $offer->price,
                'compare_at_price' => $offer->compare_at_price ? (string) $offer->compare_at_price : null,
                'badge_text' => $offer->badge_text,
                'is_default' => $defaultOffer && $offer->id === $defaultOffer->id,
            ])->all(),
            'default_offer_id' => $defaultOffer?->id,
            'testimonials' => $page->testimonials
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->map(fn ($t) => [
                    'customer_name' => $t->customer_name,
                    'customer_image_url' => $t->imageUrl(),
                    'rating' => $t->rating,
                    'text' => $t->text,
                    'video_url' => $t->video_url,
                    'is_verified' => $t->is_verified,
                ])->values()->all(),
            'faqs' => $page->faqs
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->map(fn ($f) => ['question' => $f->question, 'answer' => $f->answer])
                ->values()->all(),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveSection($section): array
    {
        $settings = $section->settings ?? [];
        $typeValue = $section->type instanceof SectionType ? $section->type->value : $section->type;

        // Resolve stored image paths to public URLs.
        foreach (self::IMAGE_KEYS[$typeValue] ?? [] as $key) {
            $path = $settings[$key] ?? null;
            $settings[$key.'_url'] = $path ? Storage::disk('public')->url($path) : null;
        }

        // Normalise demo type to a plain string for the view.
        if ($typeValue === 'demo') {
            $settings['demo_type'] = $settings['demo_type'] ?? DemoType::Image->value;
        }

        return [
            'type' => $typeValue,
            'second' => $section->type instanceof SectionType ? $section->type->second() : 0,
            'settings' => $settings,
        ];
    }
}
