<x-layouts.guest>
    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="field-label">البريد الإلكتروني</label>
            <input id="email" name="email" type="email" inputmode="email" autocomplete="username"
                   value="{{ old('email') }}" required autofocus
                   class="field-input" dir="ltr">
            @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="field-label">كلمة المرور</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                   class="field-input" dir="ltr">
            @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            تذكرني
        </label>

        <button type="submit" class="btn-brand w-full">تسجيل الدخول</button>

        <div class="text-center">
            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-brand-600">نسيت كلمة المرور؟</a>
        </div>
    </form>
</x-layouts.guest>
