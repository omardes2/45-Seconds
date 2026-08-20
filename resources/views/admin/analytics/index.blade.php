<x-layouts.admin title="التحليلات" heading="التحليلات">
    @php($c = $report['currency'])
    <form method="GET" action="{{ route('admin.analytics.index') }}" class="card mb-4 p-4">
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="field-label">من</label>
                <input type="date" name="from" value="{{ $from }}" class="field-input" dir="ltr">
            </div>
            <div>
                <label class="field-label">إلى</label>
                <input type="date" name="to" value="{{ $to }}" class="field-input" dir="ltr">
            </div>
        </div>
        @error('to') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        <button class="btn-brand mt-3 w-full">تطبيق</button>
    </form>

    <div class="grid grid-cols-2 gap-3">
        <x-stat-card label="الزوار" :value="number_format($report['totals']['visitors'])" tone="brand" />
        <x-stat-card label="الجلسات" :value="number_format($report['totals']['sessions'])" tone="slate" />
        <x-stat-card label="الطلبات" :value="number_format($report['totals']['orders'])" tone="plain" />
        <x-stat-card label="نسبة التحويل" :value="$report['totals']['conversion_rate'].'%'" tone="plain" />
        <x-stat-card label="الإيرادات" :value="money($report['totals']['revenue'], $c)" tone="green" />
        <x-stat-card label="متوسط الطلب" :value="money($report['totals']['aov'], $c)" tone="plain" />
    </div>

    <h2 class="mb-2 mt-6 text-base font-bold text-slate-900">أداء الصفحات</h2>
    @forelse ($report['pages'] as $page)
        <div class="card mb-2 p-4">
            <div class="flex items-center justify-between">
                <div class="min-w-0 truncate text-sm font-bold text-slate-900">{{ $page['name'] }}</div>
                <span class="flex-none text-sm font-black text-brand-600">{{ $page['conversion_rate'] }}%</span>
            </div>
            <div class="mt-2 grid grid-cols-4 gap-2 text-center">
                <div class="rounded-lg bg-slate-50 py-1.5">
                    <div class="text-sm font-black text-slate-900">{{ number_format($page['visitors']) }}</div>
                    <div class="text-[10px] text-slate-400">زوار</div>
                </div>
                <div class="rounded-lg bg-slate-50 py-1.5">
                    <div class="text-sm font-black text-slate-900">{{ number_format($page['sessions']) }}</div>
                    <div class="text-[10px] text-slate-400">جلسات</div>
                </div>
                <div class="rounded-lg bg-slate-50 py-1.5">
                    <div class="text-sm font-black text-slate-900">{{ number_format($page['orders']) }}</div>
                    <div class="text-[10px] text-slate-400">طلبات</div>
                </div>
                <div class="rounded-lg bg-slate-50 py-1.5">
                    <div class="text-xs font-black text-slate-900">{{ money($page['revenue'], $c) }}</div>
                    <div class="text-[10px] text-slate-400">إيراد</div>
                </div>
            </div>
        </div>
    @empty
        <x-empty-state title="لا توجد بيانات في هذه الفترة" />
    @endforelse
</x-layouts.admin>
