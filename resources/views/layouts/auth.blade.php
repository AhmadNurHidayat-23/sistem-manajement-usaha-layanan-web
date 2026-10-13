<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Autentikasi' }} - SIMAUL</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-[#101828] min-h-screen lg:h-screen lg:overflow-hidden flex flex-col font-sans antialiased selection:bg-[#DCE8FF] selection:text-[#062072]">

    <div class="min-h-screen lg:h-screen w-full flex flex-col lg:flex-row lg:overflow-hidden">

        <!-- Left Panel: Branding (Desktop Only) — exact 50% -->
        <div class="lg:w-1/2 relative hidden lg:flex flex-col justify-center items-center p-10 xl:p-14 bg-[#04184F] text-white overflow-hidden shrink-0 h-full">
            <!-- Background Image with Navy Overlay -->
            <div class="absolute inset-0 z-0 pointer-events-none">
                <img src="{{ asset('images/laundry-hero.jpg') }}" alt="SIMAUL Operasional" class="w-full h-full object-cover opacity-20 mix-blend-luminosity">
                <div class="absolute inset-0 bg-gradient-to-b from-[#062072]/95 via-[#04184F]/90 to-[#04184F]"></div>
            </div>

            <!-- Centered Content Block -->
            <div class="relative z-10 w-full max-w-md space-y-6">
                <!-- Logo Brand -->
                <a href="{{ url('/') }}" class="inline-block no-underline">
                    <img src="{{ asset('images/brand/logo-header.png') }}" alt="SIMAUL - Sistem Informasi Manajemen Usaha Layanan"
                        class="h-14 xl:h-16 w-auto object-contain brightness-0 invert">
                </a>

                <!-- Value Proposition -->
                <div class="space-y-3">
                    <h1 class="text-2xl xl:text-3xl font-bold text-white tracking-tight leading-snug m-0">
                        Kelola dan Nikmati Seluruh Layanan Usaha dengan Lebih Mudah.
                    </h1>
                    <p class="text-sm text-[#DCE8FF]/80 leading-relaxed m-0">
                        Akses akun Anda untuk memesan layanan, memantau proses pengerjaan, dan menikmati kemudahan reservasi di seluruh cabang operasional.
                    </p>
                </div>

                <!-- Feature Bullets -->
                <div class="pt-4 border-t border-white/10 flex flex-col gap-2.5 text-xs text-[#DCE8FF]/85">
                    <div class="flex items-center gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#DCE8FF] shrink-0"></span>
                        <span>Reservasi janji temu dan pesanan drop-off terpadu</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#DCE8FF] shrink-0"></span>
                        <span>Estimasi waktu pengerjaan akurat dan terpantau real-time</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#DCE8FF] shrink-0"></span>
                        <span>Dukungan pembayaran digital, kasir POS, dan poin loyalitas</span>
                    </div>
                </div>

                <!-- Return Link -->
                <div class="pt-4">
                    <a href="{{ url('/') }}" class="text-xs text-[#DCE8FF]/70 hover:text-white no-underline transition-colors font-medium">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Panel: Form Area — exact 50% -->
        <div class="lg:w-1/2 flex flex-col min-h-screen lg:min-h-0 lg:h-screen overflow-y-auto bg-[#F5F8FC]/50">
            <!-- Mobile Brand Bar -->
            <div class="lg:hidden flex items-center justify-between p-4 sm:p-6 border-b border-[#E4E7EC] shrink-0 bg-white">
                <a href="{{ url('/') }}" class="inline-block no-underline">
                    <img src="{{ asset('images/brand/logo-header.png') }}" alt="SIMAUL" class="h-8 w-auto object-contain">
                </a>
                <a href="{{ url('/') }}" class="text-xs font-medium text-[#667085] hover:text-[#062072] no-underline transition-colors">
                    Beranda
                </a>
            </div>

            <!-- Form Wrapper (Centered in Right Panel) -->
            <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-10">
                <div class="w-full max-w-[420px]">
                    <div class="bg-white rounded-2xl border border-[#E4E7EC] shadow-[0_4px_24px_-4px_rgba(16,24,40,0.06)] p-6 sm:p-8">
                        {{ $slot ?? '' }}
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
