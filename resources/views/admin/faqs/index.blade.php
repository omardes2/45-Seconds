<x-layouts.admin title="الأسئلة الشائعة" heading="الأسئلة الشائعة">
    <x-slot:actions>
        <a href="{{ route('admin.pages.builder', $page) }}" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">← المحرر</a>
    </x-slot:actions>

    @forelse ($faqs as $faq)
        <div class="card mb-2 flex items-center justify-between p-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="truncate text-sm font-bold text-slate-900">{{ $faq->question }}</span>
                    @if (! $faq->is_active)<x-badge color="zinc" label="مخفي" />@endif
                </div>
                <div class="mt-0.5 truncate text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($faq->answer, 50) }}</div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.pages.faqs.edit', [$page, $faq]) }}" class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">تعديل</a>
                <form method="POST" action="{{ route('admin.pages.faqs.destroy', [$page, $faq]) }}" onsubmit="return confirm('حذف؟')">
                    @csrf @method('DELETE')
                    <button class="rounded-xl bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600">حذف</button>
                </form>
            </div>
        </div>
    @empty
        <x-empty-state title="لا توجد أسئلة شائعة" />
    @endforelse

    <div class="card mt-4 p-4">
        <h3 class="mb-3 text-sm font-bold text-slate-700">إضافة سؤال</h3>
        <form method="POST" action="{{ route('admin.pages.faqs.store', $page) }}" class="space-y-3">
            @csrf
            @include('admin.faqs._fields', ['faq' => null])
            <button class="btn-brand w-full">إضافة</button>
        </form>
    </div>
</x-layouts.admin>
