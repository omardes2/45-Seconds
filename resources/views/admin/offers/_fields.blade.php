<div>
    <label class="field-label">اسم العرض</label>
    <input name="name" value="{{ old('name', $offer->name ?? '') }}" class="field-input" placeholder="قطعتان" required>
    @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
</div>
<div class="grid grid-cols-3 gap-2">
    <div>
        <label class="field-label">الكمية</label>
        <input name="quantity" type="number" min="1" dir="ltr" value="{{ old('quantity', $offer->quantity ?? 1) }}" class="field-input" required>
        @error('quantity') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label">السعر</label>
        <input name="price" type="number" step="0.01" min="0" dir="ltr" value="{{ old('price', $offer->price ?? '') }}" class="field-input" required>
        @error('price') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="field-label">قبل الخصم</label>
        <input name="compare_at_price" type="number" step="0.01" min="0" dir="ltr" value="{{ old('compare_at_price', $offer->compare_at_price ?? '') }}" class="field-input">
        @error('compare_at_price') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>
</div>
<div>
    <label class="field-label">نص الشارة (اختياري)</label>
    <input name="badge_text" value="{{ old('badge_text', $offer->badge_text ?? '') }}" class="field-input" placeholder="الأكثر طلبًا">
</div>
<div class="flex items-center gap-4">
    <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $offer->is_default ?? false)) class="rounded border-slate-300 text-brand-600">
        العرض الافتراضي
    </label>
    <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $offer->is_active ?? true)) class="rounded border-slate-300 text-brand-600">
        مفعّل
    </label>
</div>
