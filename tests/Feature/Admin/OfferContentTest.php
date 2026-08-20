<?php

namespace Tests\Feature\Admin;

use App\Models\LandingPage;
use App\Models\Offer;
use App\Services\Pages\PageSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_offer_can_be_default(): void
    {
        $staff = $this->staff();
        $page = LandingPage::factory()->create();
        $first = Offer::factory()->create(['landing_page_id' => $page->id, 'is_default' => true, 'name' => 'الأول']);

        // Add a second default offer.
        $this->actingAs($staff)->post(route('admin.pages.offers.store', $page), [
            'name' => 'الثاني',
            'quantity' => 2,
            'price' => '149',
            'is_default' => '1',
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertSame(1, $page->offers()->where('is_default', true)->count());
        $this->assertFalse($first->fresh()->is_default);
    }

    public function test_deleting_the_default_offer_promotes_another(): void
    {
        $staff = $this->staff();
        $page = LandingPage::factory()->create();
        $default = Offer::factory()->create(['landing_page_id' => $page->id, 'is_default' => true, 'sort_order' => 0]);
        $other = Offer::factory()->create(['landing_page_id' => $page->id, 'is_default' => false, 'sort_order' => 1]);

        $this->actingAs($staff)->delete(route('admin.pages.offers.destroy', [$page, $default]))->assertRedirect();

        $this->assertTrue($other->fresh()->is_default);
    }

    public function test_offer_compare_price_must_be_gte_price(): void
    {
        $staff = $this->staff();
        $page = LandingPage::factory()->create();

        $this->actingAs($staff)->post(route('admin.pages.offers.store', $page), [
            'name' => 'خصم',
            'quantity' => 1,
            'price' => '100',
            'compare_at_price' => '50',
        ])->assertSessionHasErrors('compare_at_price');
    }

    public function test_snapshot_only_includes_active_testimonials(): void
    {
        $page = LandingPage::factory()->create();
        Offer::factory()->create(['landing_page_id' => $page->id]);
        $page->testimonials()->create(['customer_name' => 'ظاهر', 'rating' => 5, 'text' => 'رائع', 'is_active' => true]);
        $page->testimonials()->create(['customer_name' => 'مخفي', 'rating' => 4, 'text' => 'جيد', 'is_active' => false]);

        $snapshot = app(PageSnapshotService::class)->build($page);

        $names = collect($snapshot['testimonials'])->pluck('customer_name');
        $this->assertContains('ظاهر', $names);
        $this->assertNotContains('مخفي', $names);
    }

    public function test_snapshot_only_includes_active_offers_and_faqs(): void
    {
        $page = LandingPage::factory()->create();
        Offer::factory()->create(['landing_page_id' => $page->id, 'name' => 'مفعل', 'is_active' => true, 'is_default' => true]);
        Offer::factory()->create(['landing_page_id' => $page->id, 'name' => 'معطل', 'is_active' => false, 'is_default' => false]);
        $page->faqs()->create(['question' => 'س1', 'answer' => 'ج1', 'is_active' => true]);
        $page->faqs()->create(['question' => 'س2', 'answer' => 'ج2', 'is_active' => false]);

        $snapshot = app(PageSnapshotService::class)->build($page);

        $this->assertCount(1, $snapshot['offers']);
        $this->assertSame('مفعل', $snapshot['offers'][0]['name']);
        $this->assertCount(1, $snapshot['faqs']);
    }

    public function test_staff_can_manage_faqs(): void
    {
        $staff = $this->staff();
        $page = LandingPage::factory()->create();

        $this->actingAs($staff)->post(route('admin.pages.faqs.store', $page), [
            'question' => 'هل الدفع عند الاستلام؟',
            'answer' => 'نعم.',
        ])->assertRedirect();

        $this->assertDatabaseHas('faqs', ['landing_page_id' => $page->id, 'question' => 'هل الدفع عند الاستلام؟']);
    }
}
