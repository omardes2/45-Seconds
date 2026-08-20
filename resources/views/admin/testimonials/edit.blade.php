<x-layouts.admin title="تعديل الرأي" heading="تعديل الرأي">
    <form method="POST" action="{{ route('admin.pages.testimonials.update', [$page, $testimonial]) }}" enctype="multipart/form-data" class="card space-y-3 p-4">
        @csrf @method('PUT')
        @include('admin.testimonials._fields', ['testimonial' => $testimonial])
        <button class="btn-brand w-full">حفظ</button>
    </form>
    <a href="{{ route('admin.pages.testimonials.index', $page) }}" class="mt-3 block text-center text-sm font-semibold text-slate-500">← رجوع</a>
</x-layouts.admin>
