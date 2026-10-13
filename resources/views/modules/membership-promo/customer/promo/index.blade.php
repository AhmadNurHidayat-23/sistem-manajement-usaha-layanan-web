@extends('layouts.pelanggan', ['title' => 'Promo & Voucher - SIMAUL'])

@section('content')
<div class="space-y-8 sm:space-y-10 pb-16">

    <!-- Intro Header Section -->
    <section class="bg-[#F5F8FC] border-b border-[#E4E7EC] -mt-6 sm:-mt-8 py-10 sm:py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl space-y-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#062072] bg-[#DCE8FF]/80 px-3 py-1 rounded-md inline-block mb-1">
                    Promo & Voucher
                </span>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-[#101828] tracking-tight leading-tight m-0">
                    Promo untuk Layanan Pilihan Anda
                </h1>
                <p class="text-sm sm:text-base text-[#667085] leading-relaxed m-0 pt-1">
                    Temukan promo dan voucher aktif yang dapat digunakan saat melakukan pemesanan layanan.
                </p>
            </div>
        </div>
    </section>

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm flex items-center gap-2.5 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Promo Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($promotions as $promo)
                @php
                    $isPercentage = $promo->discount_type->value === 'percentage';
                @endphp
                <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs flex flex-col justify-between overflow-hidden hover:border-[#062072]/30 hover:shadow-sm transition-all h-full">
                    
                    <!-- Card Top Area -->
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <!-- Badge row -->
                            <div class="flex items-center justify-between gap-2 mb-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold text-[#062072] bg-[#DCE8FF]/80">
                                    @if ($isPercentage)
                                        Diskon {{ number_format($promo->discount_value, 0) }}%
                                    @else
                                        Hemat Rp {{ number_format($promo->discount_value, 0, ',', '.') }}
                                    @endif
                                </span>

                                @if ($promo->branch)
                                    <span class="text-[11px] font-medium text-[#667085] bg-neutral-100 px-2 py-0.5 rounded truncate max-w-[130px]" title="{{ $promo->branch->display_name }}">
                                        {{ $promo->branch->display_name }}
                                    </span>
                                @else
                                    <span class="text-[11px] font-medium text-[#062072] bg-[#DCE8FF]/50 px-2 py-0.5 rounded">
                                        Semua Cabang
                                    </span>
                                @endif
                            </div>

                            <!-- Promo Title -->
                            <h2 class="text-base font-bold text-[#101828] mb-2 leading-snug m-0">
                                {{ $promo->name }}
                            </h2>

                            <p class="text-xs text-[#667085] leading-relaxed line-clamp-2 m-0">
                                {{ $promo->branch ? 'Berlaku khusus untuk pesanan layanan di ' . $promo->branch->display_name . '.' : 'Dapat digunakan untuk semua layanan dan cabang resmi SIMAUL.' }}
                            </p>
                        </div>

                        <!-- Validity and Quota -->
                        <div class="mt-6 pt-4 border-t border-[#E4E7EC] space-y-1.5 text-xs text-[#667085]">
                            <div class="flex items-center justify-between text-xs">
                                <span>Masa Berlaku:</span>
                                <span class="font-medium text-[#101828]">s/d {{ $promo->end_date->format('d M Y') }}</span>
                            </div>
                            @if ($promo->quota !== null)
                                <div class="flex items-center justify-between text-xs">
                                    <span>Sisa Kuota:</span>
                                    <span class="font-medium {{ ($promo->quota - ($promo->usages_count ?? 0)) <= 5 ? 'text-amber-800 font-semibold' : 'text-[#101828]' }}">
                                        {{ max(0, $promo->quota - ($promo->usages_count ?? 0)) }} voucher
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Bottom Area: Voucher Code & Copy Action -->
                    <div class="px-6 py-4 bg-[#F5F8FC] border-t border-[#E4E7EC] flex items-center justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block font-semibold">Kode Voucher</span>
                            <span class="text-sm sm:text-base font-bold text-[#062072] tracking-wider truncate block select-all">{{ $promo->code }}</span>
                        </div>
                        <button type="button" onclick="copyPromoCode('{{ $promo->code }}', this)"
                            class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-[#062072] bg-white border border-[#E4E7EC] hover:bg-[#062072] hover:text-white hover:border-[#062072] transition-colors cursor-pointer shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                            </svg>
                            <span class="btn-text">Salin</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white border border-[#E4E7EC] rounded-xl p-12 text-center text-[#667085]">
                    <div class="w-12 h-12 rounded-xl bg-[#DCE8FF]/50 text-[#062072] flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-[#101828] mb-1">Belum Ada Promo Aktif</h3>
                    <p class="text-xs text-[#667085] m-0">Nantikan voucher dan penawaran diskon spesial yang akan segera hadir.</p>
                </div>
            @endforelse
        </div>

        @if($promotions->hasPages())
            <div class="pt-4">{{ $promotions->links() }}</div>
        @endif
    </div>

</div>

@endsection
