<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'الدخول') · {{ config('app.name') }}</title>
    <meta name="theme-color" content="#4f46e5">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100">
    <div class="phone-shell flex min-h-dvh flex-col justify-center bg-white px-6 py-10">
        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-3xl bg-brand-600 text-2xl font-black text-white shadow-lg">45</div>
            <h1 class="text-2xl font-black text-slate-900">{{ config('app.name') }}</h1>
            <p class="mt-1 text-sm text-slate-400">لوحة إدارة صفحات البيع</p>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </div>
</body>
</html>
