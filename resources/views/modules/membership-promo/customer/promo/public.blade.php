@extends('layouts.pelanggan', ['title' => 'Katalog Voucher & Promo - SIMAUL'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Katalog Voucher & Promo Menarik</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Gunakan kode voucher berikut saat melakukan pemesanan layanan atau sebutkan kode kepada kasir saat pembayaran.</p>
        </div>
        @if (auth()->check() && auth()->user()->role?->name === 'customer')
            <div class="flex items-center gap-2">
                <x-buttons.btn variant="outline" href="{{ route('members.my-membership') }}" class="gap-1.5 text-xs">
                    <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Poin Loyalitas Saya
                </x-buttons.btn>
                <x-buttons.btn variant="primary" href="{{ route('bookings.create') }}" class="gap-1.5 text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Gunakan Saat Booking
                </x-buttons.btn>
            </div>
        @endif
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif

    <!-- Voucher Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($promotions as $promo)
            @php
                $isPercentage = $promo->discount_type->value === 'percentage';
                $daysRemaining = (int) now()->diffInDays($promo->end_date, false);
            @endphp
            <div class="relative bg-white rounded-xl border border-[#E4E7EC] shadow-xs flex flex-col justify-between overflow-hidden hover:shadow-md transition-shadow">
                <!-- Top Accent Strip -->
                <div class="h-2 bg-gradient-to-r from-[#062072] via-[#0B3A9E] to-blue-500"></div>

                <div class="p-5 flex-1 flex flex-col">
                    <!-- Discount Highlight Badge & Branch -->
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">
                            @if ($isPercentage)
                                DISKON {{ number_format($promo->discount_value, 0) }}%
                            @else
                                POTONGAN RP {{ number_format($promo->discount_value, 0, ',', '.') }}
                            @endif
                        </span>

                        @if ($promo->branch)
                            <span class="text-[10px] font-medium text-[#667085] bg-slate-100 px-2 py-0.5 rounded truncate max-w-[120px]" title="{{ $promo->branch->display_name }}">
                                {{ $promo->branch->display_name }}
                            </span>
                        @else
                            <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                Semua Cabang
                            </span>
                        @endif
                    </div>

                    <!-- Promo Name -->
                    <h3 class="text-sm font-bold text-[#101828] mb-1 leading-snug">{{ $promo->name }}</h3>

                    <!-- Validity & Quota Meta -->
                    <div class="mt-auto pt-3 border-t border-[#E4E7EC] space-y-1.5 text-xs text-[#667085]">
                        <div class="flex items-center justify-between text-[11px]">
                            <span>Masa Berlaku:</span>
                            <span class="font-medium text-[#101828]">s/d {{ $promo->end_date->format('d M Y') }}</span>
                        </div>
                        @if ($promo->quota !== null)
                            <div class="flex items-center justify-between text-[11px]">
                                <span>Sisa Kuota:</span>
                                <span class="font-semibold {{ ($promo->quota - $promo->usages_count) <= 5 ? 'text-rose-600' : 'text-[#101828]' }}">
                                    {{ max(0, $promo->quota - $promo->usages_count) }} kupon lagi
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card Bottom: Promo Code Box & Copy Button -->
                <div class="p-4 bg-[#F8F9FC] border-t border-dashed border-[#E4E7EC] flex items-center justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <span class="text-[10px] text-[#667085] uppercase tracking-wider block font-semibold">Kode Voucher</span>
                        <span class="text-sm font-bold text-[#062072] tracking-wider truncate block" id="code-{{ $promo->id }}">
                            {{ $promo->code }}
                        </span>
                    </div>

                    <button type="button" onclick="copyPromoCode('{{ $promo->code }}', this)" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#062072] bg-[#DCE8FF] hover:bg-[#062072] hover:text-white transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                        </svg>
                        <span>Salin Kode</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-cards.card class="p-8 text-center text-[#667085]">
                    <div class="w-12 h-12 rounded-full bg-[#DCE8FF] text-[#062072] flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-[#101828] mb-1">Belum Ada Promo Aktif</h3>
                    <p class="text-xs text-[#667085] m-0">Nantikan voucher dan penawaran diskon spesial yang akan segera hadir.</p>
                </x-cards.card>
            </div>
        @endforelse
    </div>
</div>
@endsection
