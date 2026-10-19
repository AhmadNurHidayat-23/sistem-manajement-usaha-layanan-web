<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <!-- Google Fonts: Inter Profesional (Asynchronous Non-render-blocking) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    </noscript>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F5F8FC] text-[#101828] font-sans antialiased h-screen overflow-hidden">
    <!-- Sidebar Navigasi Berdasarkan Role -->
    @php
        $isCollapsed = request()->cookie('sidebar_collapsed') === '1';
        $sidebarPartial = match(auth()->user()?->role?->name) {
            'owner' => 'layouts.partials.sidebar.owner',
            'manager' => 'layouts.partials.sidebar.manager',
            'cashier' => 'layouts.partials.sidebar.kasir',
            default => 'layouts.partials.sidebar.karyawan',
        };
    @endphp
    @include($sidebarPartial, ['isCollapsed' => $isCollapsed])

    <!-- Backdrop Overlay untuk Mobile/Tablet Drawer -->
    <div id="sidebar-backdrop"
         class="fixed inset-0 bg-[#04184F]/60 backdrop-blur-xs z-25 opacity-0 pointer-events-none lg:hidden"
         aria-hidden="true"></div>

    <!-- Area Utama: Header tetap + Content scroll -->
    <div class="main-area flex flex-col h-screen overflow-hidden {{ $isCollapsed ? 'sidebar-collapsed' : '' }}" id="main-area">
        <!-- Header / Topbar: Tetap di atas (h-14 / 56px) -->
        @include('layouts.partials.topbar', ['title' => $title ?? 'Dashboard'])

        <!-- Area Konten Utama: HANYA area ini yang scroll -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-5 bg-[#F5F8FC]">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
