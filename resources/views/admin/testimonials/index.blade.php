<x-layouts.admin title="آراء العملاء" heading="آراء العملاء">
    <x-slot:actions>
        <a href="{{ route('admin.pages.builder', $page) }}" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">← المحرر</a>
    </x-slot:actions>

    <p class="mb-3 rounded-xl bg-amber-50 p-3 text-xs text-amber-700">أدخل آراء حقيقية فقط. لا يُنشئ النظام أي مراجعات تلقائيًا، ويظهر للزوار فقط الآراء المفعّلة.</p>

    @forelse ($testimonials as $t)
        <div class="card mb-2 flex items-center justify-between p-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-900">{{ $t->customer_name }}</span>
                    <span class="text-amber-400 text-xs">{!! str_repeat('★', $t->rating) !!}</span>
                    @if ($t->is_verified)<x-badge color="emerald" label="موثّق" />@endif
                    @if (! $t->is_active)<x-badge color="zinc" label="مخفي" />@endif
                </div>
                <div class="mt-0.5 truncate text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($t->text, 50) }}</div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.pages.testimonials.edit', [$page, $t]) }}" class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">تعديل</a>
                <form method="POST" action="{{ route('admin.pages.testimonials.destroy', [$page, $t]) }}" onsubmit="return confirm('حذف؟')">
                    @csrf @method('DELETE')
                    <button class="rounded-xl bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600">حذف</button>
                </form>
            </div>
        </div>
    @empty
        <x-empty-state title="لا توجد آراء بعد" />
    @endforelse

    <div class="card mt-4 p-4">
        <h3 class="mb-3 text-sm font-bold text-slate-700">إضافة رأي</h3>
        <form method="POST" action="{{ route('admin.pages.testimonials.store', $page) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            @include('admin.testimonials._fields', ['testimonial' => null])
            <button class="btn-brand w-full">إضافة</button>
        </form>
    </div>
</x-layouts.admin>
