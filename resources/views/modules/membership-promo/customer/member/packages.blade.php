@extends('layouts.app', ['title' => 'Paket Berlangganan & Loyalitas Member'])

@section('content')
<div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                @if (auth()->user()?->role?->name === 'customer')
                    <a href="{{ route('members.my-membership') }}" class="text-xs text-[#062072] hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali ke Member Saya
                    </a>
                @else
                    <a href="{{ route('members.index') }}" class="text-xs text-[#062072] hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali ke Daftar Member
                    </a>
                @endif
            </div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Tingkat Keanggotaan & Paket Berlangganan</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Skema reward loyalty dan tingkat keuntungan bertingkat berdasarkan akumulasi poin transaksi pelanggan.</p>
        </div>
        <div class="flex items-center gap-2">
            @if (auth()->user()?->role?->name === 'customer' && ! auth()->user()?->membership)
                <form action="{{ route('members.register-self') }}" method="POST">
                    @csrf
                    <x-buttons.btn type="submit" variant="primary" class="gap-1.5 text-xs shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Daftar Member Sekarang
                    </x-buttons.btn>
                </form>
            @endif
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif

    <!-- Package Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach ($packages as $pkg)
            @php
                $isPopular = $pkg['popular'] ?? false;
                $currentPoints = auth()->user()?->membership?->loyaltyPoint?->total_earned ?? 0;
                $isCurrentTier = $currentPoints >= $pkg['min_points'];
            @endphp
            <div class="relative bg-white rounded-xl border {{ $isPopular ? 'border-[#062072] shadow-md ring-2 ring-[#062072]/20' : 'border-[#E4E7EC] shadow-xs hover:border-[#062072]' }} hover:shadow-md hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between overflow-hidden">
                @if ($isPopular)
                    <div class="bg-[#062072] text-white text-[10px] font-bold uppercase tracking-wider py-1 text-center">
                        Paling Populer
                    </div>
                @endif

                <div class="p-5 flex-1 flex flex-col">
                    <!-- Badge & Level -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full
                            @if($pkg['level'] === 'Platinum') bg-[#04184F] text-[#DCE8FF] border border-[#0B3A9E]/40
                            @elseif($pkg['level'] === 'Gold') bg-amber-100 text-amber-800
                            @elseif($pkg['level'] === 'Silver') bg-[#DCE8FF] text-[#062072] border border-[#0B3A9E]/20
                            @else bg-slate-100 text-slate-800 @endif">
                            {{ $pkg['level'] }}
                        </span>
                        <span class="text-[11px] font-medium text-[#667085]">
                            Min. {{ number_format($pkg['min_points']) }} Poin
                        </span>
                    </div>

                    <!-- Package Title & Desc -->
                    <h3 class="text-base font-bold text-[#101828] mb-1.5">{{ $pkg['name'] }}</h3>
                    <p class="text-xs text-[#667085] leading-relaxed mb-4">{{ $pkg['description'] }}</p>

                    <div class="border-t border-[#E4E7EC] pt-4 mt-auto">
                        <p class="text-[11px] font-semibold text-[#101828] uppercase tracking-wider mb-2.5">Keuntungan Paket:</p>
                        <ul class="space-y-2 text-xs text-[#667085] p-0 m-0 list-none">
                            @foreach ($pkg['features'] as $feature)
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="leading-tight">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Footer Card Action -->
                <div class="p-4 bg-[#F8F9FC] border-t border-[#E4E7EC]">
                    @if (auth()->user()?->role?->name === 'customer')
                        @if (auth()->user()?->membership)
                            <div class="text-center">
                                @if($isCurrentTier)
                                    <span class="inline-flex items-center justify-center gap-1 text-[11px] font-bold text-emerald-700">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Tingkat Aktif Terbuka
                                    </span>
                                @else
                                    <span class="text-[11px] font-medium text-[#667085]">
                                        Kumpulkan {{ number_format($pkg['min_points'] - $currentPoints) }} poin lagi
                                    </span>
                                @endif
                            </div>
                        @else
                            <form action="{{ route('members.register-self') }}" method="POST">
                                @csrf
                                <x-buttons.btn type="submit" variant="{{ $isPopular ? 'primary' : 'outline' }}" class="w-full text-xs py-1.5 justify-center">
                                    Daftar Member Sekarang
                                </x-buttons.btn>
                            </form>
                        @endif
                    @else
                        <div class="text-center text-[11px] text-[#667085] font-medium">
                            Skema Loyalty Otomatis POS
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- FAQ / Loyalty Rules Information Box -->
    <x-cards.card class="p-6">
        <h3 class="text-sm font-bold text-[#101828] mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Panduan & Ketentuan Poin Loyalitas
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs text-[#667085]">
            <div class="space-y-1.5">
                <h4 class="font-bold text-[#101828] text-xs m-0">1. Bagaimana cara memperoleh poin?</h4>
                <p class="leading-relaxed m-0">
                    Setiap transaksi pembayaran servis atau belanja layanan di kasir cabang kami akan otomatis menambahkan 1 poin untuk setiap pembelanjaan kelipatan Rp 10.000.
                </p>
            </div>

            <div class="space-y-1.5">
                <h4 class="font-bold text-[#101828] text-xs m-0">2. Kapan tier loyalitas naik?</h4>
                <p class="leading-relaxed m-0">
                    Sistem secara otomatis mengevaluasi total perolehan poin Anda. Ketika akumulasi poin mencapai batas minimum tier, status membership akan otomatis ter-upgrade ke Silver, Gold, atau Platinum.
                </p>
            </div>

            <div class="space-y-1.5">
                <h4 class="font-bold text-[#101828] text-xs m-0">3. Bagaimana cara menukarkan poin?</h4>
                <p class="leading-relaxed m-0">
                    Pelanggan dapat langsung menukarkan poin melalui portal keanggotaan online atau memberitahukan kasir saat pembayaran untuk memotong tagihan belanja.
                </p>
            </div>
        </div>
    </x-cards.card>
</div>
@endsection
