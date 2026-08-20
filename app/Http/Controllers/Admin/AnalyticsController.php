<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = ! empty($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $to = ! empty($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : Carbon::now()->endOfDay();

        return view('admin.analytics.index', [
            'report' => $this->analytics->report($from, $to),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }
}
