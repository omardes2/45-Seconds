@php($s = $section->settings ?? [])
<x-layouts.admin :title="$section->type->label()" heading="{{ $section->type->label() }}">
    <form method="POST" action="{{ route('admin.pages.sections.update', [$page, $section]) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf @method('PUT')

        {{-- Enabled toggle --}}
        <label class="card flex items-center justify-between p-4">
            <div>
                <div class="text-sm font-bold text-slate-900">تفعيل هذا القسم</div>
                <div class="text-[11px] text-slate-400">يظهر عند {{ $section->type->second() }} ثانية</div>
            </div>
            <input type="checkbox" name="is_enabled" value="1" @checked($section->is_enabled)
                   class="h-6 w-11 rounded-full border-slate-300 text-emerald-500 focus:ring-emerald-500">
        </label>

        <div class="card space-y-4 p-4">
        @switch($section->type->value)
            @case('hero')
                <div><label class="field-label">شارة (Badge)</label>
                    <input name="badge" value="{{ old('badge', $s['badge'] ?? '') }}" class="field-input" placeholder="الأكثر مبيعًا"></div>
                <div><label class="field-label">العنوان الرئيسي</label>
                    <input name="headline" value="{{ old('headline', $s['headline'] ?? '') }}" class="field-input"></div>
                <div><label class="field-label">كلمة مميزة (تُلوَّن)</label>
                    <input name="highlight" value="{{ old('highlight', $s['highlight'] ?? '') }}" class="field-input"></div>
                <div><label class="field-label">العنوان الفرعي</label>
                    <textarea name="subtitle" rows="2" class="field-input">{{ old('subtitle', $s['subtitle'] ?? '') }}</textarea></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="field-label">نص الزر</label>
                        <input name="cta_text" value="{{ old('cta_text', $s['cta_text'] ?? 'اطلب الآن') }}" class="field-input"></div>
                    <div><label class="field-label">نص التوصيل</label>
                        <input name="delivery_text" value="{{ old('delivery_text', $s['delivery_text'] ?? '') }}" class="field-input"></div>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="show_price" value="1" @checked($s['show_price'] ?? true) class="rounded border-slate-300 text-brand-600">
                    إظهار السعر في البداية
                </label>
                <div><label class="field-label">رابط فيديو (اختياري)</label>
                    <input name="video_url" type="url" dir="ltr" value="{{ old('video_url', $s['video_url'] ?? '') }}" class="field-input"></div>
                @include('admin.pages.sections._image', ['name' => 'main_image', 'label' => 'الصورة الرئيسية', 'current' => $s['main_image'] ?? null])
                @include('admin.pages.sections._image', ['name' => 'background_image', 'label' => 'صورة الخلفية (اختياري)', 'current' => $s['background_image'] ?? null])
                @break

            @case('problem')
                <div><label class="field-label">عنوان القسم</label>
                    <input name="title" value="{{ old('title', $s['title'] ?? '') }}" class="field-input"></div>
                <div><label class="field-label">العنوان الفرعي</label>
                    <textarea name="subtitle" rows="2" class="field-input">{{ old('subtitle', $s['subtitle'] ?? '') }}</textarea></div>
                @include('admin.pages.sections._image', ['name' => 'image', 'label' => 'صورة (اختياري)', 'current' => $s['image'] ?? null])
                @include('admin.pages.sections._items', ['items' => $s['items'] ?? [], 'max' => 5, 'label' => 'المشاكل (حتى 5)'])
                @break

            @case('demo')
                <div><label class="field-label">عنوان القسم</label>
                    <input name="title" value="{{ old('title', $s['title'] ?? '') }}" class="field-input"></div>
                <div><label class="field-label">العنوان الفرعي</label>
                    <textarea name="subtitle" rows="2" class="field-input">{{ old('subtitle', $s['subtitle'] ?? '') }}</textarea></div>
                <div x-data="{ t: '{{ old('demo_type', $s['demo_type'] ?? 'image') }}' }">
                    <label class="field-label">نوع العرض</label>
                    <select name="demo_type" x-model="t" class="field-input">
                        @foreach (\App\Enums\DemoType::options() as $val => $lbl)
                            <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <div class="mt-3 space-y-4">
                        <div x-show="t === 'image'">
                            @include('admin.pages.sections._image', ['name' => 'image', 'label' => 'صورة العرض', 'current' => $s['image'] ?? null])
                        </div>
                        <div x-show="t === 'video'">
                            <label class="field-label">رابط الفيديو</label>
                            <input name="video_url" type="url" dir="ltr" value="{{ old('video_url', $s['video_url'] ?? '') }}" class="field-input">
                        </div>
                        <div x-show="t === 'before_after'" class="space-y-4">
                            @include('admin.pages.sections._image', ['name' => 'before_image', 'label' => 'صورة قبل', 'current' => $s['before_image'] ?? null])
                            @include('admin.pages.sections._image', ['name' => 'after_image', 'label' => 'صورة بعد', 'current' => $s['after_image'] ?? null])
                        </div>
                    </div>
                </div>
                @break

            @case('benefits')
                <div><label class="field-label">عنوان القسم</label>
                    <input name="title" value="{{ old('title', $s['title'] ?? '') }}" class="field-input"></div>
                <div><label class="field-label">العنوان الفرعي</label>
                    <textarea name="subtitle" rows="2" class="field-input">{{ old('subtitle', $s['subtitle'] ?? '') }}</textarea></div>
                @include('admin.pages.sections._items', ['items' => $s['items'] ?? [], 'max' => 12, 'label' => 'المميزات'])
                @break

            @case('testimonials')
                <div><label class="field-label">عنوان القسم</label>
                    <input name="title" value="{{ old('title', $s['title'] ?? '') }}" class="field-input"></div>
                <div><label class="field-label">العنوان الفرعي</label>
                    <textarea name="subtitle" rows="2" class="field-input">{{ old('subtitle', $s['subtitle'] ?? '') }}</textarea></div>
                <p class="rounded-xl bg-slate-50 p-3 text-xs text-slate-500">تُدار آراء العملاء من زر «إدارة الآراء» في المحرر.</p>
                @break

            @case('offers')
                <div><label class="field-label">عنوان القسم</label>
                    <input name="title" value="{{ old('title', $s['title'] ?? '') }}" class="field-input"></div>
                <div><label class="field-label">العنوان الفرعي</label>
                    <textarea name="subtitle" rows="2" class="field-input">{{ old('subtitle', $s['subtitle'] ?? '') }}</textarea></div>
                <p class="rounded-xl bg-slate-50 p-3 text-xs text-slate-500">تُدار العروض والأسعار من زر «إدارة العروض» في المحرر.</p>
                @break

            @case('trust')
                <div><label class="field-label">عنوان القسم</label>
                    <input name="title" value="{{ old('title', $s['title'] ?? '') }}" class="field-input"></div>
                <div><label class="field-label">العنوان الفرعي</label>
                    <textarea name="subtitle" rows="2" class="field-input">{{ old('subtitle', $s['subtitle'] ?? '') }}</textarea></div>
                @include('admin.pages.sections._items', ['items' => $s['items'] ?? [], 'max' => 8, 'label' => 'عناصر الثقة (دفع عند الاستلام، توصيل سريع...)'])
                <div><label class="field-label">عنوان الأسئلة الشائعة</label>
                    <input name="faq_title" value="{{ old('faq_title', $s['faq_title'] ?? 'الأسئلة الشائعة') }}" class="field-input"></div>
                <p class="rounded-xl bg-slate-50 p-3 text-xs text-slate-500">تُدار الأسئلة الشائعة من زر «إدارة الأسئلة الشائعة» في المحرر.</p>
                @break

            @case('final_cta')
                <div><label class="field-label">العنوان</label>
                    <input name="headline" value="{{ old('headline', $s['headline'] ?? '') }}" class="field-input"></div>
                <div><label class="field-label">العنوان الفرعي</label>
                    <textarea name="subtitle" rows="2" class="field-input">{{ old('subtitle', $s['subtitle'] ?? '') }}</textarea></div>
                <div><label class="field-label">نص الزر</label>
                    <input name="cta_text" value="{{ old('cta_text', $s['cta_text'] ?? 'اطلب الآن') }}" class="field-input"></div>
                @break
        @endswitch
        </div>

        <button type="submit" class="btn-brand w-full">حفظ القسم</button>
    </form>
</x-layouts.admin>
