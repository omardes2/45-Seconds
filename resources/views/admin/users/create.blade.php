<x-layouts.admin title="مستخدم جديد" heading="مستخدم جديد">
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
        @csrf
        @include('admin.users._form', ['user' => null])
        <button type="submit" class="btn-brand w-full">إنشاء المستخدم</button>
    </form>
</x-layouts.admin>
