<x-cards.card class="overflow-hidden">
    <div class="p-4 border-b border-[#E4E7EC]">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-[#101828] m-0">Efektivitas Promo & Kupon</h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Evaluasi pemakaian voucher diskon pelanggan.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mt-3 pt-3 border-t border-[#E4E7EC]">
            <div class="p-3 rounded-lg bg-[#F8F9FC] border border-[#E4E7EC]">
                <span class="text-[10px] uppercase font-semibold text-[#667085] block">Total Kupon Digunakan</span>
                <span class="text-sm font-bold text-[#062072] block mt-1">
                    {{ number_format($promo_effectiveness['total_usages']) }} kali
                </span>
            </div>
            <div class="p-3 rounded-lg bg-[#F8F9FC] border border-[#E4E7EC]">
                <span class="text-[10px] uppercase font-semibold text-[#667085] block">Total Potongan Diskon</span>
                <span class="text-sm font-bold text-[#101828] block mt-1">
                    Rp {{ number_format($promo_effectiveness['total_discount_given'], 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <div class="divide-y divide-[#E4E7EC]">
        @forelse ($promo_effectiveness['top_promotions'] as $promo)
            <div class="p-3.5 flex items-center justify-between hover:bg-[#F8F9FC]/60 transition-colors">
                <div>
                    <span class="font-semibold text-xs text-[#062072] bg-[#DCE8FF] px-2 py-0.5 rounded border border-[#0B3A9E]/30">
                        {{ $promo['code'] }}
                    </span>
                    <span class="text-xs font-semibold text-[#101828] ml-2">{{ $promo['name'] }}</span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-[#101828]">{{ $promo['usage_count'] }}x</span>
                    <span class="text-[10px] text-[#667085] block">Diskon: Rp {{ number_format($promo['total_discount'], 0, ',', '.') }}</span>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-xs text-[#667085]">
                Belum ada kode promo yang digunakan pelanggan.
            </div>
        @endforelse
    </div>
</x-cards.card>
