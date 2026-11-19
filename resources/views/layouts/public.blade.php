<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Layanan' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <!-- Google Fonts: Inter Profesional (Non-blocking async load) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    </noscript>
    <script>
        (function() {
            try {
                document.documentElement.classList.remove('dark');
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F5F8FC] text-[#101828] min-h-screen flex flex-col font-sans antialiased">
    <!-- Top Loading Progress Bar untuk Smooth Navigation Feedback -->
    <div id="app-top-progress" class="fixed top-0 left-0 h-[2.5px] bg-[#062072] dark:bg-[#3B82F6] z-[9999] pointer-events-none transition-all duration-300 opacity-0 w-0" aria-hidden="true"></div>
    <!-- Public Navigation Bar -->
    <header class="bg-[#062072] text-white border-b border-[#04184F] shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 no-underline text-white">
                <div class="w-8 h-8 rounded-lg bg-[#0B3A9E] flex items-center justify-center text-white shrink-0 shadow-xs">
                    <x-icons.icon name="branch" class="w-4.5 h-4.5 text-white" />
                </div>
                <div class="leading-tight">
                    <span class="font-bold text-sm tracking-wide uppercase block">SIMAUL</span>
                    <span class="text-[10px] text-[#DCE8FF] font-medium tracking-normal block">Sistem Informasi Manajemen Usaha Layanan</span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                @auth
                    @php
                        $cleanUserName = preg_replace('/\s*\(.*?\)\s*$/', '', auth()->user()->name);
                    @endphp
                    <span class="text-xs text-[#DCE8FF] hidden sm:inline">Halo, {{ $cleanUserName }}</span>
                    @php
                        $userRole = auth()->user()->role?->name;
                        $dashboardRoute = match($userRole) {
                            'owner' => route('owner.dashboard'),
                            'manager' => route('manager.dashboard'),
                            'cashier' => route('cashier.dashboard'),
                            'employee' => route('employee.dashboard'),
                            default => null,
                        };
                    @endphp

                    @if($dashboardRoute)
                        <x-buttons.btn variant="secondary" href="{{ $dashboardRoute }}">
                            Portal Dashboard
                        </x-buttons.btn>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <x-buttons.btn variant="outline" type="submit">
                            Keluar
                        </x-buttons.btn>
                    </form>
                @else
                    <x-buttons.btn variant="outline" href="{{ route('login') }}">
                        Masuk
                    </x-buttons.btn>
                    <x-buttons.btn variant="secondary" href="{{ route('register') }}">
                        Daftar Pelanggan
                    </x-buttons.btn>
                @endauth
            </div>
        </div>
    </header>

    <!-- Public Body Content Slot -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-[#E4E7EC] py-6 text-center text-xs text-[#667085] mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            <p class="m-0">&copy; {{ date('Y') }} SIMAUL — Sistem Informasi Manajemen Usaha Layanan. Hak cipta dilindungi.</p>
        </div>
    </footer>
</body>
</html>
