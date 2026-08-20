<x-layouts.admin title="الإعدادات" heading="الإعدادات العامة">
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="card p-4 space-y-4">
            <div>
                <label class="field-label">اسم الموقع</label>
                <input name="site_name" value="{{ old('site_name', $general['site_name'] ?? config('app.name')) }}" class="field-input" required>
                @error('site_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="field-label">العملة الافتراضية</label>
                <select name="default_currency" class="field-input">
                    @foreach ($currencies as $value => $label)
                        <option value="{{ $value }}" @selected(($general['default_currency'] ?? config('fortyfive.default_currency')) === $value)>{{ $label }} ({{ $value }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="field-label">المدينة الافتراضية (اختياري)</label>
                <input name="default_city" value="{{ old('default_city', $general['default_city'] ?? '') }}" class="field-input">
            </div>

            <div>
                <label class="field-label">شعار الموقع (اختياري)</label>
                @if (! empty($general['logo']))
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($general['logo']) }}" class="mb-2 h-16 rounded-xl object-contain" alt="الشعار">
                @endif
                <input name="logo" type="file" accept="image/*" class="field-input">
                @error('logo') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" class="btn-brand w-full">حفظ الإعدادات</button>
    </form>
</x-layouts.admin>
