<?php

namespace Tests\Feature\Admin;

use App\Enums\PageStatus;
use App\Enums\SectionType;
use App\Models\LandingPage;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Pages\PageBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_page_builds_the_full_section_skeleton(): void
    {
        $staff = $this->staff();
        $product = Product::factory()->create();

        $this->actingAs($staff)->post(route('admin.pages.store'), [
            'product_id' => $product->id,
            'name' => 'حملة تجريبية',
        ])->assertRedirect();

        $page = LandingPage::first();
        $this->assertNotNull($page);
        $this->assertCount(count(SectionType::journey()), $page->sections);
        $this->assertEqualsCanonicalizing(
            array_map(fn ($t) => $t->value, SectionType::journey()),
            $page->sections->map(fn ($s) => $s->type->value)->all(),
        );
    }

    public function test_page_slugs_are_unique(): void
    {
        $staff = $this->staff();
        LandingPage::factory()->create(['slug' => 'sleep-light']);
        $product = Product::factory()->create();

        $page = app(PageBuilder::class)->createForProduct($product, 'sleep light');
        $this->assertNotSame('sleep-light', $page->slug);
    }

    public function test_publishing_requires_an_offer(): void
    {
        $staff = $this->staff();
        $page = LandingPage::factory()->create();

        $this->actingAs($staff)->post(route('admin.pages.publish', $page))
            ->assertRedirect();

        $this->assertNull($page->fresh()->published_at);
        $this->assertSame(PageStatus::Draft, $page->fresh()->status);
    }

    public function test_publishing_builds_a_snapshot_and_exposes_the_page(): void
    {
        $staff = $this->staff();
        $page = LandingPage::factory()->create();
        Offer::factory()->create(['landing_page_id' => $page->id]);

        $this->actingAs($staff)->post(route('admin.pages.publish', $page))->assertRedirect();

        $page->refresh();
        $this->assertSame(PageStatus::Published, $page->status);
        $this->assertNotEmpty($page->published_snapshot);
        $this->assertArrayHasKey('offers', $page->published_snapshot);

        // Public page is now reachable.
        $this->get('/p/'.$page->slug)->assertOk();
    }

    public function test_draft_pages_are_not_publicly_accessible(): void
    {
        $page = LandingPage::factory()->create(['status' => PageStatus::Draft]);

        $this->get('/p/'.$page->slug)->assertNotFound();
    }

    public function test_paused_pages_are_not_publicly_accessible(): void
    {
        $staff = $this->staff();
        $page = LandingPage::factory()->create();
        Offer::factory()->create(['landing_page_id' => $page->id]);
        $this->actingAs($staff)->post(route('admin.pages.publish', $page));

        $this->actingAs($staff)->post(route('admin.pages.pause', $page));

        $this->get('/p/'.$page->slug)->assertNotFound();
    }

    public function test_editing_a_draft_does_not_change_the_published_snapshot(): void
    {
        $staff = $this->staff();
        $page = LandingPage::factory()->create();
        Offer::factory()->create(['landing_page_id' => $page->id, 'name' => 'العرض الأصلي']);
        $this->actingAs($staff)->post(route('admin.pages.publish', $page));

        $originalSnapshot = $page->fresh()->published_snapshot;

        // Change the offer in the draft.
        $page->offers()->update(['name' => 'العرض المعدل']);

        // Public still shows the old snapshot until re-published.
        $this->assertSame('العرض الأصلي', $page->fresh()->published_snapshot['offers'][0]['name']);
        $this->assertSame($originalSnapshot, $page->fresh()->published_snapshot);
    }

    public function test_preview_renders_live_draft_data(): void
    {
        $staff = $this->staff();
        $page = LandingPage::factory()->create();
        Offer::factory()->create(['landing_page_id' => $page->id]);

        $this->actingAs($staff)->get(route('admin.pages.preview', $page))
            ->assertOk()
            ->assertSee('45');
    }
}
