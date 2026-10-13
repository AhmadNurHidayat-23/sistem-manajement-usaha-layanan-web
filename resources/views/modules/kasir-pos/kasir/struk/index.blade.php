<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi - {{ $transaction->transaction_code }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="receipt-print-body bg-[#F5F8FC] min-h-screen py-6 sm:py-10 px-4 flex flex-col items-center justify-start text-[#101828] font-sans antialiased">

    <!-- Container Utama -->
    <div class="w-full max-w-2xl mx-auto flex flex-col items-center">

        <!-- Flash Alerts (Not Printed) -->
        @if (session('success'))
            <div class="no-print w-full mb-4">
                <x-alerts.alert type="success" :message="session('success')" />
            </div>
        @endif
        @if (session('error'))
            <div class="no-print w-full mb-4">
                <x-alerts.alert type="danger" :message="session('error')" />
            </div>
        @endif

        <!-- Header Halaman (Not Printed) -->
        <header class="no-print w-full text-center mb-6">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#EEF4FF] text-[#062072] border border-[#C7DCFF] mb-2">
                <svg class="w-3.5 h-3.5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>{{ $transaction->branch->display_name ?? 'SIMAUL' }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-[#062072] tracking-tight m-0">Struk Transaksi</h1>
            <p class="text-xs sm:text-sm text-[#475467] mt-1 m-0">Rincian pembayaran dan bukti transaksi pelanggan</p>
        </header>

        <!-- Action Bar (Not Printed) -->
        @include('modules.kasir-pos.kasir.struk.partials.action-bar')

        <!-- Area Struk Transaksi (Printable Card) -->
        <main class="w-full flex justify-center">
            <div class="receipt-box w-full max-w-[380px] bg-white border border-[#E4E7EC] rounded-2xl shadow-sm p-6 sm:p-7 font-sans text-xs text-[#101828]">
                @include('modules.kasir-pos.kasir.struk.partials.header')
                @include('modules.kasir-pos.kasir.struk.partials.items')
                @include('modules.kasir-pos.kasir.struk.partials.payment')
                @include('modules.kasir-pos.kasir.struk.partials.footer')
            </div>
        </main>

    </div>

</body>
</html>
