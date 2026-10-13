@props([
    'title' => null,
    'subtitle' => null,
    'action' => null,
    'noPadding' => false,
    'overflowVisible' => false,
])

@php
    $hasCustomPadding = preg_match('/\bp[xy]?-|\bp-\d/', $attributes->get('class', ''));
    $bodyPadding = ($noPadding || ($hasCustomPadding && !($title || $action))) ? '' : 'p-4 sm:p-5';
    $isOverflowHidden = ! $overflowVisible && ! str_contains($attributes->get('class', ''), 'overflow-visible') && ($title || $action);
    $overflowClass = $isOverflowHidden ? 'overflow-hidden' : 'overflow-visible';
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-[#E4E7EC] shadow-2xs ' . $overflowClass]) }}>
    @if ($title || $action)
        <div class="px-4 sm:px-5 py-3 border-b border-[#E4E7EC] flex items-center justify-between gap-3 bg-white">
            <div class="min-w-0">
                @if ($title)
                    <h3 class="text-sm font-bold text-[#101828] m-0 truncate">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-[#667085] mt-0.5 m-0 truncate">{{ $subtitle }}</p>
                @endif
            </div>
            @if ($action)
                <div class="shrink-0">
                    {{ $action }}
                </div>
            @endif
        </div>
        <div class="{{ $bodyPadding }}">
            {{ $slot }}
        </div>
    @else
        <div class="{{ $bodyPadding }}">
            {{ $slot }}
        </div>
    @endif
</div>
