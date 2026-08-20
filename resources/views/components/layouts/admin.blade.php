@props([
    'title' => 'لوحة التحكم',
    'heading' => 'لوحة التحكم',
    'actions' => null,
])
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <meta name="theme-color" content="#4f46e5">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-100">
    <div class="phone-shell relative flex min-h-dvh flex-col bg-slate-50">
        <header class="safe-top sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-sm font-black text-white">45</span>
                    <div class="leading-tight">
                        <div class="text-sm font-bold text-slate-900">{{ $heading }}</div>
                        <div class="text-[11px] text-slate-400">{{ config('app.name') }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    {{ $actions }}
                    <div x-data="{ open: false }" class="relative">
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
                 class="mx-4 mt-3 rounded-2xl px-4 py-3 text-sm font-semibold
                    {{ session('error') ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-100' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' }}">
                {{ session('status') ?? session('success') ?? session('error') }}
            </div>
        @endif

        <main class="flex-1 px-4 pb-28 pt-4">
            {{ $slot }}
        </main>

        <nav class="safe-bottom fixed inset-x-0 bottom-0 z-30 mx-auto max-w-[480px] border-t border-slate-200 bg-white/95 px-2 pt-2 backdrop-blur">
            <div class="grid grid-cols-5 gap-1">
                @php($nav = [
                    ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => 'الرئيسية', 'icon' => 'home', 'can' => null],
                    ['route' => 'admin.pages.index', 'active' => 'admin.pages.*', 'label' => 'الصفحات', 'icon' => 'layers', 'can' => \App\Enums\Permission::ManagePages->value],
                    ['route' => 'admin.orders.index', 'active' => 'admin.orders.*', 'label' => 'الطلبات', 'icon' => 'bag', 'can' => \App\Enums\Permission::ManageOrders->value],
                    ['route' => 'admin.tracking.edit', 'active' => 'admin.tracking.*', 'label' => 'التتبع', 'icon' => 'pulse', 'can' => \App\Enums\Permission::ManageTracking->value],
                    ['route' => 'admin.settings.edit', 'active' => 'admin.settings.*', 'label' => 'الإعدادات', 'icon' => 'cog', 'can' => \App\Enums\Permission::ManageSettings->value],
                ])
                @foreach ($nav as $item)
                    @continue(! Route::has($item['route']))
                    @continue($item['can'] && auth()->user()->cannot($item['can']))
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
