@props(['color' => 'slate', 'label' => ''])
@php
    $map = [
        'slate' => 'bg-slate-100 text-slate-600',
        'zinc' => 'bg-zinc-100 text-zinc-600',
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'rose' => 'bg-rose-100 text-rose-700',
        'sky' => 'bg-sky-100 text-sky-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
        'violet' => 'bg-violet-100 text-violet-700',
        'cyan' => 'bg-cyan-100 text-cyan-700',
        'orange' => 'bg-orange-100 text-orange-700',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold '.($map[$color] ?? $map['slate'])]) }}>
    {{ $label ?: $slot }}
</span>
