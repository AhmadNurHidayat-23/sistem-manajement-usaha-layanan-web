@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $sizeClasses = match($size) {
        'sm' => 'px-2.5 py-1 text-[11px] sm:text-xs h-[30px] rounded-md gap-1.5',
        'lg' => 'px-4 py-2.5 text-sm h-[40px] rounded-lg gap-2',
        default => 'px-3.5 py-1.5 text-xs sm:text-[13px] h-[34px] rounded-lg gap-2',
    };

    $baseClasses = 'inline-flex items-center justify-center font-semibold transition-all duration-150 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-1 border border-transparent shadow-2xs cursor-pointer whitespace-nowrap ' . $sizeClasses;

    $variants = [
        'primary' => 'bg-[#062072] hover:bg-[#04184F] text-white focus:ring-[#062072]',
        'secondary' => 'bg-[#EAF2FF] hover:bg-[#D5E5FD] text-[#062072] border-[#C2D8FC] focus:ring-[#062072]',
        'outline' => 'bg-[#EAF2FF] hover:bg-[#D5E5FD] text-[#062072] border-[#C2D8FC] focus:ring-[#062072]',
        'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white focus:ring-emerald-600',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white focus:ring-rose-600',
        'disabled' => 'bg-[#F2F4F7] text-[#98A2B3] border-[#EAECF0] cursor-not-allowed opacity-75 focus:ring-transparent shadow-none',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
