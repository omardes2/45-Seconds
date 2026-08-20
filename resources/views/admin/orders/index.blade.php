<x-layouts.admin title="الطلبات" heading="الطلبات">
    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.orders.index') }}" class="card mb-4 space-y-3 p-4"
          x-data="{ open: {{ collect($filters)->filter()->isNotEmpty() ? 'true' : 'false' }} }">
        <div class="flex gap-2">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث بالرقم أو الهاتف أو الاسم"
                   class="field-input flex-1" inputmode="search">
            <button class="btn-brand !px-4">بحث</button>
        </div>
        <button type="button" @click="open = !open" class="text-xs font-semibold text-brand-600">فلاتر متقدمة ▾</button>
        <div x-show="open" x-cloak class="space-y-3">
            <select name="status" class="field-input">
                <option value="">كل الحالات</option>
                @foreach ($statuses as $val => $label)
                    <option value="{{ $val }}" @selected(($filters['status'] ?? '') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="page_id" class="field-input">
                <option value="">كل الصفحات</option>
                @foreach ($pages as $p)
                    <option value="{{ $p->id }}" @selected((string)($filters['page_id'] ?? '') === (string)$p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <div class="grid grid-cols-2 gap-2">
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="field-input" dir="ltr">
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="field-input" dir="ltr">
            </div>
            <div class="flex gap-2">
                <button class="btn-brand flex-1">تطبيق</button>
                <a href="{{ route('admin.orders.index') }}" class="btn flex-1 bg-slate-100 text-slate-600">مسح</a>
            </div>
        </div>
    </form>

    @forelse ($orders as $order)
        <a href="{{ route('admin.orders.show', $order) }}" class="card mb-2 block p-4">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-sm font-bold text-slate-900">#{{ $order->order_number }} — {{ $order->full_name }}</div>
                    <div class="text-xs text-slate-400" dir="ltr">{{ $order->phone }}</div>
                    <div class="mt-0.5 text-[11px] text-slate-400">{{ $order->landingPage?->name }} · {{ $order->created_at->diffForHumans() }}</div>
                </div>
                <div class="text-left">
                    <div class="text-sm font-black text-slate-900">{{ money($order->total, $order->currency) }}</div>
                    <x-badge :color="$order->status->color()" :label="$order->status->label()" class="mt-1" />
                </div>
            </div>
        </a>
    @empty
        <x-empty-state title="لا توجد طلبات" subtitle="ستظهر الطلبات هنا فور وصولها." />
    @endforelse

    <div class="mt-4">{{ $orders->links() }}</div>
</x-layouts.admin>
