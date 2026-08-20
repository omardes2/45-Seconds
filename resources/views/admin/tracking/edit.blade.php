<x-layouts.admin title="التتبع" heading="تكاملات التتبع">
    <form method="POST" action="{{ route('admin.tracking.update') }}" class="space-y-5" x-data>
        @csrf
        @method('PUT')

        {{-- Meta / Facebook --}}
        <div class="card p-4 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Meta (Facebook / Instagram)</h2>
                <label class="inline-flex cursor-pointer items-center gap-2">
                    <input type="checkbox" name="meta_enabled" value="1" @checked($meta['enabled'] ?? false)
                           class="peer sr-only">
                    <span class="relative h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500 after:absolute after:top-0.5 after:right-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition peer-checked:after:-translate-x-5"></span>
                </label>
            </div>
            <div>
                <label class="field-label">Pixel ID</label>
                <input name="meta_pixel_id" dir="ltr" value="{{ old('meta_pixel_id', $meta['pixel_id'] ?? '') }}"
                       inputmode="numeric" placeholder="123456789012345" class="field-input">
                @error('meta_pixel_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <details class="rounded-xl bg-slate-50 p-3">
                <summary class="cursor-pointer text-sm font-semibold text-slate-600">Conversions API (اختياري — خادمي)</summary>
                <div class="mt-3 space-y-3">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="meta_capi_enabled" value="1" @checked($meta['capi_enabled'] ?? false)
                               class="rounded border-slate-300 text-brand-600">
                        تفعيل CAPI الخادمي
                    </label>
                    <div>
                        <label class="field-label">Access Token</label>
                        <input name="meta_access_token" dir="ltr" type="password" placeholder="{{ ! empty($meta['access_token']) ? '•••••• محفوظ' : '' }}" class="field-input">
                        <p class="mt-1 text-[11px] text-slate-400">يُخزَّن مشفَّرًا. اتركه فارغًا للإبقاء على الحالي.</p>
                    </div>
                    <div>
                        <label class="field-label">Test Event Code (اختياري)</label>
                        <input name="meta_test_event_code" dir="ltr" value="{{ old('meta_test_event_code', $meta['test_event_code'] ?? '') }}" class="field-input">
                    </div>
                </div>
            </details>
        </div>

        {{-- TikTok --}}
        <div class="card p-4 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">TikTok</h2>
                <label class="inline-flex cursor-pointer items-center gap-2">
                    <input type="checkbox" name="tiktok_enabled" value="1" @checked($tiktok['enabled'] ?? false)
                           class="peer sr-only">
                    <span class="relative h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500 after:absolute after:top-0.5 after:right-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition peer-checked:after:-translate-x-5"></span>
                </label>
            </div>
            <div>
                <label class="field-label">Pixel ID</label>
                <input name="tiktok_pixel_id" dir="ltr" value="{{ old('tiktok_pixel_id', $tiktok['pixel_id'] ?? '') }}"
                       placeholder="CXXXXXXXXXXXXXXXXX" class="field-input">
            </div>
            <details class="rounded-xl bg-slate-50 p-3">
                <summary class="cursor-pointer text-sm font-semibold text-slate-600">Events API (اختياري — خادمي)</summary>
                <div class="mt-3 space-y-3">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="tiktok_capi_enabled" value="1" @checked($tiktok['capi_enabled'] ?? false)
                               class="rounded border-slate-300 text-brand-600">
                        تفعيل Events API الخادمي
                    </label>
                    <div>
                        <label class="field-label">Access Token</label>
                        <input name="tiktok_access_token" dir="ltr" type="password" placeholder="{{ ! empty($tiktok['access_token']) ? '•••••• محفوظ' : '' }}" class="field-input">
                        <p class="mt-1 text-[11px] text-slate-400">يُخزَّن مشفَّرًا. اتركه فارغًا للإبقاء على الحالي.</p>
                    </div>
                </div>
            </details>
        </div>

        <button type="submit" class="btn-brand w-full">حفظ إعدادات التتبع</button>
    </form>
</x-layouts.admin>
