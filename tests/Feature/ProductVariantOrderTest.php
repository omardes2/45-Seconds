<?php

namespace Tests\Feature;

use App\Models\LandingPage;
use App\Models\Offer;
use App\Services\Pages\PagePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantOrderTest extends TestCase
{
    use RefreshDatabase;

    private function publishedPageWithVariants(int $quantity = 2): LandingPage
    {
        $page = LandingPage::factory()->create([
            'options' => [['name' => 'צבע', 'choices' => ['לבן', 'שחור']]],
        ]);
        Offer::factory()->create([
            'landing_page_id' => $page->id,
            'quantity' => $quantity,
            'price' => 100,
            'is_default' => true,
            'is_active' => true,
        ]);
        app(PagePublisher::class)->publish($page);

        return $page->fresh();
    }

    public function test_snapshot_exposes_the_variant_groups(): void
    {
        $page = $this->publishedPageWithVariants();

        $this->assertSame(
            [['name' => 'צבע', 'choices' => ['לבן', 'שחור']]],
            $page->published_snapshot['options'],
        );
    }

    public function test_order_stores_a_variant_choice_per_unit(): void
    {
        $page = $this->publishedPageWithVariants(quantity: 2);
        $offer = $page->offers()->first();

        $this->post(route('public.order.store', $page->slug), [
            'offer_id' => $offer->id,
            'full_name' => 'ישראל ישראלי',
            'phone' => '0591234567',
            'city' => 'תל אביב',
            'address' => 'הרצל 10',
            'options' => [
                ['צבע' => 'לבן'],
                ['צבע' => 'שחור'],
            ],
        ])->assertRedirect();

        $order = $page->orders()->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame([['צבע' => 'לבן'], ['צבע' => 'שחור']], $order->options);
    }

    public function test_order_is_rejected_when_a_unit_has_no_valid_variant(): void
    {
        $page = $this->publishedPageWithVariants(quantity: 2);
        $offer = $page->offers()->first();

        $this->from('/p/'.$page->slug)->post(route('public.order.store', $page->slug), [
            'offer_id' => $offer->id,
            'full_name' => 'ישראל ישראלי',
            'phone' => '0591234567',
            'city' => 'תל אביב',
            'address' => 'הרצל 10',
            'options' => [
                ['צבע' => 'לבן'],
                ['צבע' => 'לא קיים'], // invalid choice for unit 2
            ],
        ])->assertSessionHasErrors('options.1.צבע');

        $this->assertSame(0, $page->orders()->count());
    }
}
