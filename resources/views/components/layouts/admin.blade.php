@props([
    'title' => 'لوحة التحكم',
    'heading' => 'لوحة التحكم',
    'actions' => null,
    // When true the content stays phone-width even on desktop (mobile-style screen).
    'narrow' => false,
])
@php
    $contentMax = $narrow ? 'max-w-[480px]' : 'max-w-[480px] lg:max-w-6xl';
@endphp
@php
    $nav = [
        ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => 'الرئيسية', 'icon' => 'home', 'can' => null],
        ['route' => 'admin.pages.index', 'active' => 'admin.pages.*', 'label' => 'الصفحات', 'icon' => 'layers', 'can' => \App\Enums\Permission::ManagePages->value],
        ['route' => 'admin.orders.index', 'active' => 'admin.orders.*', 'label' => 'الطلبات', 'icon' => 'bag', 'can' => \App\Enums\Permission::ManageOrders->value],
        ['route' => 'admin.products.index', 'active' => 'admin.products.*', 'label' => 'المنتجات', 'icon' => 'box', 'can' => \App\Enums\Permission::ManageProducts->value],
        ['route' => 'admin.analytics.index', 'active' => 'admin.analytics.*', 'label' => 'التحليلات', 'icon' => 'chart', 'can' => \App\Enums\Permission::ViewAnalytics->value],
        ['route' => 'admin.tracking.edit', 'active' => 'admin.tracking.*', 'label' => 'التتبع', 'icon' => 'pulse', 'can' => \App\Enums\Permission::ManageTracking->value],
        ['route' => 'admin.settings.edit', 'active' => 'admin.settings.*', 'label' => 'الإعدادات', 'icon' => 'cog', 'can' => \App\Enums\Permission::ManageSettings->value],
    ];
    // Bottom bar (mobile) keeps only the five primary destinations.
    $bottomNav = collect($nav)->whereIn('route', [
        'admin.dashboard', 'admin.pages.index', 'admin.orders.index', 'admin.tracking.edit', 'admin.settings.edit',
    ])->all();
    $canSee = fn ($item) => Route::has($item['route']) && (! $item['can'] || auth()->user()->can($item['can']));
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <meta name="theme-color" content="#4f46e5">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-100">
    <div class="lg:flex lg:min-h-dvh">

        {{-- ================= Desktop sidebar (RTL → appears on the right) ================= --}}
        <aside class="hidden lg:sticky lg:top-0 lg:flex lg:h-dvh lg:w-64 lg:flex-none lg:flex-col lg:border-l lg:border-slate-200 lg:bg-white">
            <div class="flex items-center gap-2 px-5 py-5">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-600 text-sm font-black text-white">45</span>
                <div class="leading-tight">
                    <div class="text-sm font-black text-slate-900">{{ config('app.name') }}</div>
                    <div class="text-[11px] text-slate-400">لوحة الإدارة</div>
                </div>
            </div>

            <nav class="flex-1 space-y-1 px-3">
                @foreach ($nav as $item)
                    @continue(! $canSee($item))
                    @php($isActive = request()->routeIs($item['active']))
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $isActive ? 'bg-brand-50 text-brand-700' : 'text-slate-500 hover:bg-slate-50' }}">
                        <x-nav-icon :name="$item['icon']" />
                        {{ $item['label'] }}
                    </a>
                @endforeach

                @can(\App\Enums\Permission::ManageUsers->value)
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.users.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-500 hover:bg-slate-50' }}">
                        <x-nav-icon name="users" /> المستخدمون
                    </a>
                @endcan
                @if (Route::has('admin.audit-logs.index') && auth()->user()->can(\App\Enums\Permission::ManageSettings->value))
                    <a href="{{ route('admin.audit-logs.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.audit-logs.*') ? 'bg-brand-50 text-brand-700' : 'text-slate-500 hover:bg-slate-50' }}">
                        <x-nav-icon name="clock" /> سجل النشاط
                    </a>
                @endif
            </nav>

            <div class="border-t border-slate-100 p-3">
                <div class="flex items-center gap-3 rounded-xl px-3 py-2">
                    <span class="grid h-9 w-9 flex-none place-items-center rounded-xl bg-slate-100 text-sm font-bold text-slate-600">{{ mb_substr(auth()->user()->name ?? '؟', 0, 1) }}</span>
                    <div class="min-w-0 leading-tight">
                        <div class="truncate text-sm font-bold text-slate-800">{{ auth()->user()->name }}</div>
                        <div class="truncate text-[11px] text-slate-400" dir="ltr">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mt-1 w-full rounded-xl px-3 py-2 text-right text-sm font-semibold text-rose-600 hover:bg-rose-50">تسجيل الخروج</button>
                </form>
            </div>
        </aside>

        {{-- ================= Main column ================= --}}
        <div class="flex min-h-dvh min-w-0 flex-1 flex-col bg-slate-50">
            {{-- Top bar --}}
            <header class="safe-top sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div class="mx-auto flex w-full {{ $contentMax }} items-center justify-between px-4 py-3 lg:px-8">
                    <div class="flex items-center gap-2">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-sm font-black text-white lg:hidden">45</span>
                        <div class="leading-tight">
                            <div class="text-sm font-bold text-slate-900 lg:text-lg">{{ $heading }}</div>
                            <div class="text-[11px] text-slate-400 lg:hidden">{{ config('app.name') }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        {{ $actions }}
                        {{-- Profile dropdown (mobile-only; desktop uses the sidebar footer) --}}
                        <div x-data="{ open: false }" class="relative lg:hidden">
                            <button @click="open = !open" class="grid h-9 w-9 place-items-center rounded-xl bg-slate-100 text-slate-600">
                                <span class="text-sm font-bold">{{ mb_substr(auth()->user()->name ?? '؟', 0, 1) }}</span>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false"
                                 class="absolute left-0 mt-2 w-52 rounded-2xl border border-slate-100 bg-white p-2 shadow-xl">
                                <div class="px-3 py-2 text-xs text-slate-400" dir="ltr">{{ auth()->user()->email }}</div>
                                @can(\App\Enums\Permission::ManageUsers->value)
                                    <a href="{{ route('admin.users.index') }}" class="block rounded-xl px-3 py-2 text-sm hover:bg-slate-50">المستخدمون</a>
                                @endcan
                                @if (Route::has('admin.audit-logs.index') && auth()->user()->can(\App\Enums\Permission::ManageSettings->value))
                                    <a href="{{ route('admin.audit-logs.index') }}" class="block rounded-xl px-3 py-2 text-sm hover:bg-slate-50">سجل النشاط</a>
                                @endif
                                @if (Route::has('admin.products.index'))
                                    <a href="{{ route('admin.products.index') }}" class="block rounded-xl px-3 py-2 text-sm hover:bg-slate-50">المنتجات</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="mt-1 block w-full rounded-xl px-3 py-2 text-right text-sm font-semibold text-rose-600 hover:bg-rose-50">تسجيل الخروج</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            @if (session('status') || session('success') || session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     class="mx-auto mt-3 w-full {{ $contentMax }} rounded-2xl px-4 py-3 text-sm font-semibold lg:px-8
                        {{ session('error') ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-100' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' }}">
                    {{ session('status') ?? session('success') ?? session('error') }}
                </div>
            @endif

            <main class="mx-auto w-full {{ $contentMax }} flex-1 px-4 pb-28 pt-4 lg:px-8 lg:pb-10">
                {{ $slot }}
            </main>
        </div>

        {{-- ================= Mobile bottom navigation ================= --}}
        <nav class="safe-bottom fixed inset-x-0 bottom-0 z-30 mx-auto max-w-[480px] border-t border-slate-200 bg-white/95 px-2 pt-2 backdrop-blur lg:hidden">
            <div class="grid grid-cols-5 gap-1">
                @foreach ($bottomNav as $item)
                    @continue(! $canSee($item))
                    @php($isActive = request()->routeIs($item['active']))
                    <a href="{{ route($item['route']) }}"
                       class="flex flex-col items-center gap-1 rounded-2xl px-1 py-1.5 text-[11px] font-semibold transition {{ $isActive ? 'text-brand-600' : 'text-slate-400' }}">
                        <span class="grid h-8 w-8 place-items-center rounded-xl {{ $isActive ? 'bg-brand-50' : '' }}">
                            <x-nav-icon :name="$item['icon']" />
                        </span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
