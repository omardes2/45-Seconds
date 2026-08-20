<x-layouts.admin title="المستخدمون" heading="المستخدمون">
    <x-slot:actions>
        <a href="{{ route('admin.users.create') }}" class="btn-brand !px-3 !py-2 text-sm">＋ جديد</a>
    </x-slot:actions>

    @forelse ($users as $user)
        <div class="card mb-2 flex items-center justify-between p-3">
            <div>
                <div class="text-sm font-bold text-slate-900">{{ $user->name }}</div>
                <div class="text-xs text-slate-400" dir="ltr">{{ $user->email }}</div>
                <div class="mt-1 flex flex-wrap gap-1">
                    @foreach ($user->roles as $role)
                        <x-badge color="indigo" :label="$role->display_name" />
                    @endforeach
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">تعديل</a>
            </div>
        </div>
    @empty
        <x-empty-state title="لا يوجد مستخدمون" />
    @endforelse

    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.admin>
