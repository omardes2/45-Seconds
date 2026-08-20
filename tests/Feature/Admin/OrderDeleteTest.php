<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_order_can_be_deleted(): void
    {
        $staff = $this->staff();
        $order = Order::factory()->create();

        $this->actingAs($staff)->delete(route('admin.orders.destroy', $order))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSoftDeleted($order);

        // Deleted orders no longer show in the listing.
        $this->actingAs($staff)->get(route('admin.orders.index'))
            ->assertDontSee($order->order_number);
    }
}
