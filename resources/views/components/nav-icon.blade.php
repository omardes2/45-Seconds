@props(['name'])
@php
    $paths = [
        'home' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>',
        'layers' => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/>',
        'bag' => '<path d="M6 8h12l-1 12H7L6 8Z"/><path d="M9 8a3 3 0 0 1 6 0"/>',
        'pulse' => '<path d="M3 12h4l2-6 4 12 2-6h6"/>',
        'cog' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3M4.2 4.2l2.1 2.1m11.4 11.4 2.1 2.1M2 12h3m14 0h3M4.2 19.8l2.1-2.1m11.4-11.4 2.1-2.1"/>',
        'box' => '<path d="M12 3 21 8v8l-9 5-9-5V8l9-5Z"/><path d="M3 8l9 5 9-5"/><path d="M12 13v8"/>',
        'chart' => '<path d="M4 20V10m6 10V4m6 16v-6"/>',
        'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M16 6a3 3 0 0 1 0 6m5 8a5 5 0 0 0-4-5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    ];
@endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
    {!! $paths[$name] ?? $paths['home'] !!}
</svg>
