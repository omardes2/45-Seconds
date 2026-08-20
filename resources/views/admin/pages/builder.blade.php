<x-layouts.admin :title="$page->name" heading="محرر الصفحة">
    <x-slot:actions>
        <a href="{{ route('admin.pages.preview', $page) }}" target="_blank"
           class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">فتح المعاينة ↗</a>
    </x-slot:actions>

    <div x-data="{ reloadPreview() { const f = this.$refs.preview; if (f) f.src = f.src; } }"
         class="lg:grid lg:grid-cols-[380px_1fr] lg:items-start lg:gap-6">

        {{-- ================= Live preview (sticky on desktop) ================= --}}
        <div class="mb-6 lg:sticky lg:top-24 lg:mb-0">
            <div class="flex items-center justify-between px-1 pb-2">
                <div class="text-sm font-bold text-slate-700">معاينة حيّة</div>
                <button type="button" @click="reloadPreview()"
                        class="flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-200">
                    ↻ تحديث
                </button>
            </div>
            <div class="mx-auto w-full max-w-[320px] overflow-hidden rounded-[2.2rem] border-8 border-slate-900 bg-black shadow-2xl">
                <iframe x-ref="preview" src="{{ route('admin.pages.preview', $page) }}"
                        class="h-[600px] w-full bg-white" loading="lazy" title="معاينة"></iframe>
            </div>
            <p class="mt-2 text-center text-[11px] text-slate-400">تظهر تعديلاتك هنا. اضغط «تحديث» بعد أي تغيير.</p>
        </div>

        {{-- ================= Editing controls ================= --}}
        <div class="space-y-4">
            {{-- Status + public link --}}
            <div class="card flex items-center justify-between p-4">
                <div>
                    <div class="text-xs text-slate-400">الحالة</div>
                    <x-badge :color="$page->status->color()" :label="$page->status->label()" class="mt-0.5" />
                </div>
                <div class="text-left">
                    <div class="text-xs text-slate-400">الرابط العام</div>
                    <a href="{{ route('admin.pages.preview', $page) }}" target="_blank"
                       class="text-xs font-semibold text-brand-600" dir="ltr">/p/{{ $page->slug }}</a>
                </div>
            </div>

            {{-- Publish / pause bar --}}
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

            {{-- Steps --}}
            <div class="space-y-2">
                <div class="px-1 pt-1 text-xs font-bold uppercase tracking-wide text-slate-400">أقسام الصفحة</div>

                <a href="{{ route('admin.pages.meta', $page) }}" class="card flex items-center justify-between p-4 transition hover:ring-2 hover:ring-brand-100">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-slate-900 text-xs font-black text-white">⚙︎</span>
                        <div>
                            <div class="text-sm font-bold text-slate-900">إعدادات الصفحة و SEO</div>
                            <div class="text-[11px] text-slate-400">الاسم، الرابط، العنوان، الوصف</div>
                        </div>
                    </div>
                    <span class="text-slate-300">›</span>
                </a>

                @foreach ($page->sections as $section)
                    @php($type = $section->type)
                    <div class="card p-4 {{ $section->is_enabled ? '' : 'opacity-60' }}">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('admin.pages.sections.edit', [$page, $section]) }}" class="flex flex-1 items-center gap-3">
                                <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-50 text-xs font-black text-brand-700">{{ $type->second() }}s</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900">{{ $type->label() }}</div>
                                    <div class="text-[11px] text-slate-400">
                                        @switch($type->value)
                                            @case('offers') {{ $page->offers->count() }} عرض @break
                                            @case('testimonials') {{ $page->testimonials->count() }} رأي @break
                                            @case('trust') {{ $page->faqs->count() }} سؤال شائع @break
                                            @default اضغط للتحرير
                                        @endswitch
                                    </div>
                                </div>
                            </a>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.pages.sections.toggle', [$page, $section]) }}">@csrf
                                    <button type="submit" title="تفعيل/إيقاف"
                                            class="relative h-6 w-11 rounded-full transition {{ $section->is_enabled ? 'bg-emerald-500' : 'bg-slate-200' }}">
                                        <span class="absolute top-0.5 h-5 w-5 rounded-full bg-white transition {{ $section->is_enabled ? 'right-0.5' : 'right-5' }}"></span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Content management shortcuts for relational sections --}}
                        @if ($type->value === 'offers' && Route::has('admin.pages.offers.index'))
                            <a href="{{ route('admin.pages.offers.index', $page) }}" class="mt-3 block rounded-xl bg-slate-50 py-2 text-center text-xs font-bold text-brand-600">إدارة العروض</a>
                        @elseif ($type->value === 'testimonials' && Route::has('admin.pages.testimonials.index'))
                            <a href="{{ route('admin.pages.testimonials.index', $page) }}" class="mt-3 block rounded-xl bg-slate-50 py-2 text-center text-xs font-bold text-brand-600">إدارة الآراء</a>
                        @elseif ($type->value === 'trust' && Route::has('admin.pages.faqs.index'))
                            <a href="{{ route('admin.pages.faqs.index', $page) }}" class="mt-3 block rounded-xl bg-slate-50 py-2 text-center text-xs font-bold text-brand-600">إدارة الأسئلة الشائعة</a>
                        @endif
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('admin.pages.archive', $page) }}" class="pt-2"
                  onsubmit="return confirm('أرشفة هذه الصفحة؟')">
                @csrf @method('DELETE')
                <button class="btn w-full bg-rose-50 text-rose-600">أرشفة الصفحة</button>
            </form>
        </div>
    </div>
</x-layouts.admin>
