<x-layouts.admin title="العروض" heading="العروض">
    <x-slot:actions>
        <a href="{{ route('admin.pages.builder', $page) }}" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">← المحرر</a>
    </x-slot:actions>

    @php($currency = $page->product->currency)
    @forelse ($offers as $offer)
        <div class="card mb-2 flex items-center justify-between p-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-900">{{ $offer->name }}</span>
                    @if ($offer->is_default)<x-badge color="emerald" label="افتراضي" />@endif
                    @if (! $offer->is_active)<x-badge color="zinc" label="معطّل" />@endif
                    @if ($offer->badge_text)<x-badge color="violet" :label="$offer->badge_text" />@endif
                </div>
                <div class="mt-0.5 text-xs text-slate-400">{{ $offer->quantity }} قطعة · {{ money($offer->price, $currency) }}</div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.pages.offers.edit', [$page, $offer]) }}" class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">تعديل</a>
                <form method="POST" action="{{ route('admin.pages.offers.destroy', [$page, $offer]) }}" onsubmit="return confirm('حذف العرض؟')">
                    @csrf @method('DELETE')
                    <button class="rounded-xl bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600">حذف</button>
                </form>
            </div>
        </div>
    @empty
        <x-empty-state title="لا توجد عروض" subtitle="أضف عرضًا واحدًا على الأقل لتتمكن من النشر." />
    @endforelse

    <div class="card mt-4 p-4">
        <h3 class="mb-3 text-sm font-bold text-slate-700">إضافة عرض</h3>
        <form method="POST" action="{{ route('admin.pages.offers.store', $page) }}" class="space-y-3">
            @csrf
            @include('admin.offers._fields', ['offer' => null])
            <button class="btn-brand w-full">إضافة العرض</button>
        </form>
    </div>
</x-layouts.admin>
