<?php

namespace Tests\Feature;

use App\Jobs\SendServerConversion;
use App\Models\LandingPage;
use App\Models\Offer;
use App\Models\Order;
use App\Services\Pages\PagePublisher;
use App\Services\SettingsRepository;
use App\Services\Tracking\TrackingManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TrackingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function publishedPage(): LandingPage
    {
        $page = LandingPage::factory()->create();
        Offer::factory()->create(['landing_page_id' => $page->id, 'price' => 149, 'quantity' => 1, 'is_default' => true]);
        app(PagePublisher::class)->publish($page);

        return $page->fresh();
    }

    private function settings(): SettingsRepository
    {
        return app(SettingsRepository::class);
    }

    public function test_no_pixel_script_when_disabled(): void
    {
        $page = $this->publishedPage();

        $this->get('/p/'.$page->slug)
            ->assertOk()
            ->assertDontSee('fbevents.js')
            ->assertDontSee('analytics.tiktok.com');
    }

    public function test_meta_pixel_is_injected_only_when_enabled_with_id(): void
    {
        $page = $this->publishedPage();
        $this->settings()->setMany('tracking_meta', ['enabled' => true, 'pixel_id' => '111222333444']);

        $html = $this->get('/p/'.$page->slug)->assertOk()->getContent();

        $this->assertStringContainsString('fbevents.js', $html);
        $this->assertStringContainsString('111222333444', $html);
    }

    public function test_tiktok_pixel_is_injected_when_enabled(): void
    {
        $page = $this->publishedPage();
        $this->settings()->setMany('tracking_tiktok', ['enabled' => true, 'pixel_id' => 'CABCDEF123']);

        $html = $this->get('/p/'.$page->slug)->assertOk()->getContent();

        $this->assertStringContainsString('analytics.tiktok.com', $html);
        $this->assertStringContainsString('CABCDEF123', $html);
    }

    public function test_access_token_is_never_exposed_in_public_html(): void
    {
        $page = $this->publishedPage();
        $this->settings()->setMany('tracking_meta', ['enabled' => true, 'pixel_id' => '111']);
        $this->settings()->set('tracking_meta', 'access_token', 'TOP-SECRET-TOKEN', encrypt: true);

        $html = $this->get('/p/'.$page->slug)->assertOk()->getContent();

        $this->assertStringNotContainsString('TOP-SECRET-TOKEN', $html);
    }

    public function test_purchase_pixel_fires_on_thank_you_after_order(): void
    {
        $page = $this->publishedPage();
        $this->settings()->setMany('tracking_meta', ['enabled' => true, 'pixel_id' => '111']);
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
        $html = $this->get('/p/'.$page->slug.'/thank-you')->assertOk()->getContent();

        $this->assertStringContainsString("fsTrack('Purchase'", $html);
        $this->assertStringContainsString('order_'.$order->id, $html);
    }

    public function test_server_conversion_job_is_queued_only_when_capi_enabled(): void
    {
        Queue::fake();
        $page = $this->publishedPage();
        $offer = $page->offers()->first();

        // CAPI disabled → no job.
        $this->post('/p/'.$page->slug.'/order', [
            'offer_id' => $offer->id, 'full_name' => 'عميل أول', 'phone' => '0591111111', 'city' => 'غزة', 'address' => 'شارع 1',
        ])->assertRedirect();
        Queue::assertNotPushed(SendServerConversion::class);

        // Enable CAPI with a token → job queued on the next order.
        $this->settings()->setMany('tracking_meta', ['enabled' => true, 'pixel_id' => '111', 'capi_enabled' => true]);
        $this->settings()->set('tracking_meta', 'access_token', 'secret', encrypt: true);

        $this->assertTrue(app(TrackingManager::class)->hasServerTracking(), 'manager should report server tracking on');

        $this->post('/p/'.$page->slug.'/order', [
            'offer_id' => $offer->id, 'full_name' => 'عميل ثاني', 'phone' => '0592222222', 'city' => 'غزة', 'address' => 'شارع 1',
        ])->assertRedirect();
        Queue::assertPushed(SendServerConversion::class);
    }

    public function test_tracking_manager_reflects_enabled_state(): void
    {
        $this->assertFalse(app(TrackingManager::class)->hasBrowserTracking());

        $this->settings()->setMany('tracking_meta', ['enabled' => true, 'pixel_id' => '999']);

        $this->assertTrue(app(TrackingManager::class)->hasBrowserTracking());
    }
}
