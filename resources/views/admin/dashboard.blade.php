<x-layouts.admin title="الرئيسية" heading="مرحبًا 👋">
    @php($c = $summary['currency'] ?? 'ILS')
    <div class="grid grid-cols-2 gap-3">
        <x-stat-card label="طلبات اليوم" :value="number_format($summary['orders_today'])" tone="brand" />
        <x-stat-card label="مبيعات اليوم" :value="money($summary['revenue_today'], $c)" tone="green" />
        <x-stat-card label="زيارات اليوم" :value="number_format($summary['visitors_today'])" tone="slate" />
        <x-stat-card label="نسبة التحويل" :value="$summary['conversion_rate'].'%'" tone="plain" />
    </div>

    <div class="mt-3 grid grid-cols-3 gap-3">
        <x-stat-card label="طلبات الشهر" :value="number_format($summary['orders_month'])" tone="plain" />
        <x-stat-card label="مبيعات الشهر" :value="money($summary['revenue_month'], $c)" tone="plain" />
        <x-stat-card label="متوسط الطلب" :value="money($summary['aov'], $c)" tone="plain" />
    </div>

    @if (Route::has('admin.analytics.index') && auth()->user()->can(\App\Enums\Permission::ViewAnalytics->value))
        <a href="{{ route('admin.analytics.index') }}" class="mt-3 flex items-center justify-between rounded-card bg-slate-900 px-4 py-3 text-white">
            <span class="text-sm font-bold">📊 التحليلات الكاملة (مع فلتر التاريخ)</span>
            <span>←</span>
        </a>
    @endif

    {{-- Recent orders --}}
    <div class="mt-6">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">آخر الطلبات</h2>
            @if (Route::has('admin.orders.index'))
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-brand-600">عرض الكل</a>
            @endif
        </div>
        @forelse ($summary['recent_orders'] as $order)
            <div class="card mb-2 flex items-center justify-between p-3">
                <div>
                    <div class="text-sm font-bold text-slate-900">#{{ $order->order_number }} — {{ $order->full_name }}</div>
                    <div class="text-xs text-slate-400">{{ \Illuminate\Support\Carbon::parse($order->created_at)->diffForHumans() }}</div>
                </div>
                <div class="text-left">
                    <div class="text-sm font-black text-slate-900">{{ money($order->total, $order->currency) }}</div>
                    <x-badge :color="\App\Enums\OrderStatus::from($order->status)->color()" :label="\App\Enums\OrderStatus::from($order->status)->label()" />
                </div>
            </div>
        @empty
            <x-empty-state title="لا توجد طلبات بعد" subtitle="ستظهر الطلبات هنا فور وصولها من صفحات البيع." />
        @endforelse
    </div>

    {{-- Top pages --}}
    @if (! empty($summary['top_pages']))
        <div class="mt-6">
            <h2 class="mb-2 text-base font-bold text-slate-900">أفضل الصفحات</h2>
            @foreach ($summary['top_pages'] as $page)
                <div class="card mb-2 flex items-center justify-between p-3">
                    <div class="text-sm font-semibold text-slate-800">{{ $page->name }}</div>
                    <span class="text-sm font-black text-brand-600">{{ number_format($page->orders_count) }} طلب</span>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
