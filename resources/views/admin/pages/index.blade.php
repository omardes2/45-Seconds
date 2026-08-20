<x-layouts.admin title="الصفحات" heading="صفحات البيع">
    <x-slot:actions>
        <a href="{{ route('admin.pages.create') }}" class="btn-brand !px-3 !py-2 text-sm">＋ صفحة</a>
    </x-slot:actions>

    <div class="lg:grid lg:grid-cols-2 lg:gap-4">
    @forelse ($pages as $page)
        <div class="card mb-3 p-4 lg:mb-0">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <div class="truncate text-sm font-bold text-slate-900">{{ $page->name }}</div>
                    <div class="text-xs text-slate-400">{{ $page->product->name }} · <span dir="ltr">/p/{{ $page->slug }}</span></div>
                </div>
                <x-badge :color="$page->status->color()" :label="$page->status->label()" />
            </div>

            @php($st = $stats[$page->id] ?? null)
            <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                <div class="rounded-xl bg-slate-50 py-2">
                    <div class="text-sm font-black text-slate-900">{{ number_format($page->orders_count) }}</div>
                    <div class="text-[10px] text-slate-400">طلبات</div>
                </div>
                <div class="rounded-xl bg-slate-50 py-2">
                    <div class="text-sm font-black text-slate-900">{{ number_format($st['sessions'] ?? 0) }}</div>
                    <div class="text-[10px] text-slate-400">زيارات</div>
                </div>
                <div class="rounded-xl bg-slate-50 py-2">
                    <div class="text-sm font-black text-slate-900">{{ $st['conversion_rate'] ?? '0' }}%</div>
                    <div class="text-[10px] text-slate-400">تحويل</div>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('admin.pages.builder', $page) }}" class="flex-1 rounded-xl bg-brand-600 py-2 text-center text-xs font-bold text-white">تحرير</a>
                <a href="{{ route('admin.pages.preview', $page) }}" target="_blank" class="flex-1 rounded-xl bg-slate-100 py-2 text-center text-xs font-bold text-slate-600">معاينة</a>
                @if ($page->status !== \App\Enums\PageStatus::Published)
                    <form method="POST" action="{{ route('admin.pages.publish', $page) }}" class="flex-1">
                        @csrf
                        <button class="w-full rounded-xl bg-emerald-500 py-2 text-xs font-bold text-white">نشر</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.pages.pause', $page) }}" class="flex-1">
                        @csrf
                        <button class="w-full rounded-xl bg-amber-500 py-2 text-xs font-bold text-white">إيقاف</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="lg:col-span-2">
            <x-empty-state title="لا توجد صفحات بيع" subtitle="أنشئ صفحتك الأولى بأسلوب 45 ثانية.">
                <a href="{{ route('admin.pages.create') }}" class="btn-brand">＋ صفحة جديدة</a>
            </x-empty-state>
        </div>
    @endforelse
    </div>

    <div class="mt-4">{{ $pages->links() }}</div>
</x-layouts.admin>
