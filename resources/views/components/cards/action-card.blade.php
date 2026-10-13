@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'href' => null,
])

@php
    $baseClasses = 'py-3 px-3.5 sm:py-3.5 sm:px-4 bg-white border border-[#E4E7EC] rounded-xl shadow-2xs hover:border-[#062072] hover:shadow-sm transition-all duration-200 cursor-pointer flex items-center gap-3 text-left h-full no-underline';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
            @if ($icon)
                <x-icons.icon :name="$icon" class="w-4.5 h-4.5 text-[#062072]" />
            @else
                {{ $slotIcon ?? $slot }}
            @endif
        </div>
        <div class="min-w-0 flex-1">
            <h5 class="text-[13px] sm:text-sm font-semibold text-[#101828] m-0 leading-tight truncate">{{ $title }}</h5>
            @if ($subtitle)
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 leading-snug truncate">{{ $subtitle }}</p>
            @endif
        </div>
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $baseClasses . ' w-full']) }}>
        <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
            @if ($icon)
                <x-icons.icon :name="$icon" class="w-4.5 h-4.5 text-[#062072]" />
            @else
                {{ $slotIcon ?? $slot }}
            @endif
        </div>
        <div class="min-w-0 flex-1">
            <h5 class="text-[13px] sm:text-sm font-semibold text-[#101828] m-0 leading-tight truncate">{{ $title }}</h5>
            @if ($subtitle)
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 leading-snug truncate">{{ $subtitle }}</p>
            @endif
        </div>
    </button>
@endif
