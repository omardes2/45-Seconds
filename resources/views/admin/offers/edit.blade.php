<x-layouts.admin title="تعديل العرض" heading="تعديل العرض">
    <form method="POST" action="{{ route('admin.pages.offers.update', [$page, $offer]) }}" class="card space-y-3 p-4">
        @csrf @method('PUT')
        @include('admin.offers._fields', ['offer' => $offer])
        <button class="btn-brand w-full">حفظ</button>
    </form>
    <a href="{{ route('admin.pages.offers.index', $page) }}" class="mt-3 block text-center text-sm font-semibold text-slate-500">← رجوع للعروض</a>
</x-layouts.admin>
