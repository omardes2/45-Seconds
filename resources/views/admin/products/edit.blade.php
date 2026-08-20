<x-layouts.admin title="تعديل المنتج" heading="تعديل المنتج" :narrow="true">
    <x-slot:actions>
        @if ($product->landingPages()->exists())
            <a href="{{ route('admin.pages.index') }}" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">الصفحات ({{ $product->landingPages()->count() }})</a>
        @endif
    </x-slot:actions>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')
        @include('admin.products._form', ['product' => $product])
        <button type="submit" class="btn-brand w-full">حفظ التغييرات</button>
    </form>

    {{-- Gallery management --}}
    @if ($product->media->isNotEmpty())
        <div class="mt-6">
            <h3 class="mb-2 text-sm font-bold text-slate-700">الوسائط</h3>
            <div class="grid grid-cols-3 gap-2">
                @foreach ($product->media as $m)
                    <div class="relative overflow-hidden rounded-xl bg-slate-100">
                        @if ($m->type === \App\Enums\MediaType::Image && $m->resolvedUrl())
                            <img src="{{ $m->resolvedUrl() }}" class="aspect-square w-full object-cover" alt="">
                        @else
                            <div class="grid aspect-square place-items-center text-[11px] text-slate-400">فيديو</div>
                        @endif
                        <form method="POST" action="{{ route('admin.products.media.destroy', [$product, $m->id]) }}"
                              onsubmit="return confirm('حذف هذا الوسيط؟')" class="absolute left-1 top-1">
                            @csrf @method('DELETE')
                            <button class="grid h-6 w-6 place-items-center rounded-full bg-black/60 text-xs text-white">✕</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="mt-6"
          onsubmit="return confirm('أرشفة هذا المنتج؟')">
        @csrf @method('DELETE')
        <button type="submit" class="btn w-full bg-rose-50 text-rose-600">أرشفة المنتج</button>
    </form>
</x-layouts.admin>
