<x-layouts.admin title="صفحة جديدة" heading="صفحة بيع جديدة">
    @if ($products->isEmpty())
        <x-empty-state title="لا يوجد منتج نشط" subtitle="أنشئ منتجًا نشطًا أولًا لبناء صفحة بيع له.">
            <a href="{{ route('admin.products.create') }}" class="btn-brand">＋ منتج جديد</a>
        </x-empty-state>
    @else
        <form method="POST" action="{{ route('admin.pages.store') }}" class="space-y-4">
            @csrf
            <div class="card p-4 space-y-4">
                <div>
                    <label class="field-label">المنتج</label>
                    <select name="product_id" class="field-input" required>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">اسم الصفحة (داخلي)</label>
                    <input name="name" value="{{ old('name') }}" class="field-input" placeholder="مثال: حملة ضوء النوم - أكتوبر" required>
                    @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <p class="px-1 text-xs text-slate-400">سيتم إنشاء أقسام رحلة الـ45 ثانية تلقائيًا، ويمكنك تعبئتها خطوة بخطوة.</p>
            <button type="submit" class="btn-brand w-full">إنشاء الصفحة</button>
        </form>
    @endif
</x-layouts.admin>
