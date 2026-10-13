{{-- Promo --}}
<section id="promo" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-[#062072] block mb-2">PENAWARAN SPESIAL</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-[#101828] tracking-tight m-0">
                Promo & Voucher Aktif
            </h2>
            <p class="text-sm text-[#667085] mt-1.5 m-0">
                Gunakan kode voucher saat konfirmasi pesanan untuk mendapatkan potongan harga langsung.
            </p>
        </div>
        <a href="{{ route('customer.promo') }}"
            class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#062072] hover:text-[#04184F] no-underline shrink-0">
            <span>Lihat Semua Promo</span>
        </a>
    </div>

    @if (isset($promotions) && $promotions->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($promotions as $promo)
                @php
                    $isPercentage = $promo->discount_type->value === 'percentage';
                @endphp
                <div class="bg-white rounded-xl border border-[#E4E7EC] p-6 flex flex-col justify-between h-full hover:border-[#062072]/30 transition-colors">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-semibold text-[#062072] bg-[#DCE8FF]/70 px-2.5 py-1 rounded">
                                @if ($isPercentage)
                                    Diskon {{ number_format($promo->discount_value, 0) }}%
                                @else
                                    Hemat Rp {{ number_format($promo->discount_value, 0, ',', '.') }}
                                @endif
                            </span>
                            <span class="text-[11px] text-[#667085]">
                                s/d {{ $promo->end_date->format('d M Y') }}
                            </span>
                        </div>

                        <h3 class="text-base font-semibold text-[#101828] m-0 mb-1.5 leading-snug">
                            {{ $promo->name }}
                        </h3>
                        <p class="text-xs text-[#667085] line-clamp-2 leading-relaxed m-0 mb-4">
                            {{ $promo->branch ? 'Berlaku khusus di cabang ' . ($promo->branch->display_name ?? $promo->branch->name) . '.' : 'Berlaku di seluruh outlet resmi.' }}
                        </p>
                    </div>

                    <div class="pt-4 border-t border-[#E4E7EC] flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block font-medium">Kode Voucher</span>
                            <span class="font-sans text-sm font-bold tracking-wide text-[#062072]">{{ $promo->code }}</span>
                        </div>
                        <button type="button" onclick="copyPromoCode('{{ $promo->code }}', this)"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-[#062072] bg-[#DCE8FF]/60 hover:bg-[#062072] hover:text-white border border-[#062072]/20 cursor-pointer transition-colors">
                            Salin
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-[#E4E7EC] bg-white p-8 text-center text-[#667085]">
            <p class="text-sm m-0">Belum ada promo voucher aktif saat ini.</p>
        </div>
    @endif
</section>
