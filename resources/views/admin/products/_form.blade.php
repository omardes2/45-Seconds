<div class="card p-4 space-y-4">
    <div>
        <label class="field-label">اسم المنتج</label>
        <input name="name" value="{{ old('name', $product->name ?? '') }}" class="field-input" required>
        @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="field-label">الوصف (اختياري)</label>
        <textarea name="description" rows="3" class="field-input">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="field-label">السعر الأساسي</label>
            <input name="base_price" type="number" step="0.01" min="0" dir="ltr"
                   value="{{ old('base_price', $product->base_price ?? '') }}" class="field-input" required>
            @error('base_price') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="field-label">قبل الخصم (اختياري)</label>
            <input name="compare_at_price" type="number" step="0.01" min="0" dir="ltr"
                   value="{{ old('compare_at_price', $product->compare_at_price ?? '') }}" class="field-input">
            @error('compare_at_price') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="field-label">العملة</label>
            <select name="currency" class="field-input">
                @foreach ($currencies as $value => $label)
                    <option value="{{ $value }}" @selected(old('currency', $product->currency->value ?? config('fortyfive.default_currency')) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">الحالة</label>
            <select name="status" class="field-input">
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $product->status->value ?? 'draft') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="field-label">SKU (اختياري)</label>
            <input name="sku" dir="ltr" value="{{ old('sku', $product->sku ?? '') }}" class="field-input">
        </div>
        <div>
            <label class="field-label">الرابط (Slug)</label>
            <input name="slug" dir="ltr" value="{{ old('slug', $product->slug ?? '') }}" placeholder="تلقائي" class="field-input">
            @error('slug') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="card p-4 space-y-4">
    <div>
        <label class="field-label">الصورة الرئيسية</label>
        @if (! empty($product?->main_image) && $product->mainImageUrl())
            <img src="{{ $product->mainImageUrl() }}" class="mb-2 h-28 w-28 rounded-xl object-cover" alt="">
        @endif
        <input name="main_image" type="file" accept="image/png,image/jpeg,image/webp" class="field-input">
        @error('main_image') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-[11px] text-slate-400">PNG / JPG / WebP — بحد أقصى 4MB. يتم ضغطها تلقائيًا.</p>
    </div>

    <div>
        <label class="field-label">صور إضافية (Gallery)</label>
        <input name="gallery[]" type="file" accept="image/png,image/jpeg,image/webp" multiple class="field-input">
        @error('gallery.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="field-label">رابط فيديو (اختياري)</label>
        <input name="video_url" type="url" dir="ltr" value="{{ old('video_url') }}"
               placeholder="https://..." class="field-input">
        @error('video_url') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
</div>
