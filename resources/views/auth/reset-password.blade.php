<x-layouts.guest>
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label for="email" class="field-label">البريد الإلكتروني</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required
                   class="field-input" dir="ltr">
            @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="field-label">كلمة المرور الجديدة</label>
            <input id="password" name="password" type="password" required class="field-input" dir="ltr">
            @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="field-label">تأكيد كلمة المرور</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="field-input" dir="ltr">
        </div>
        <button type="submit" class="btn-brand w-full">تعيين كلمة المرور</button>
    </form>
</x-layouts.guest>
