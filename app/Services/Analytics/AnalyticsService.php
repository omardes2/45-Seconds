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
}
