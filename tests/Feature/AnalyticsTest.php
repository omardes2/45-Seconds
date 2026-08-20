<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\LandingPage;
use App\Models\Order;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function makeVisits(LandingPage $page, int $sessions, int $visitors): void
    {
        for ($i = 0; $i < $sessions; $i++) {
            DB::table('visits')->insert([
                'session_id' => (string) Str::uuid(),
                'visitor_id' => 'visitor-'.($i % $visitors),
                'landing_page_id' => $page->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function test_conversion_rate_is_orders_over_sessions(): void
    {
        $page = LandingPage::factory()->create();
        $this->makeVisits($page, sessions: 10, visitors: 8);
        Order::factory()->count(2)->create(['landing_page_id' => $page->id, 'product_id' => $page->product_id]);

        $report = app(AnalyticsService::class)->report();

        $this->assertSame(10, $report['totals']['sessions']);
        $this->assertSame(8, $report['totals']['visitors']);
        $this->assertSame(2, $report['totals']['orders']);
        // 2 / 10 = 20%
        $this->assertSame(20.0, $report['totals']['conversion_rate']);
    }

    public function test_revenue_excludes_cancelled_and_returned(): void
    {
        $page = LandingPage::factory()->create();
        Order::factory()->create(['landing_page_id' => $page->id, 'product_id' => $page->product_id, 'total' => 100, 'status' => OrderStatus::Delivered]);
        Order::factory()->create(['landing_page_id' => $page->id, 'product_id' => $page->product_id, 'total' => 50, 'status' => OrderStatus::New]);
        Order::factory()->create(['landing_page_id' => $page->id, 'product_id' => $page->product_id, 'total' => 999, 'status' => OrderStatus::Cancelled]);
        Order::factory()->create(['landing_page_id' => $page->id, 'product_id' => $page->product_id, 'total' => 999, 'status' => OrderStatus::Returned]);

        $report = app(AnalyticsService::class)->report();

        $this->assertSame(150.0, $report['totals']['revenue']);
        // AOV over the 2 revenue orders = 75
        $this->assertSame(75.0, $report['totals']['aov']);
    }

    public function test_date_range_filters_results(): void
    {
        $page = LandingPage::factory()->create();
        Order::factory()->create(['landing_page_id' => $page->id, 'product_id' => $page->product_id, 'created_at' => Carbon::parse('2020-01-01')]);
        Order::factory()->create(['landing_page_id' => $page->id, 'product_id' => $page->product_id, 'created_at' => now()]);

        $report = app(AnalyticsService::class)->report(now()->subDays(2), now());

        $this->assertSame(1, $report['totals']['orders']);
    }

    public function test_page_breakdown_is_per_page(): void
    {
        $a = LandingPage::factory()->create();
        $b = LandingPage::factory()->create();
        Order::factory()->count(3)->create(['landing_page_id' => $a->id, 'product_id' => $a->product_id]);
        Order::factory()->count(1)->create(['landing_page_id' => $b->id, 'product_id' => $b->product_id]);

        $pages = collect(app(AnalyticsService::class)->pageBreakdown())->keyBy('id');

        $this->assertSame(3, $pages[$a->id]['orders']);
        $this->assertSame(1, $pages[$b->id]['orders']);
    }

    public function test_analytics_page_renders_for_permitted_user(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.analytics.index'))->assertOk();
    }

    public function test_analytics_validates_date_order(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.analytics.index', ['from' => '2025-05-01', 'to' => '2025-01-01']))
            ->assertSessionHasErrors('to');
    }
}
