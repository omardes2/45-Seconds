<div class="grid grid-cols-2 gap-2">
    <div>
        <label class="field-label">اسم العميل</label>
        <input name="customer_name" value="{{ old('customer_name', $testimonial->customer_name ?? '') }}" class="field-input" required>
        @error('customer_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label">التقييم</label>
        <select name="rating" class="field-input">
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" @selected(old('rating', $testimonial->rating ?? 5) == $i)>{{ str_repeat('★', $i) }}</option>
            @endfor
        </select>
    </div>
</div>
<div>
    <label class="field-label">النص</label>
    <textarea name="text" rows="3" class="field-input" required>{{ old('text', $testimonial->text ?? '') }}</textarea>
    @error('text') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="field-label">صورة العميل (اختياري)</label>
    @if (! empty($testimonial?->customer_image) && $testimonial->imageUrl())
        <img src="{{ $testimonial->imageUrl() }}" class="mb-2 h-12 w-12 rounded-full object-cover" alt="">
    @endif
    <input name="customer_image" type="file" accept="image/*" class="field-input">
    @error('customer_image') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="field-label">رابط فيديو (اختياري)</label>
    <input name="video_url" type="url" dir="ltr" value="{{ old('video_url', $testimonial->video_url ?? '') }}" class="field-input">
</div>
<div class="flex items-center gap-4">
    <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $testimonial->is_verified ?? false)) class="rounded border-slate-300 text-brand-600">
        موثّق
    </label>
    <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $testimonial->is_active ?? true)) class="rounded border-slate-300 text-brand-600">
        ظاهر للزوار
    </label>
</div>
