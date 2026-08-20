<?php

namespace Tests\Feature;

use App\Enums\TrackingEventType;
use App\Models\LandingPage;
use App\Models\Offer;
use App\Models\Order;
use App\Models\TrackingEvent;
use App\Models\Visit;
use App\Models\Visitor;
use App\Services\Pages\PagePublisher;
use App\Services\Tracking\VisitorTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    private function publishedPage(): LandingPage
    {
        $page = LandingPage::factory()->create();
        Offer::factory()->create(['landing_page_id' => $page->id, 'price' => 149, 'quantity' => 2, 'is_default' => true]);
        app(PagePublisher::class)->publish($page);

        return $page->fresh();
    }

    public function test_visiting_a_page_creates_a_visitor_visit_and_sets_a_cookie(): void
    {
        $page = $this->publishedPage();

        $response = $this->get('/p/'.$page->slug.'?utm_source=facebook&utm_campaign=launch&fbclid=abc123');

        $response->assertOk();
        $response->assertCookie(config('fortyfive.visitor_cookie'));

        $this->assertSame(1, Visitor::count());
        $visit = Visit::first();
        $this->assertNotNull($visit);
        $this->assertSame('facebook', $visit->utm_source);
        $this->assertSame('launch', $visit->utm_campaign);
        $this->assertSame('abc123', $visit->fbclid);
        $this->assertSame($page->id, $visit->landing_page_id);
    }

    public function test_page_view_event_is_recorded(): void
    {
        $page = $this->publishedPage();

        $this->get('/p/'.$page->slug);

        $this->assertDatabaseHas('tracking_events', [
            'landing_page_id' => $page->id,
            'type' => 'page_view',
        ]);
    }

    public function test_order_snapshots_attribution_from_the_visit(): void
    {
        $page = $this->publishedPage();
        $offer = $page->offers()->first();

        // Visit with UTM first (starts the session + visit).
        $this->get('/p/'.$page->slug.'?utm_source=tiktok&utm_medium=cpc&ttclid=tt999');

        $this->post('/p/'.$page->slug.'/order', [
            'offer_id' => $offer->id,
            'full_name' => 'عميل',
            'phone' => '0591234567',
            'city' => 'غزة',
            'address' => 'شارع 1',
        ])->assertRedirect();

        $order = Order::first();
        $this->assertNotNull($order->attribution);
        $this->assertSame('tiktok', $order->attribution->utm_source);
        $this->assertSame('cpc', $order->attribution->utm_medium);
        $this->assertSame('tt999', $order->attribution->ttclid);
        $this->assertNotNull($order->visitor_id);
    }

    public function test_order_created_tracking_event_is_recorded_with_shared_event_id(): void
    {
        $page = $this->publishedPage();
        $offer = $page->offers()->first();

        $this->get('/p/'.$page->slug);
        $this->post('/p/'.$page->slug.'/order', [
            'offer_id' => $offer->id,
            'full_name' => 'عميل',
            'phone' => '0591234567',
            'city' => 'غزة',
            'address' => 'شارع 1',
        ]);

        $order = Order::first();
        $this->assertDatabaseHas('tracking_events', [
            'type' => 'order_created',
            'event_id' => 'order_'.$order->id,
        ]);
    }

    public function test_client_event_endpoint_records_allowed_events(): void
    {
        $page = $this->publishedPage();

        $this->postJson(route('track.event'), [
            'type' => 'offer_selected',
            'page_id' => $page->id,
            'metadata' => ['offer_id' => 5],
        ])->assertNoContent();

        $this->assertDatabaseHas('tracking_events', ['type' => 'offer_selected', 'landing_page_id' => $page->id]);
    }

    public function test_client_event_endpoint_rejects_unknown_types(): void
    {
        $page = $this->publishedPage();

        $this->postJson(route('track.event'), ['type' => 'purchase', 'page_id' => $page->id])
            ->assertStatus(422);
    }

    public function test_tracking_events_dedupe_on_event_id(): void
    {
        $page = $this->publishedPage();
        $tracker = app(VisitorTracker::class);

        $tracker->recordEvent(TrackingEventType::ViewContent, $page, 'v1', 's1', [], 'dup-1');
        $tracker->recordEvent(TrackingEventType::ViewContent, $page, 'v1', 's1', [], 'dup-1');

        $this->assertSame(1, TrackingEvent::where('event_id', 'dup-1')->count());
    }
}
