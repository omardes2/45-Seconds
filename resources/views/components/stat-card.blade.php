@props(['label', 'value', 'sub' => null, 'tone' => 'brand'])
@php
    $tones = [
        'brand' => 'from-brand-500 to-brand-700 text-white',
        'green' => 'from-emerald-500 to-emerald-700 text-white',
        'slate' => 'from-slate-700 to-slate-900 text-white',
        'plain' => 'bg-white text-slate-900 ring-1 ring-slate-100',
    ];
    $isGradient = $tone !== 'plain';
@endphp
<div class="rounded-card p-4 shadow-sm {{ $isGradient ? 'bg-gradient-to-br '.$tones[$tone] : $tones['plain'] }}">
    <div class="text-xs font-semibold {{ $isGradient ? 'text-white/80' : 'text-slate-400' }}">{{ $label }}</div>
    <div class="mt-1 text-2xl font-black tracking-tight">{{ $value }}</div>
    @if ($sub)
        <div class="mt-0.5 text-[11px] {{ $isGradient ? 'text-white/70' : 'text-slate-400' }}">{{ $sub }}</div>
    @endif
</div>
