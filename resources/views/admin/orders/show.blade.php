<x-layouts.admin :title="'#'.$order->order_number" heading="تفاصيل الطلب">
    {{-- Summary --}}
    <div class="card p-4">
        <div class="flex items-center justify-between">
            <div class="text-lg font-black text-slate-900" dir="ltr">#{{ $order->order_number }}</div>
            <x-badge :color="$order->status->color()" :label="$order->status->label()" />
        </div>
        <div class="mt-3 space-y-1 text-sm">
            <div class="flex justify-between"><span class="text-slate-400">العميل</span><span class="font-semibold">{{ $order->full_name }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">الهاتف</span><a href="tel:{{ $order->phone }}" class="font-semibold text-brand-600" dir="ltr">{{ $order->phone }}</a></div>
            <div class="flex justify-between"><span class="text-slate-400">المدينة</span><span class="font-semibold">{{ $order->city }}{{ $order->area ? ' — '.$order->area : '' }}</span></div>
            <div class="flex justify-between gap-4"><span class="text-slate-400">العنوان</span><span class="text-left font-semibold">{{ $order->address }}</span></div>
            @if ($order->notes)<div class="flex justify-between gap-4"><span class="text-slate-400">ملاحظات</span><span class="text-left">{{ $order->notes }}</span></div>@endif
        </div>
    </div>

    {{-- Items / money --}}
    <div class="card mt-3 p-4">
        <div class="flex items-center justify-between text-sm">
            <span class="text-slate-500">{{ $order->offer?->name ?? $order->product->name }} × {{ $order->quantity }}</span>
            <span class="font-semibold">{{ money($order->subtotal, $order->currency) }}</span>
        </div>
        <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2">
            <span class="font-bold text-slate-900">الإجمالي (دفع عند الاستلام)</span>
            <span class="text-xl font-black text-brand-600">{{ money($order->total, $order->currency) }}</span>
        </div>
    </div>

    {{-- Change status --}}
    @if (! empty($allowedStatuses))
        <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="card mt-3 space-y-3 p-4">
            @csrf @method('PUT')
            <h3 class="text-sm font-bold text-slate-700">تغيير الحالة</h3>
            <select name="status" class="field-input">
                @foreach ($allowedStatuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
            <input name="note" placeholder="ملاحظة (اختياري)" class="field-input">
            <button class="btn-brand w-full">تحديث الحالة</button>
        </form>
    @else
        <div class="mt-3 rounded-xl bg-slate-100 p-3 text-center text-xs text-slate-500">هذه الحالة نهائية.</div>
    @endif

    {{-- Internal note --}}
    <form method="POST" action="{{ route('admin.orders.notes', $order) }}" class="card mt-3 space-y-3 p-4">
        @csrf
        <h3 class="text-sm font-bold text-slate-700">ملاحظة داخلية</h3>
        <textarea name="note" rows="2" class="field-input" required></textarea>
        <button class="btn w-full bg-slate-900 text-white">إضافة ملاحظة</button>
    </form>

    {{-- Attribution --}}
    @if ($order->attribution)
        <div class="card mt-3 p-4">
            <h3 class="mb-2 text-sm font-bold text-slate-700">مصدر الطلب</h3>
            <div class="space-y-1 text-xs text-slate-500" dir="ltr">
                @foreach (['utm_source'=>'Source','utm_medium'=>'Medium','utm_campaign'=>'Campaign','utm_content'=>'Content','utm_term'=>'Term','fbclid'=>'fbclid','ttclid'=>'ttclid'] as $key => $lbl)
                    @if ($order->attribution->$key)
                        <div class="flex justify-between"><span>{{ $lbl }}</span><span class="font-semibold text-slate-700">{{ $order->attribution->$key }}</span></div>
                    @endif
                @endforeach
                @if ($order->attribution->referrer)<div class="truncate">Referrer: {{ $order->attribution->referrer }}</div>@endif
            </div>
        </div>
    @endif

    {{-- History --}}
    <div class="card mt-3 p-4">
        <h3 class="mb-3 text-sm font-bold text-slate-700">سجل الحالة</h3>
        <div class="space-y-3">
            @foreach ($order->statusHistories as $h)
                <div class="flex gap-3">
                    <div class="mt-1 h-2 w-2 flex-none rounded-full bg-brand-500"></div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-slate-800">
                            @if ($h->from_status && $h->from_status !== $h->to_status)
                                {{ $h->from_status->label() }} ← {{ $h->to_status->label() }}
                            @elseif ($h->from_status === $h->to_status && $h->note)
                                ملاحظة
                            @else
                                {{ $h->to_status->label() }}
                            @endif
                        </div>
                        @if ($h->note)<div class="text-xs text-slate-500">{{ $h->note }}</div>@endif
                        <div class="text-[11px] text-slate-400">{{ $h->user?->name ?? 'النظام' }} · {{ $h->created_at?->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
