<?php

namespace App\Services\Pages;

use App\Enums\PageStatus;
use App\Enums\SectionType;
use App\Models\LandingPage;
use App\Models\Product;
use Illuminate\Support\Str;

class PageBuilder
{
    /**
     * Create a landing page for a product with the full canonical section
     * skeleton (one section per journey step, in order).
     */
    public function createForProduct(Product $product, string $name, ?int $userId = null): LandingPage
    {
        $page = new LandingPage([
            'product_id' => $product->id,
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'status' => PageStatus::Draft,
            'title' => $product->name,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
        $page->save();

        $this->ensureSkeleton($page);

        return $page;
    }

    /**
     * Make sure the page has one section per journey type, preserving existing
     * sections and their settings. Missing types are appended in journey order.
     */
    public function ensureSkeleton(LandingPage $page): void
    {
        $existing = $page->sections()->pluck('type')->all();
        $existing = array_map(fn ($t) => $t instanceof SectionType ? $t->value : $t, $existing);

        $position = (int) $page->sections()->max('position');

        foreach (SectionType::journey() as $type) {
            if (in_array($type->value, $existing, true)) {
                continue;
            }

            $page->sections()->create([
                'type' => $type,
                'position' => ++$position,
                'is_enabled' => true,
                'settings' => $type->defaultSettings(),
            ]);
        }
    }

    /**
     * @param  array<int, int>  $orderedIds  section ids in the desired order
     */
    public function reorder(LandingPage $page, array $orderedIds): void
    {
        $position = 0;
        foreach ($orderedIds as $id) {
            $page->sections()->whereKey($id)->update(['position' => ++$position]);
        }
    }

    public function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'page';
        $slug = $base;
        $i = 1;

        while (LandingPage::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
