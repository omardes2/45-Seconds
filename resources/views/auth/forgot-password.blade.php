<x-layouts.guest>
    <p class="mb-4 text-center text-sm text-slate-500">أدخل بريدك وسنرسل لك رابط إعادة تعيين كلمة المرور.</p>
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="field-label">البريد الإلكتروني</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="field-input" dir="ltr">
            @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="btn-brand w-full">إرسال الرابط</button>
        <div class="text-center">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-brand-600">العودة للدخول</a>
        </div>
    </form>
</x-layouts.guest>
