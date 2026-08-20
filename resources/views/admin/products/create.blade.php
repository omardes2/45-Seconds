<x-layouts.admin title="منتج جديد" heading="منتج جديد" :narrow="true">
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @include('admin.products._form', ['product' => null])
        <button type="submit" class="btn-brand w-full">حفظ المنتج</button>
    </form>
</x-layouts.admin>
