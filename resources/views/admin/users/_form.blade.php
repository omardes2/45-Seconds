@php($selectedRoles = old('roles', isset($user) && $user ? $user->roles->pluck('id')->all() : []))
<div>
    <label class="field-label">الاسم</label>
    <input name="name" value="{{ old('name', $user->name ?? '') }}" class="field-input" required>
    @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="field-label">البريد الإلكتروني</label>
    <input name="email" type="email" dir="ltr" value="{{ old('email', $user->email ?? '') }}" class="field-input" required>
    @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="field-label">كلمة المرور {{ isset($user) && $user ? '(اتركها فارغة لعدم التغيير)' : '' }}</label>
    <input name="password" type="password" dir="ltr" class="field-input" {{ isset($user) && $user ? '' : 'required' }}>
    @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="field-label">تأكيد كلمة المرور</label>
    <input name="password_confirmation" type="password" dir="ltr" class="field-input">
</div>
<div>
    <label class="field-label">الأدوار</label>
    <div class="space-y-2">
        @foreach ($roles as $role)
            <label class="flex items-center gap-3 rounded-xl bg-white p-3 ring-1 ring-slate-100">
                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                       @checked(in_array($role->id, $selectedRoles))
                       class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-semibold text-slate-700">{{ $role->display_name }}</span>
            </label>
        @endforeach
    </div>
    @error('roles') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
</div>
