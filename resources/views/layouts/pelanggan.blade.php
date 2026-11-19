<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SIMAUL - Sistem Informasi Manajemen Usaha Layanan' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    </noscript>
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('simaul_theme') || localStorage.getItem('simaul_landing_theme');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else if (theme === 'light') {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white dark:bg-[#080E1E] text-[#101828] dark:text-[#F8FAFC] min-h-screen flex flex-col font-sans antialiased selection:bg-[#DCE8FF] selection:text-[#062072]">
    <!-- Top Loading Progress Bar untuk Smooth Navigation Feedback -->
    <div id="app-top-progress" class="fixed top-0 left-0 h-[2.5px] bg-[#062072] dark:bg-[#3B82F6] z-[9999] pointer-events-none transition-all duration-300 opacity-0 w-0" aria-hidden="true"></div>

    @php
        $isHome = request()->routeIs('customer.home');
    @endphp

    @include('layouts.partials.pelanggan-header')

    <main class="flex-1 w-full {{ $isHome ? 'pt-0' : 'pt-20 sm:pt-24 pb-12' }}">
        @yield('content')
    </main>

    @include('layouts.partials.pelanggan-footer')

    {{-- Floating Dark Mode Button untuk Landing Page & Seluruh Area Pelanggan --}}
    <button id="landing-theme-toggle" type="button"
        class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 w-11 h-11 sm:w-12 sm:h-12 rounded-full shadow-lg bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] text-[#101828] dark:text-[#FBBF24] flex items-center justify-center z-40 transition-all hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#062072] cursor-pointer"
        aria-label="Toggle tema gelap atau terang"
        title="Toggle tema gelap atau terang">
        {{-- Moon Icon (tampil saat Light Mode) --}}
        <svg id="landing-theme-icon-moon" class="w-5 h-5 text-[#062072] dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
        {{-- Sun Icon (tampil saat Dark Mode) --}}
        <svg id="landing-theme-icon-sun" class="w-5 h-5 text-amber-400 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
    </button>

    @stack('scripts')
</body>
</html>
