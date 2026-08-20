<x-layouts.admin title="تعديل مستخدم" heading="تعديل مستخدم">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['user' => $user])
        <button type="submit" class="btn-brand w-full">حفظ التغييرات</button>
    </form>

    @unless ($user->is(auth()->user()))
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-3"
              onsubmit="return confirm('حذف هذا المستخدم؟')">
            @csrf @method('DELETE')
            <button type="submit" class="btn w-full bg-rose-50 text-rose-600">حذف المستخدم</button>
        </form>
    @endunless
</x-layouts.admin>
