@props([
    'variant' => 'default', // 'default', 'topbar', 'landing', 'mobile'
])

@php
    $locale = app()->getLocale();
    $label = $locale === 'en' ? 'EN 🇬🇧' : 'ID 🇮🇩';
    $title = $locale === 'en' ? 'Ganti ke Bahasa Indonesia' : 'Switch to English';

    $baseClasses = 'inline-flex items-center justify-center font-semibold text-xs transition-all cursor-pointer select-none border';

    $variantClasses = match($variant) {
        'topbar' => 'h-9 px-2.5 rounded-lg text-[#101828] dark:text-[#F8FAFC] bg-white dark:bg-[#0E172E] border border-[#E4E7EC] dark:border-[#1E2D4A] hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] shrink-0',
        'landing' => 'h-9 px-2.5 rounded-lg text-white border-white/30 hover:bg-white/10 shrink-0',
        'mobile' => 'w-full h-10 px-3 rounded-lg text-[#101828] dark:text-[#F8FAFC] bg-[#F5F8FC] dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] hover:bg-neutral-100 dark:hover:bg-[#1E2D4A]',
        default => 'h-9 px-2.5 rounded-lg text-[#101828] dark:text-[#F8FAFC] border border-[#E4E7EC] dark:border-[#1E2D4A] hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] shrink-0',
    };
@endphp

<button type="button"
        {{ $attributes->merge(['class' => "$baseClasses $variantClasses btn-switch-locale"]) }}
        aria-label="{{ $title }}"
        title="{{ $title }}">
    <span class="locale-label tracking-wide">{{ $label }}</span>
</button>
