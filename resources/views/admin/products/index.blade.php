<x-layouts.admin title="المنتجات" heading="المنتجات">
    <x-slot:actions>
        <a href="{{ route('admin.products.create') }}" class="btn-brand !px-3 !py-2 text-sm">＋ منتج</a>
    </x-slot:actions>

    @forelse ($products as $product)
        <a href="{{ route('admin.products.edit', $product) }}" class="card mb-2 flex items-center gap-3 p-3">
            <div class="h-14 w-14 flex-none overflow-hidden rounded-xl bg-slate-100">
                @if ($product->mainImageUrl())
                    <img src="{{ $product->mainImageUrl() }}" class="h-full w-full object-cover" alt="{{ $product->name }}">
                @else
                    <div class="grid h-full w-full place-items-center text-slate-300">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-6 w-6"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="m3 15 5-4 4 3 3-2 6 5"/></svg>
                    </div>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <div class="truncate text-sm font-bold text-slate-900">{{ $product->name }}</div>
                <div class="text-xs text-slate-400">{{ money($product->base_price, $product->currency) }} · {{ $product->landing_pages_count }} صفحة</div>
            </div>
            <x-badge :color="$product->status->color()" :label="$product->status->label()" />
        </a>
    @empty
        <x-empty-state title="لا توجد منتجات" subtitle="أنشئ منتجك الأول لبدء بناء صفحة بيع.">
            <a href="{{ route('admin.products.create') }}" class="btn-brand">＋ منتج جديد</a>
        </x-empty-state>
    @endforelse

    <div class="mt-4">{{ $products->links() }}</div>
</x-layouts.admin>
