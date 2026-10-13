@props([
    'variant' => 'primary',
])

@php
    $variants = [
        'primary' => 'bg-[#DCE8FF] text-[#062072] border-[#0B3A9E]/20',
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200',
        'neutral' => 'bg-slate-100 text-slate-700 border-slate-200',
    ];

    $classes = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border ' . ($variants[$variant] ?? $variants['neutral']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
