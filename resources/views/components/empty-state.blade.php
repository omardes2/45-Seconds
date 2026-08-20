@props(['title' => 'لا توجد بيانات بعد', 'subtitle' => null])
<div class="flex flex-col items-center justify-center rounded-card bg-white px-6 py-12 text-center ring-1 ring-slate-100">
    <div class="mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-7 w-7">
            <path d="M4 7h16M4 12h16M4 17h10" stroke-linecap="round"/>
        </svg>
    </div>
    <h3 class="text-base font-bold text-slate-700">{{ $title }}</h3>
    @if ($subtitle)<p class="mt-1 text-sm text-slate-400">{{ $subtitle }}</p>@endif
    @if (! $slot->isEmpty())<div class="mt-4">{{ $slot }}</div>@endif
</div>
