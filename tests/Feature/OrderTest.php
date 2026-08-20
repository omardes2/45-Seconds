<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PageStatus;
use App\Models\LandingPage;
use App\Models\Offer;
use App\Models\Order;
use App\Services\Pages\PagePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function publishedPage(float $offerPrice = 149, int $qty = 2): LandingPage
    {
        $page = LandingPage::factory()->create();
        Offer::factory()->create([
            'landing_page_id' => $page->id,
            'name' => 'قطعتان',
            'quantity' => $qty,
            'price' => $offerPrice,
            'is_default' => true,
            'is_active' => true,
        ]);
        app(PagePublisher::class)->publish($page);

        return $page->fresh();
    }

    private function validPayload(Offer $offer, array $overrides = []): array
    {
        return array_merge([
            'offer_id' => $offer->id,
            'full_name' => 'أحمد محمد',
            'phone' => '0591234567',
            'city' => 'رام الله',
            'area' => 'الطيرة',
            'address' => 'شارع الإرسال 12',
        ], $overrides);
    }

    public function test_a_valid_order_is_created_with_server_computed_price(): void
    {
        $page = $this->publishedPage(149, 2);
        $offer = $page->offers()->first();

        $this->post("/p/{$page->slug}/order", $this->validPayload($offer))
            ->assertRedirect(route('public.thankyou', $page->slug));

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame('149.00', $order->total);
        $this->assertSame('149.00', $order->subtotal);
        $this->assertSame(2, $order->quantity);
        $this->assertSame(OrderStatus::New, $order->status);
        $this->assertStringStartsWith('45', $order->order_number);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'to_status' => 'new']);
    }

    public function test_client_cannot_tamper_with_the_price(): void
    {
        $page = $this->publishedPage(149, 1);
        $offer = $page->offers()->first();

        // Malicious client sends total=1, price=1, subtotal=1, quantity=99.
        $this->post("/p/{$page->slug}/order", $this->validPayload($offer, [
            'total' => 1,
            'price' => 1,
            'subtotal' => 1,
            'unit_price' => 1,
            'quantity' => 99,
        ]))->assertRedirect();

        $order = Order::first();
        // Server recomputed from the DB offer, ignoring client values.
        $this->assertSame('149.00', $order->total);
        $this->assertSame(1, $order->quantity);
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $page = $this->publishedPage();
        $offer = $page->offers()->first();

        $this->post("/p/{$page->slug}/order", $this->validPayload($offer, ['phone' => 'abc']))
            ->assertSessionHasErrors('phone');
        $this->assertSame(0, Order::count());
    }

    public function test_nonexistent_offer_is_rejected(): void
    {
        $page = $this->publishedPage();

        $this->post("/p/{$page->slug}/order", $this->validPayload($page->offers()->first(), ['offer_id' => 99999]))
            ->assertSessionHasErrors('offer_id');
        $this->assertSame(0, Order::count());
    }

    public function test_offer_from_another_page_is_rejected(): void
    {
        $page = $this->publishedPage();
        $otherPage = $this->publishedPage();
        $foreignOffer = $otherPage->offers()->first();

        $this->post("/p/{$page->slug}/order", $this->validPayload($page->offers()->first(), ['offer_id' => $foreignOffer->id]))
            ->assertSessionHasErrors('offer_id');
        $this->assertSame(0, Order::where('landing_page_id', $page->id)->count());
    }

    public function test_disabled_offer_is_rejected(): void
    {
        $page = $this->publishedPage();
        $offer = $page->offers()->first();
        $offer->update(['is_active' => false]);

        $this->post("/p/{$page->slug}/order", $this->validPayload($offer))
            ->assertSessionHasErrors('offer_id');
        $this->assertSame(0, Order::count());
    }

    public function test_orders_are_rejected_on_draft_paused_and_archived_pages(): void
    {
        foreach ([PageStatus::Draft, PageStatus::Paused, PageStatus::Archived] as $status) {
            $page = $this->publishedPage();
            $offer = $page->offers()->first();
            $page->update(['status' => $status]);

            $this->post("/p/{$page->slug}/order", $this->validPayload($offer))->assertNotFound();
        }

        $this->assertSame(0, Order::count());
    }

    public function test_order_status_can_follow_allowed_transitions(): void
    {
        $admin = $this->superAdmin();
        $order = Order::factory()->create(['status' => OrderStatus::New]);

        $this->actingAs($admin)->put(route('admin.orders.status', $order), [
            'status' => OrderStatus::Confirmed->value,
        ])->assertRedirect();

        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id, 'from_status' => 'new', 'to_status' => 'confirmed',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'order_status_changed']);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $admin = $this->superAdmin();
        $order = Order::factory()->create(['status' => OrderStatus::Delivered]);

        // Delivered -> New is not allowed.
        $this->actingAs($admin)->put(route('admin.orders.status', $order), [
            'status' => OrderStatus::New->value,
        ])->assertSessionHasErrors('status');

        $this->assertSame(OrderStatus::Delivered, $order->fresh()->status);
    }

    public function test_order_search_by_phone_and_number(): void
    {
        $admin = $this->superAdmin();
        $order = Order::factory()->create(['phone' => '0599999999', 'order_number' => '459999']);
        Order::factory()->create(['phone' => '0591111111']);

        $this->actingAs($admin)->get(route('admin.orders.index', ['q' => '0599999999']))
            ->assertOk()->assertSee('459999');
    }

    public function test_checkout_is_rate_limited(): void
    {
        $page = $this->publishedPage();
        $offer = $page->offers()->first();
        $max = config('fortyfive.checkout_rate_limit.max_attempts');

        for ($i = 0; $i < $max; $i++) {
            $this->post("/p/{$page->slug}/order", $this->validPayload($offer, ['phone' => '059000000'.$i]));
        }

        $this->post("/p/{$page->slug}/order", $this->validPayload($offer))->assertStatus(429);
    }
}
