<?php

namespace App\Services\Analytics;

use App\Enums\OrderStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Computes admin dashboard + per-page analytics. Every query is guarded by a
 * Schema::hasTable() check so the dashboard renders a clean zero-state on a
 * fresh install before the domain tables exist.
 *
 * Conversion rate methodology (documented in docs/DECISIONS.md):
 *   conversion_rate = orders_created / unique_sessions_on_page.
 * Revenue counts orders whose status is not cancelled/returned.
 */
class AnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboardSummary(): array
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        return [
            'orders_today' => $this->ordersCount($today),
            'orders_month' => $this->ordersCount($monthStart),
            'visitors_today' => $this->visitorsCount($today),
            'revenue_today' => $this->revenueSum($today),
            'revenue_month' => $this->revenueSum($monthStart),
            'aov' => $this->averageOrderValue($monthStart),
            'conversion_rate' => $this->conversionRate($today),
            'currency' => config('fortyfive.default_currency', 'ILS'),
            'recent_orders' => $this->recentOrders(),
            'top_pages' => $this->topPages($monthStart),
        ];
    }

    private function ordersCount(?Carbon $since = null): int
    {
        if (! Schema::hasTable('orders')) {
            return 0;
        }

        return DB::table('orders')
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->count();
    }

    private function revenueSum(?Carbon $since = null): float
    {
        if (! Schema::hasTable('orders')) {
            return 0.0;
        }

        return (float) DB::table('orders')
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->whereNotIn('status', [OrderStatus::Cancelled->value, OrderStatus::Returned->value])
            ->sum('total');
    }

    private function averageOrderValue(?Carbon $since = null): float
    {
        if (! Schema::hasTable('orders')) {
            return 0.0;
        }

        $query = DB::table('orders')
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->whereNotIn('status', [OrderStatus::Cancelled->value, OrderStatus::Returned->value]);

        $count = (clone $query)->count();

        return $count > 0 ? round((float) $query->sum('total') / $count, 2) : 0.0;
    }

    private function visitorsCount(?Carbon $since = null): int
    {
        if (! Schema::hasTable('visits')) {
            return 0;
        }

        return DB::table('visits')
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->distinct('visitor_id')
            ->count('visitor_id');
    }

    private function conversionRate(?Carbon $since = null): float
    {
        $sessions = $this->sessionsCount($since);
        $orders = $this->ordersCount($since);

        return $sessions > 0 ? round(($orders / $sessions) * 100, 1) : 0.0;
    }

    private function sessionsCount(?Carbon $since = null): int
    {
        if (! Schema::hasTable('visits')) {
            return 0;
        }

        return DB::table('visits')
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->count();
    }

    /**
     * @return array<int, object>
     */
    private function recentOrders(int $limit = 5): array
    {
        if (! Schema::hasTable('orders')) {
            return [];
        }

        return DB::table('orders')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['order_number', 'full_name', 'total', 'currency', 'status', 'created_at'])
            ->all();
    }

    /**
     * @return array<int, object>
     */
    private function topPages(?Carbon $since = null, int $limit = 5): array
    {
        if (! Schema::hasTable('orders') || ! Schema::hasTable('landing_pages')) {
            return [];
        }

        return DB::table('landing_pages')
            ->leftJoin('orders', function ($join) use ($since) {
                $join->on('orders.landing_page_id', '=', 'landing_pages.id');
                if ($since) {
                    $join->where('orders.created_at', '>=', $since);
                }
            })
            ->groupBy('landing_pages.id', 'landing_pages.name')
            ->orderByDesc(DB::raw('COUNT(orders.id)'))
            ->limit($limit)
            ->get([
                'landing_pages.id',
                'landing_pages.name',
                DB::raw('COUNT(orders.id) as orders_count'),
            ])
            ->all();
    }

    /**
     * Full analytics report for an (optional) date range: overall totals plus a
     * per-landing-page breakdown.
     *
     * @return array<string, mixed>
     */
    public function report(?Carbon $from = null, ?Carbon $to = null): array
    {
        return [
            'currency' => config('fortyfive.default_currency', 'ILS'),
            'totals' => [
                'visitors' => $this->rangeVisitors($from, $to),
                'sessions' => $this->rangeSessions($from, $to),
                'orders' => $this->rangeOrders($from, $to),
                'revenue' => $this->rangeRevenue($from, $to),
                'conversion_rate' => $this->ratio($this->rangeOrders($from, $to), $this->rangeSessions($from, $to)),
                'aov' => $this->rangeAov($from, $to),
            ],
            'pages' => $this->pageBreakdown($from, $to),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pageBreakdown(?Carbon $from = null, ?Carbon $to = null): array
    {
        if (! Schema::hasTable('landing_pages')) {
            return [];
        }

        return DB::table('landing_pages')
            ->orderByDesc('id')
            ->get(['id', 'name', 'slug'])
            ->map(function ($page) use ($from, $to) {
                $sessions = $this->rangeSessions($from, $to, $page->id);
                $orders = $this->rangeOrders($from, $to, $page->id);

                return [
                    'id' => $page->id,
                    'name' => $page->name,
                    'slug' => $page->slug,
                    'visitors' => $this->rangeVisitors($from, $to, $page->id),
                    'sessions' => $sessions,
                    'orders' => $orders,
                    'revenue' => $this->rangeRevenue($from, $to, $page->id),
                    'conversion_rate' => $this->ratio($orders, $sessions),
                    'aov' => $this->rangeAov($from, $to, $page->id),
                ];
            })
            ->all();
    }

    private function rangeVisitors(?Carbon $from, ?Carbon $to, ?int $pageId = null): int
    {
        if (! Schema::hasTable('visits')) {
            return 0;
        }

        return DB::table('visits')
            ->when($pageId, fn ($q) => $q->where('landing_page_id', $pageId))
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to->copy()->endOfDay()))
            ->distinct('visitor_id')
            ->count('visitor_id');
    }

    private function rangeSessions(?Carbon $from, ?Carbon $to, ?int $pageId = null): int
    {
        if (! Schema::hasTable('visits')) {
            return 0;
        }

        return DB::table('visits')
            ->when($pageId, fn ($q) => $q->where('landing_page_id', $pageId))
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to->copy()->endOfDay()))
            ->count();
    }

    private function rangeOrders(?Carbon $from, ?Carbon $to, ?int $pageId = null): int
    {
        if (! Schema::hasTable('orders')) {
            return 0;
        }

        return DB::table('orders')
            ->when($pageId, fn ($q) => $q->where('landing_page_id', $pageId))
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to->copy()->endOfDay()))
            ->count();
    }

    private function rangeRevenue(?Carbon $from, ?Carbon $to, ?int $pageId = null): float
    {
        if (! Schema::hasTable('orders')) {
            return 0.0;
        }

        return (float) DB::table('orders')
            ->when($pageId, fn ($q) => $q->where('landing_page_id', $pageId))
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to->copy()->endOfDay()))
            ->whereNotIn('status', [OrderStatus::Cancelled->value, OrderStatus::Returned->value])
            ->sum('total');
    }

    private function rangeAov(?Carbon $from, ?Carbon $to, ?int $pageId = null): float
    {
        $revenue = $this->rangeRevenue($from, $to, $pageId);

        if (! Schema::hasTable('orders')) {
            return 0.0;
        }

        $count = DB::table('orders')
            ->when($pageId, fn ($q) => $q->where('landing_page_id', $pageId))
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to->copy()->endOfDay()))
            ->whereNotIn('status', [OrderStatus::Cancelled->value, OrderStatus::Returned->value])
            ->count();

        return $count > 0 ? round($revenue / $count, 2) : 0.0;
    }

    private function ratio(int $numerator, int $denominator): float
    {
        return $denominator > 0 ? round(($numerator / $denominator) * 100, 1) : 0.0;
    }
}
