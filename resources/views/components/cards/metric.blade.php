@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'variant' => 'primary',
    'valueClass' => null,
])

@php
    $styles = [
        'primary' => [
            'icon_bg' => 'bg-[#DCE8FF] text-[#062072]',
        ],
        'emerald' => [
            'icon_bg' => 'bg-[#DCE8FF] text-[#062072]',
        ],
        'navy' => [
            'icon_bg' => 'bg-[#DCE8FF] text-[#062072]',
        ],
        'amber' => [
            'icon_bg' => 'bg-[#DCE8FF] text-[#062072]',
        ],
    ];

    $cfg = $styles[$variant] ?? $styles['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full']) }}>
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-[#667085] m-0 truncate">{{ $title }}</p>
            <h3 class="{{ $valueClass ?? 'text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight' }}">{{ $value }}</h3>
            @if ($subtitle)
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug font-normal">{{ $subtitle }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl {{ $cfg['icon_bg'] }} flex items-center justify-center shrink-0">
                <x-icons.icon :name="$icon" class="w-[18px] h-[18px]" />
            </div>
        @endif
    </div>
</div>


