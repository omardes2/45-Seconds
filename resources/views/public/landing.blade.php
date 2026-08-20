@php
    use App\Enums\CurrencyEnum;
    $seo = $data['seo'];
    $product = $data['product'];
    $offers = $data['offers'];
    $currency = $data['currency'];
    $symbol = CurrencyEnum::from($currency)->symbol();
    $defaultId = $data['default_offer_id'];
    // Compact offer map for the client (price recomputed server-side on order).
    $offersJs = collect($offers)->keyBy('id')->map(fn ($o) => [
        'price' => (float) $o['price'],
        'qty' => $o['quantity'],
        'name' => $o['name'],
    ]);
    $sections = collect($data['sections']);
    $orderAction = \Illuminate\Support\Facades\Route::has('public.order.store')
        ? route('public.order.store', $data['page']['slug']) : '#';
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1">
    <title>{{ $seo['title'] }}</title>
    @if ($seo['description'])<meta name="description" content="{{ $seo['description'] }}">@endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seo['og_title'] ?: $seo['title'] }}">
    @if ($seo['og_description'])<meta property="og:description" content="{{ $seo['og_description'] }}">@endif
    @if ($seo['og_image'])<meta property="og:image" content="{{ \Illuminate\Support\Str::startsWith($seo['og_image'], 'http') ? $seo['og_image'] : url($seo['og_image']) }}">@endif
    <meta name="theme-color" content="#0b1020">
    @if ($preview ?? false)<meta name="robots" content="noindex">@endif
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-200">
<div class="phone-shell relative bg-white"
     x-data="{
        offers: @js($offersJs),
        selected: @js($defaultId),
        checkoutOpen: false,
        submitting: false,
        progress: 0,
        preview: @js($preview ?? false),
        symbol: @js($symbol),
        get current() { return this.offers[this.selected] || Object.values(this.offers)[0] || {price:0, qty:1, name:''} },
        priceText() { return this.symbol + ' ' + Number(this.current.price).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}) },
        select(id) { this.selected = id; window.dispatchEvent(new CustomEvent('fs:offer-selected', {detail:{offer_id:id}})); },
        openCheckout() { this.checkoutOpen = true; window.dispatchEvent(new CustomEvent('fs:checkout-opened')); },
        closeCheckout() { this.checkoutOpen = false; },
        onScroll() { const h = document.body.scrollHeight - window.innerHeight; this.progress = h > 0 ? Math.min(45, Math.round((window.scrollY / h) * 45)) : 0; },
        onSubmit(e) { if (this.preview) { e.preventDefault(); alert('هذه معاينة — لن يتم إنشاء طلب.'); return; } this.submitting = true; },
     }"
     x-init="onScroll()" @scroll.window.passive="onScroll()">

    {{-- Scroll progress 0 → 45 --}}
    <div class="safe-top pointer-events-none sticky top-0 z-40">
        <div class="flex items-center gap-2 bg-white/80 px-4 py-1.5 backdrop-blur">
            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-200">
                <div class="h-full rounded-full bg-brand-600 transition-all duration-200" :style="`width: ${(progress/45)*100}%`"></div>
            </div>
            <span class="text-[11px] font-black tabular-nums text-slate-500"><span x-text="progress"></span>/45 ث</span>
        </div>
    </div>

    @if ($preview ?? false)
        <div class="bg-amber-400 px-4 py-1.5 text-center text-[11px] font-bold text-amber-950">وضع المعاينة — بيانات المسودة</div>
    @endif

    {{-- Sections --}}
    @foreach ($sections as $section)
        @php($s = $section['settings'])
        @switch($section['type'])

            @case('hero')
                <section class="relative overflow-hidden px-5 pb-8 pt-6 text-center"
                         @if(!empty($s['background_image_url'])) style="background-image:linear-gradient(180deg,rgba(11,16,32,.55),rgba(11,16,32,.85)),url('{{ $s['background_image_url'] }}');background-size:cover;background-position:center" @endif>
                    <div class="{{ !empty($s['background_image_url']) ? 'text-white' : 'text-slate-900' }}">
                        @if (!empty($s['badge']))
                            <span class="inline-flex rounded-full bg-brand-600 px-3 py-1 text-xs font-bold text-white">{{ $s['badge'] }}</span>
                        @endif
                        <h1 class="mt-3 text-3xl font-black leading-tight">
                            {{ $s['headline'] ?? $product['name'] }}
                            @if (!empty($s['highlight']))<span class="text-brand-500">{{ $s['highlight'] }}</span>@endif
                        </h1>
                        @if (!empty($s['subtitle']))<p class="mt-2 text-base opacity-90">{{ $s['subtitle'] }}</p>@endif
                    </div>
                    @if (!empty($s['main_image_url']))
                        <img src="{{ $s['main_image_url'] }}" alt="{{ $product['name'] }}" fetchpriority="high"
                             class="mx-auto mt-5 w-full max-w-[320px] rounded-3xl object-cover shadow-2xl">
                    @elseif (!empty($product['main_image_url']))
                        <img src="{{ $product['main_image_url'] }}" alt="{{ $product['name'] }}" fetchpriority="high"
                             class="mx-auto mt-5 w-full max-w-[320px] rounded-3xl object-cover shadow-2xl">
                    @endif
                    @if (($s['show_price'] ?? true))
                        <div class="mt-5 flex items-center justify-center gap-3">
                            <span class="text-3xl font-black text-brand-600" x-text="priceText()"></span>
                            @if ($product['compare_at_price'])
                                <span class="text-lg font-semibold text-slate-400 line-through">{{ money($product['compare_at_price'], $currency) }}</span>
                            @endif
                        </div>
                    @endif
                    <button @click="openCheckout()" class="btn-cta mt-5 w-full">{{ $s['cta_text'] ?? 'اطلب الآن' }}</button>
                    @if (!empty($s['delivery_text']))<p class="mt-2 text-sm font-semibold text-emerald-600">✓ {{ $s['delivery_text'] }}</p>@endif
                </section>
                @break

            @case('problem')
                <section class="bg-slate-50 px-5 py-8">
                    @if (!empty($s['title']))<h2 class="text-center text-2xl font-black text-slate-900">{{ $s['title'] }}</h2>@endif
                    @if (!empty($s['subtitle']))<p class="mt-1 text-center text-slate-500">{{ $s['subtitle'] }}</p>@endif
                    @if (!empty($s['image_url']))<img src="{{ $s['image_url'] }}" loading="lazy" class="mx-auto mt-4 w-full max-w-[320px] rounded-2xl" alt="">@endif
                    <div class="mt-5 space-y-3">
                        @foreach (($s['items'] ?? []) as $item)
                            <div class="card flex items-start gap-3 p-4">
                                <span class="text-2xl">{{ $item['icon'] ?: '⚠️' }}</span>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $item['title'] }}</div>
                                    @if (!empty($item['description']))<div class="text-sm text-slate-500">{{ $item['description'] }}</div>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
                @break

            @case('demo')
                <section class="px-5 py-8">
                    @if (!empty($s['title']))<h2 class="text-center text-2xl font-black text-slate-900">{{ $s['title'] }}</h2>@endif
                    @if (!empty($s['subtitle']))<p class="mt-1 text-center text-slate-500">{{ $s['subtitle'] }}</p>@endif
                    <div class="mt-5">
                        @if ($s['demo_type'] === 'before_after' && !empty($s['before_image_url']) && !empty($s['after_image_url']))
                            <div class="relative mx-auto max-w-[340px] select-none overflow-hidden rounded-2xl"
                                 x-data="{ pos: 50 }" x-init="$el.style.touchAction='none'">
                                <img src="{{ $s['after_image_url'] }}" class="block w-full" alt="بعد" draggable="false">
                                <div class="absolute inset-0 overflow-hidden" :style="`width:${pos}%`">
                                    <img src="{{ $s['before_image_url'] }}" class="block h-full w-full object-cover" alt="قبل" style="max-width:none" :style="`width:${100/(pos/100)}%`" draggable="false">
                                    <span class="absolute bottom-2 right-2 rounded bg-black/60 px-2 py-0.5 text-[10px] text-white">قبل</span>
                                </div>
                                <span class="absolute bottom-2 left-2 rounded bg-black/60 px-2 py-0.5 text-[10px] text-white">بعد</span>
                                <div class="absolute inset-y-0" :style="`right:calc(${pos}% - 1px)`">
                                    <div class="h-full w-0.5 bg-white shadow"></div>
                                    <div class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 grid h-9 w-9 place-items-center rounded-full bg-white shadow-lg text-brand-600" style="right:0">⇄</div>
                                </div>
                                <input type="range" min="0" max="100" x-model="pos"
                                       @change.once="window.dispatchEvent(new CustomEvent('fs:demo-interaction'))"
                                       class="absolute inset-0 h-full w-full cursor-ew-resize opacity-0" aria-label="مقارنة قبل وبعد">
                            </div>
                        @elseif ($s['demo_type'] === 'video' && !empty($s['video_url']))
                            <div class="mx-auto aspect-video max-w-[340px] overflow-hidden rounded-2xl bg-black">
                                <iframe src="{{ $s['video_url'] }}" class="h-full w-full" loading="lazy" allowfullscreen title="عرض"></iframe>
                            </div>
                        @elseif (!empty($s['image_url']))
                            <img src="{{ $s['image_url'] }}" loading="lazy" class="mx-auto w-full max-w-[340px] rounded-2xl" alt="">
                        @endif
                    </div>
                </section>
                @break

            @case('benefits')
                <section class="bg-slate-50 px-5 py-8">
                    @if (!empty($s['title']))<h2 class="text-center text-2xl font-black text-slate-900">{{ $s['title'] }}</h2>@endif
                    @if (!empty($s['subtitle']))<p class="mt-1 text-center text-slate-500">{{ $s['subtitle'] }}</p>@endif
                    <div class="mt-5 space-y-3">
                        @foreach (($s['items'] ?? []) as $item)
                            <div class="card flex items-start gap-3 p-4">
                                <span class="grid h-10 w-10 flex-none place-items-center rounded-xl bg-brand-50 text-xl">{{ $item['icon'] ?: '✅' }}</span>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $item['title'] }}</div>
                                    @if (!empty($item['description']))<div class="text-sm text-slate-500">{{ $item['description'] }}</div>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
                @break

            @case('testimonials')
                @if (!empty($data['testimonials']))
                <section class="px-5 py-8">
                    @if (!empty($s['title']))<h2 class="text-center text-2xl font-black text-slate-900">{{ $s['title'] }}</h2>@endif
                    @if (!empty($s['subtitle']))<p class="mt-1 text-center text-slate-500">{{ $s['subtitle'] }}</p>@endif
                    <div class="no-scrollbar mt-5 flex snap-x gap-3 overflow-x-auto pb-2">
                        @foreach ($data['testimonials'] as $t)
                            <div class="w-[80%] flex-none snap-center card p-4">
                                <div class="flex items-center gap-3">
                                    @if (!empty($t['customer_image_url']))
                                        <img src="{{ $t['customer_image_url'] }}" class="h-10 w-10 rounded-full object-cover" alt="">
                                    @else
                                        <span class="grid h-10 w-10 place-items-center rounded-full bg-brand-100 font-bold text-brand-700">{{ mb_substr($t['customer_name'],0,1) }}</span>
                                    @endif
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">{{ $t['customer_name'] }}
                                            @if ($t['is_verified'])<span class="text-emerald-500">✓</span>@endif
                                        </div>
                                        <div class="text-amber-400">{!! str_repeat('★', (int)$t['rating']) !!}<span class="text-slate-200">{!! str_repeat('★', 5-(int)$t['rating']) !!}</span></div>
                                    </div>
                                </div>
                                <p class="mt-3 text-sm text-slate-600">{{ $t['text'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif
                @break

            @case('offers')
                <section class="bg-slate-50 px-5 py-8" id="offers">
                    @if (!empty($s['title']))<h2 class="text-center text-2xl font-black text-slate-900">{{ $s['title'] }}</h2>@endif
                    @if (!empty($s['subtitle']))<p class="mt-1 text-center text-slate-500">{{ $s['subtitle'] }}</p>@endif
                    <div class="mt-5 space-y-3">
                        @foreach ($offers as $offer)
                            <label @click="select({{ $offer['id'] }})"
                                   class="relative flex cursor-pointer items-center gap-3 rounded-2xl border-2 bg-white p-4 transition"
                                   :class="selected === {{ $offer['id'] }} ? 'border-brand-600 ring-2 ring-brand-100' : 'border-slate-200'">
                                <span class="grid h-6 w-6 flex-none place-items-center rounded-full border-2"
                                      :class="selected === {{ $offer['id'] }} ? 'border-brand-600 bg-brand-600' : 'border-slate-300'">
                                    <span class="h-2 w-2 rounded-full bg-white" x-show="selected === {{ $offer['id'] }}"></span>
                                </span>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900">{{ $offer['name'] }}</span>
                                        @if (!empty($offer['badge_text']))
                                            <span class="rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-bold text-white">{{ $offer['badge_text'] }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-400">{{ $offer['quantity'] }} × {{ $product['name'] }}</div>
                                </div>
                                <div class="text-left">
                                    <div class="text-lg font-black text-slate-900">{{ money($offer['price'], $currency) }}</div>
                                    @if (!empty($offer['compare_at_price']))
                                        <div class="text-xs text-slate-400 line-through">{{ money($offer['compare_at_price'], $currency) }}</div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </section>
                @break

            @case('trust')
                <section class="px-5 py-8">
                    @if (!empty($s['title']))<h2 class="text-center text-2xl font-black text-slate-900">{{ $s['title'] }}</h2>@endif
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        @foreach (($s['items'] ?? []) as $item)
                            <div class="card p-4 text-center">
                                <div class="text-2xl">{{ $item['icon'] ?: '🛡️' }}</div>
                                <div class="mt-1 text-sm font-bold text-slate-800">{{ $item['title'] }}</div>
                                @if (!empty($item['description']))<div class="text-[11px] text-slate-400">{{ $item['description'] }}</div>@endif
                            </div>
                        @endforeach
                    </div>

                    @if (!empty($data['faqs']))
                        <h3 class="mb-3 mt-8 text-lg font-black text-slate-900">{{ $s['faq_title'] ?? 'الأسئلة الشائعة' }}</h3>
                        <div class="space-y-2">
                            @foreach ($data['faqs'] as $faq)
                                <div class="card overflow-hidden" x-data="{ open: false }">
                                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-2 p-4 text-right">
                                        <span class="text-sm font-bold text-slate-800">{{ $faq['question'] }}</span>
                                        <span class="text-slate-400 transition" :class="open && 'rotate-180'">▾</span>
                                    </button>
                                    <div x-show="open" x-collapse class="px-4 pb-4 text-sm text-slate-600">{{ $faq['answer'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
                @break

            @case('final_cta')
                <section class="bg-ink px-5 py-10 text-center text-white">
                    <div class="text-6xl font-black text-brand-500">45<span class="text-white/30">/45</span></div>
                    <h2 class="mt-3 text-2xl font-black">{{ $s['headline'] ?? 'انتهت الـ45 ثانية. هل تريده؟' }}</h2>
                    @if (!empty($s['subtitle']))<p class="mt-2 text-white/70">{{ $s['subtitle'] }}</p>@endif
                    <div class="mx-auto mt-5 max-w-[320px] rounded-2xl bg-white/10 p-4 text-right">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-white/70">{{ $product['name'] }}</span>
                            <span class="text-sm font-bold" x-text="current.name"></span>
                        </div>
                        <div class="mt-1 flex items-center justify-between">
                            <span class="text-sm text-white/70">الإجمالي</span>
                            <span class="text-2xl font-black text-brand-400" x-text="priceText()"></span>
                        </div>
                    </div>
                    <button @click="openCheckout()" class="btn-cta mt-5 w-full max-w-[320px]">{{ $s['cta_text'] ?? 'اطلب الآن' }}</button>
                </section>
                @break
        @endswitch
    @endforeach

    {{-- spacer so content is not hidden behind the sticky bar --}}
    <div class="h-24"></div>

    {{-- Sticky CTA --}}
    <div class="safe-bottom fixed inset-x-0 bottom-0 z-40 mx-auto max-w-[480px] border-t border-slate-200 bg-white/95 px-4 pt-3 backdrop-blur"
         x-show="!checkoutOpen" x-transition.opacity>
        <div class="flex items-center gap-3">
            <div class="flex-none">
                <div class="text-[11px] text-slate-400">الإجمالي</div>
                <div class="text-xl font-black text-slate-900" x-text="priceText()"></div>
            </div>
            <button @click="openCheckout()" class="btn-cta flex-1">اطلب الآن</button>
        </div>
    </div>

    {{-- Checkout bottom sheet --}}
    <div x-show="checkoutOpen" x-cloak class="fixed inset-0 z-50" style="display:none">
        <div class="absolute inset-0 bg-black/50" @click="closeCheckout()" x-transition.opacity></div>
        <div class="safe-bottom absolute inset-x-0 bottom-0 mx-auto max-h-[92vh] max-w-[480px] overflow-y-auto rounded-t-3xl bg-white p-5"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0">
            <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-slate-200"></div>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-black text-slate-900">إتمام الطلب</h3>
                <button @click="closeCheckout()" class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-slate-500">✕</button>
            </div>

            {{-- order summary --}}
            <div class="mb-4 rounded-2xl bg-slate-50 p-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">العرض</span>
                    <span class="font-bold text-slate-900" x-text="current.name"></span>
                </div>
                <div class="mt-1 flex items-center justify-between">
                    <span class="text-slate-500">الإجمالي (الدفع عند الاستلام)</span>
                    <span class="text-xl font-black text-brand-600" x-text="priceText()"></span>
                </div>
            </div>

            <form method="POST" action="{{ $orderAction }}" @submit="onSubmit($event)" class="space-y-3">
                @csrf
                <input type="hidden" name="offer_id" :value="selected">
                <div>
                    <label class="field-label">الاسم الكامل</label>
                    <input name="full_name" value="{{ old('full_name') }}" class="field-input" required autocomplete="name">
                    @error('full_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">رقم الهاتف</label>
                    <input name="phone" type="tel" dir="ltr" value="{{ old('phone') }}" class="field-input" required autocomplete="tel" inputmode="tel">
                    @error('phone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">المدينة</label>
                        <input name="city" value="{{ old('city') }}" class="field-input" required>
                        @error('city') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="field-label">المنطقة</label>
                        <input name="area" value="{{ old('area') }}" class="field-input">
                    </div>
                </div>
                <div>
                    <label class="field-label">العنوان</label>
                    <input name="address" value="{{ old('address') }}" class="field-input" required autocomplete="street-address">
                    @error('address') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label">ملاحظات (اختياري)</label>
                    <textarea name="notes" rows="2" class="field-input">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="btn-cta w-full" :disabled="submitting">
                    <span x-show="!submitting">تأكيد الطلب — <span x-text="priceText()"></span></span>
                    <span x-show="submitting">جارٍ الإرسال...</span>
                </button>
                <p class="text-center text-[11px] text-slate-400">بتأكيد الطلب أنت توافق على التواصل معك لتأكيده.</p>
            </form>
        </div>
    </div>
</div>
@unless ($preview ?? false)
<script>
(function () {
    var pageId = {{ (int) ($data['page']['id'] ?? 0) }};
    var url = @js(route('track.event'));
    function send(type, meta) {
        try {
            fetch(url, {
                method: 'POST', keepalive: true,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ type: type, page_id: pageId, metadata: meta || {} }),
            });
        } catch (e) {}
    }
    window.addEventListener('DOMContentLoaded', function () { send('view_content'); });
    window.addEventListener('fs:offer-selected', function (e) { send('offer_selected', e.detail); });
    window.addEventListener('fs:checkout-opened', function () { send('checkout_opened'); });
    window.addEventListener('fs:demo-interaction', function () { send('demo_interaction'); });
})();
</script>
@endunless
@stack('body')
</body>
</html>
