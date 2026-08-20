<x-layouts.admin title="إعدادات الصفحة" heading="إعدادات الصفحة">
    <form method="POST" action="{{ route('admin.pages.meta.update', $page) }}" class="space-y-4">
        @csrf @method('PUT')

        <div class="card p-4 space-y-4">
            <div>
                <label class="field-label">اسم الصفحة (داخلي)</label>
                <input name="name" value="{{ old('name', $page->name) }}" class="field-input" required>
                @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="field-label">الرابط (Slug)</label>
                <div class="flex items-center gap-1 text-sm text-slate-400" dir="ltr">
                    <span>/p/</span>
                    <input name="slug" dir="ltr" value="{{ old('slug', $page->slug) }}" class="field-input flex-1" required>
                </div>
                @error('slug') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="card p-4 space-y-4">
            <h3 class="text-sm font-bold text-slate-700">SEO والمشاركة</h3>
            <div>
                <label class="field-label">عنوان الصفحة (Title)</label>
                <input name="title" value="{{ old('title', $page->title) }}" class="field-input">
            </div>
            <div>
                <label class="field-label">وصف الميتا</label>
                <textarea name="meta_description" rows="2" class="field-input">{{ old('meta_description', $page->meta_description) }}</textarea>
            </div>
            <div>
                <label class="field-label">عنوان OpenGraph</label>
                <input name="og_title" value="{{ old('og_title', $page->og_title) }}" class="field-input">
            </div>
            <div>
                <label class="field-label">وصف OpenGraph</label>
                <textarea name="og_description" rows="2" class="field-input">{{ old('og_description', $page->og_description) }}</textarea>
            </div>
            <p class="text-[11px] text-slate-400">صورة المشاركة الافتراضية هي صورة المنتج الرئيسية.</p>
        </div>

        <button type="submit" class="btn-brand w-full">حفظ</button>
    </form>
</x-layouts.admin>
