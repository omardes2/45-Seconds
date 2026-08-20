<x-layouts.admin title="تعديل السؤال" heading="تعديل السؤال">
    <form method="POST" action="{{ route('admin.pages.faqs.update', [$page, $faq]) }}" class="card space-y-3 p-4">
        @csrf @method('PUT')
        @include('admin.faqs._fields', ['faq' => $faq])
        <button class="btn-brand w-full">حفظ</button>
    </form>
    <a href="{{ route('admin.pages.faqs.index', $page) }}" class="mt-3 block text-center text-sm font-semibold text-slate-500">← رجوع</a>
</x-layouts.admin>
