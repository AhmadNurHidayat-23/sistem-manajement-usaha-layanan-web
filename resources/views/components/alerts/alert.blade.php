@props([
    'type' => 'success',
    'message' => null,
])

@php
    $types = [
        'success' => [
            'classes' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        ],
        'danger' => [
            'classes' => 'bg-rose-50 border-rose-200 text-rose-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        ],
        'error' => [
            'classes' => 'bg-rose-50 border-rose-200 text-rose-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        ],
        'warning' => [
            'classes' => 'bg-amber-50 border-amber-200 text-amber-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
        ],
        'info' => [
            'classes' => 'bg-blue-50 border-blue-200 text-[#062072]',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        ],
    ];

    $cfg = $types[$type] ?? $types['info'];
@endphp

<div {{ $attributes->merge(['class' => 'border px-4 py-3 rounded-lg text-xs font-medium flex items-center gap-2.5 ' . $cfg['classes']]) }}>
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {!! $cfg['icon'] !!}
    </svg>
    <div class="flex-1">
        {{ $message ?? $slot }}
    </div>
</div>
