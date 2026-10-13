@extends('layouts.pelanggan', ['title' => 'Member & Poin Loyalitas'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Member & Poin Loyalitas</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Pantau kartu member digital, akumulasi poin, dan tukar reward.</p>
        </div>
    </div>

    <!-- Alerts -->
    @foreach(['success', 'error', 'info'] as $msg)
        @if(session($msg))
        <div class="{{ $msg === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : ($msg === 'error' ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-blue-50 border-blue-200 text-blue-700') }} border px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            {{ session($msg) }}
        </div>
        @endif
    @endforeach
    @if ($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg">
            <ul class="list-disc pl-5 m-0 space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! $membership)
        <!-- Not a Member Yet: CTA -->
        <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs overflow-hidden">
            <div class="bg-gradient-to-r from-[#062072] to-[#0B3A9E] p-6 sm:p-8 text-center text-white">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-white/10 flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Anda Belum Menjadi Member</h3>
                <p class="text-sm text-[#DCE8FF] mb-5 max-w-md mx-auto">
                    Daftarkan diri Anda sebagai member untuk mendapatkan poin loyalitas, diskon eksklusif, dan berbagai keuntungan menarik lainnya!
                </p>
                <form method="POST" action="{{ route('customer.member.register') }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-white text-[#062072] rounded-lg text-sm font-bold hover:bg-[#DCE8FF] transition-colors cursor-pointer border-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Daftar Member Sekarang
                    </button>
                </form>
            </div>

            <!-- Benefits Preview -->
            <div class="p-5 sm:p-6">
                <h4 class="text-sm font-bold text-[#101828] mb-3">Keuntungan Menjadi Member</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach([
                        ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Dapat poin setiap transaksi Rp 10.000'],
                        ['icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z', 'text' => 'Voucher diskon eksklusif member'],
                        ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Tukar poin dengan potongan belanja'],
                        ['icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'text' => 'Naik level untuk benefit lebih besar'],
                    ] as $benefit)
                        <div class="flex items-start gap-3 p-3 bg-[#F5F8FC] rounded-lg">
                            <svg class="w-5 h-5 text-[#062072] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $benefit['icon'] }}"/>
                            </svg>
                            <span class="text-xs text-[#101828]">{{ $benefit['text'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <!-- Member Card & Points -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @include('modules.membership-promo.customer.member.partials.card')
            @include('modules.membership-promo.customer.member.partials.points-history')
        </div>

        <!-- Membership Tiers -->
        <div>
            <h3 class="text-sm font-bold text-[#101828] mb-4">Tingkat Keanggotaan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($packages as $pkg)
                    @php
                        $isCurrentTier = isset($currentTier) && $currentTier['level'] === $pkg['level'];
                    @endphp
                    <div class="bg-white rounded-xl border {{ $isCurrentTier ? 'border-[#062072] ring-2 ring-[#DCE8FF]' : 'border-[#E4E7EC]' }} shadow-xs p-4 relative">
                        @if ($isCurrentTier)
                            <span class="absolute -top-2.5 left-4 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-[#062072] text-white">
                                TIER ANDA
                            </span>
                        @endif
                        @if ($pkg['popular'])
                            <span class="absolute -top-2.5 right-4 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white">
                                POPULER
                            </span>
                        @endif
                        <h4 class="text-xs font-bold text-[#101828] mb-1 mt-1">{{ $pkg['level'] }}</h4>
                        <p class="text-[10px] text-[#667085] mb-3">Min. {{ $pkg['min_points'] }} poin</p>
                        <ul class="space-y-1">
                            @foreach($pkg['features'] as $feature)
                                <li class="text-[10px] text-[#667085] flex items-start gap-1.5">
                                    <svg class="w-3 h-3 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
