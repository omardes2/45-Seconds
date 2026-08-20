<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>تم استلام طلبك</title>
    <meta name="robots" content="noindex">
    @include('public.partials.tracking')
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-200">
    <div class="phone-shell flex min-h-dvh flex-col items-center justify-center bg-white px-6 py-10 text-center">
        <div class="mb-5 grid h-20 w-20 place-items-center rounded-full bg-emerald-100 text-4xl">✅</div>
        <h1 class="text-2xl font-black text-slate-900">تم استلام طلبك!</h1>
        <p class="mt-2 text-slate-500">سنتواصل معك قريبًا لتأكيد الطلب والتوصيل.</p>

        <div class="mt-6 w-full max-w-[340px] rounded-2xl bg-slate-50 p-5 text-right">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">رقم الطلب</span>
                <span class="text-lg font-black text-brand-600" dir="ltr">#{{ $order->order_number }}</span>
            </div>
            <div class="mt-2 flex items-center justify-between">
                <span class="text-sm text-slate-500">العرض</span>
                <span class="text-sm font-bold text-slate-900">{{ $order->offer?->name ?? $order->product->name }}</span>
            </div>
            <div class="mt-2 flex items-center justify-between">
                <span class="text-sm text-slate-500">الإجمالي (الدفع عند الاستلام)</span>
                <span class="text-xl font-black text-slate-900">{{ money($order->total, $order->currency) }}</span>
            </div>
        </div>

        <a href="{{ route('public.show', $page->slug) }}" class="btn-brand mt-6 w-full max-w-[340px]">العودة للصفحة</a>
    </div>
    <script>
        // Purchase fires only here — i.e. only after the order was created
        // server-side. eventID matches the CAPI event for deduplication.
        window.addEventListener('DOMContentLoaded', function () {
            if (window.fsTrack) {
                fsTrack('Purchase', {
                    content_ids: [@js((string) $order->product_id)],
                    content_name: @js($order->product->name),
                    content_type: 'product',
                    value: {{ (float) $order->total }},
                    currency: @js($order->currency->value),
                    num_items: {{ (int) $order->quantity }},
                }, @js('order_'.$order->id));
            }
        });
    </script>
    @stack('body')
</body>
</html>
