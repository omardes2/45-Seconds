<x-layouts.admin :title="$page->name" heading="محرر الصفحة">
    <x-slot:actions>
        <a href="{{ route('admin.pages.preview', $page) }}" target="_blank"
           class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">فتح المعاينة ↗</a>
    </x-slot:actions>

    <div x-data="{ open: 'seo' }" class="lg:flex lg:items-start lg:gap-6">

        {{-- ================= Live preview (center, large, sticky) ================= --}}
        <div class="mb-6 lg:order-1 lg:mb-0 lg:flex-1 lg:sticky lg:top-24">
            <div class="mx-auto w-full" style="max-width:360px">
                <div class="mb-2 flex items-center justify-between px-1">
                    <span class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        معاينة حيّة
                        <span data-preview-status class="text-[11px] font-normal text-slate-400"></span>
                    </span>
                    <button type="button" onclick="fsReloadPreview()"
                            class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-200">↻ تحديث</button>
                </div>
                <div class="overflow-hidden border-8 border-slate-900 bg-black shadow-2xl" style="border-radius:2.4rem">
                    <iframe id="builder-preview" src="{{ route('admin.pages.preview', $page) }}"
                            class="w-full bg-white" style="height:78vh;min-height:600px;max-height:800px;display:block"
                            title="معاينة"></iframe>
                </div>
            </div>
        </div>

        {{-- ================= Left control sidebar ================= --}}
        <div class="space-y-3 lg:order-2 lg:w-[400px] lg:flex-none">

            {{-- Status + publish --}}
            <div class="card p-4">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-slate-400">الحالة</div>
                        <x-badge :color="$page->status->color()" :label="$page->status->label()" class="mt-0.5" />
                    </div>
                    <div class="text-left">
                        <div class="text-xs text-slate-400">الرابط العام</div>
                        <a href="{{ route('admin.pages.preview', $page) }}" target="_blank" class="text-xs font-semibold text-brand-600" dir="ltr">/p/{{ $page->slug }}</a>
                    </div>
                </div>
                <div class="flex gap-2">
                    @if ($page->status !== \App\Enums\PageStatus::Published)
                        <form method="POST" action="{{ route('admin.pages.publish', $page) }}" class="flex-1">@csrf
                            <button class="btn-cta w-full">🚀 نشر الصفحة</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.pages.pause', $page) }}" class="flex-1">@csrf
                            <button class="btn w-full bg-amber-500 text-white">⏸ إيقاف</button>
                        </form>
                        <form method="POST" action="{{ route('admin.pages.publish', $page) }}" class="flex-1">@csrf
                            <button class="btn w-full bg-emerald-500 text-white">↻ إعادة نشر</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="px-1 pt-1 text-xs font-bold uppercase tracking-wide text-slate-400">التحرير المباشر</div>

            {{-- SEO / settings accordion --}}
            <div class="card overflow-hidden">
                <button type="button" @click="open = (open === 'seo' ? null : 'seo')"
                        class="flex w-full items-center justify-between gap-3 p-4 text-right">
                    <span class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-slate-900 text-xs font-black text-white">⚙︎</span>
                        <span>
                            <span class="block text-sm font-bold text-slate-900">إعدادات الصفحة و SEO</span>
                            <span class="block text-[11px] text-slate-400">الاسم، الرابط، العنوان، الوصف</span>
                        </span>
                    </span>
                    <span class="text-slate-300 transition" :class="open === 'seo' && 'rotate-90'">›</span>
                </button>
                <div x-show="open === 'seo'" x-collapse.duration.200ms>
                    <form data-autosave method="POST" action="{{ route('admin.pages.meta.update', $page) }}" class="space-y-3 border-t border-slate-100 p-4">
                        @csrf @method('PUT')
                        <div><label class="field-label">اسم الصفحة (داخلي)</label>
                            <input name="name" value="{{ $page->name }}" class="field-input" required></div>
                        <div><label class="field-label">الرابط (Slug)</label>
                            <input name="slug" dir="ltr" value="{{ $page->slug }}" class="field-input" required></div>
                        <div><label class="field-label">عنوان الصفحة (Title)</label>
                            <input name="title" value="{{ $page->title }}" class="field-input"></div>
                        <div><label class="field-label">وصف الميتا</label>
                            <textarea name="meta_description" rows="2" class="field-input">{{ $page->meta_description }}</textarea></div>
                        <div><label class="field-label">عنوان OpenGraph</label>
                            <input name="og_title" value="{{ $page->og_title }}" class="field-input"></div>
                        <div><label class="field-label">وصف OpenGraph</label>
                            <textarea name="og_description" rows="2" class="field-input">{{ $page->og_description }}</textarea></div>
                        <div class="text-left"><span data-save-status class="text-[11px] text-slate-400"></span></div>
                    </form>
                </div>
            </div>

            {{-- Section accordions --}}
            @foreach ($page->sections as $section)
                @php($type = $section->type)
                <div class="card overflow-hidden">
                    <button type="button" @click="open = (open === {{ $section->id }} ? null : {{ $section->id }})"
                            class="flex w-full items-center justify-between gap-3 p-4 text-right">
                        <span class="flex items-center gap-3">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-50 text-xs font-black text-brand-700">{{ $type->second() }}s</span>
                            <span>
                                <span class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-slate-900">{{ $type->label() }}</span>
                                    @unless ($section->is_enabled)
                                        <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-400">مخفي</span>
                                    @endunless
                                </span>
                                <span class="block text-[11px] text-slate-400">
                                    @switch($type->value)
                                        @case('offers') {{ $page->offers->count() }} عرض @break
                                        @case('testimonials') {{ $page->testimonials->count() }} رأي @break
                                        @case('trust') {{ $page->faqs->count() }} سؤال شائع @break
                                        @default اضغط للتحرير
                                    @endswitch
                                </span>
                            </span>
                        </span>
                        <span class="text-slate-300 transition" :class="open === {{ $section->id }} && 'rotate-90'">›</span>
                    </button>
                    <div x-show="open === {{ $section->id }}" x-collapse.duration.200ms>
                        <form data-autosave method="POST" action="{{ route('admin.pages.sections.update', [$page, $section]) }}"
                              enctype="multipart/form-data" class="border-t border-slate-100 p-4">
                            @csrf @method('PUT')
                            @include('admin.pages.sections._fields', ['section' => $section])

                            @if ($type->value === 'offers' && Route::has('admin.pages.offers.index'))
                                <a href="{{ route('admin.pages.offers.index', $page) }}" class="mt-3 block rounded-xl bg-slate-50 py-2 text-center text-xs font-bold text-brand-600">إدارة العروض ↗</a>
                            @elseif ($type->value === 'testimonials' && Route::has('admin.pages.testimonials.index'))
                                <a href="{{ route('admin.pages.testimonials.index', $page) }}" class="mt-3 block rounded-xl bg-slate-50 py-2 text-center text-xs font-bold text-brand-600">إدارة الآراء ↗</a>
                            @elseif ($type->value === 'trust' && Route::has('admin.pages.faqs.index'))
                                <a href="{{ route('admin.pages.faqs.index', $page) }}" class="mt-3 block rounded-xl bg-slate-50 py-2 text-center text-xs font-bold text-brand-600">إدارة الأسئلة الشائعة ↗</a>
                            @endif

                            <div class="mt-3 text-left"><span data-save-status class="text-[11px] text-slate-400"></span></div>
                        </form>
                    </div>
                </div>
            @endforeach

            <form method="POST" action="{{ route('admin.pages.archive', $page) }}" class="pt-2"
                  onsubmit="return confirm('أرشفة هذه الصفحة؟')">
                @csrf @method('DELETE')
                <button class="btn w-full bg-rose-50 text-rose-600">أرشفة الصفحة</button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        var preview = document.getElementById('builder-preview');
        var pStatus = document.querySelector('[data-preview-status]');
        var reloadTimer;
        window.fsReloadPreview = function () {
            if (!preview) return;
            if (pStatus) pStatus.textContent = '⟳ يحدّث…';
            preview.contentWindow.location.reload();
        };
        if (preview) {
            preview.addEventListener('load', function () { if (pStatus) pStatus.textContent = ''; });
        }
        function scheduleReload() {
            clearTimeout(reloadTimer);
            reloadTimer = setTimeout(window.fsReloadPreview, 250);
        }

        document.querySelectorAll('form[data-autosave]').forEach(function (form) {
            var timer;
            var status = form.querySelector('[data-save-status]');
            function setStatus(text, cls) { if (status) { status.textContent = text; status.className = 'text-[11px] ' + cls; } }
            function save() {
                setStatus('جارٍ الحفظ…', 'text-slate-400');
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                }).then(function (r) {
                    if (r.ok) { setStatus('✓ تم الحفظ', 'text-emerald-600'); scheduleReload(); }
                    else { setStatus('تعذّر الحفظ — تحقّق من الحقول', 'text-rose-500'); }
                }).catch(function () { setStatus('خطأ في الاتصال', 'text-rose-500'); });
            }
            form.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(save, 700); });
            form.addEventListener('change', function (e) {
                clearTimeout(timer);
                timer = setTimeout(save, e.target && e.target.type === 'file' ? 0 : 250);
            });
        });
    })();
    </script>
    @endpush
</x-layouts.admin>
