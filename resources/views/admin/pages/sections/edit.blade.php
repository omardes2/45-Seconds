<x-layouts.admin :title="$section->type->label()" heading="{{ $section->type->label() }}">
    <form method="POST" action="{{ route('admin.pages.sections.update', [$page, $section]) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf @method('PUT')
        <div class="card p-4">
            @include('admin.pages.sections._fields', ['section' => $section])
        </div>
        <button type="submit" class="btn-brand w-full">حفظ القسم</button>
    </form>
</x-layouts.admin>
