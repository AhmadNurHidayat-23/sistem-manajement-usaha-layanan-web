<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SIMAUL - Sistem Informasi Manajemen Usaha Layanan' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-[#101828] min-h-screen flex flex-col font-sans antialiased selection:bg-[#DCE8FF] selection:text-[#062072]">

    @php
        $isHome = request()->routeIs('customer.home');
    @endphp

    @include('layouts.partials.pelanggan-header')

    <main class="flex-1 w-full {{ $isHome ? 'pt-0' : 'pt-24 sm:pt-28 pb-12' }}">
        @yield('content')
    </main>

    @include('layouts.partials.pelanggan-footer')

    @stack('scripts')
</body>
</html>
