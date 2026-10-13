<!-- 6. Penggunaan Promo & Voucher Diskon -->
<x-cards.card class="overflow-hidden">
    <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">Penggunaan Promo & Kupon Diskon</h3>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Rincian kupon promo yang digunakan pada transaksi pelanggan.</p>
        </div>
        <div class="flex items-center gap-3 text-xs">
            <span class="text-[#667085]">Total Dipakai: <strong class="text-[#101828]">{{ $report['promotions']['total_usages'] }} kali</strong></span>
            <span class="text-[#667085]">Total Diskon Promo: <strong class="text-[#101828]">Rp {{ number_format($report['promotions']['total_discount'], 0, ',', '.') }}</strong></span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-[13px]">
            <thead>
                <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold text-xs uppercase tracking-wider">
                    <th class="py-3 px-4">Kode Promo</th>
                    <th class="py-3 px-4">Nama Promo</th>
                    <th class="py-3 px-4">Jenis & Nilai Promo</th>
                    <th class="py-3 px-4 text-center">Frekuensi Digunakan</th>
                    <th class="py-3 px-4 text-right">Total Diskon Diberikan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                @forelse ($report['promotions']['list'] as $promo)
                    <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded font-semibold text-xs bg-[#DCE8FF] text-[#062072] border border-[#0B3A9E]/20">
                                {{ $promo['code'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-semibold text-[#101828] whitespace-nowrap">{{ $promo['name'] }}</td>
                        <td class="py-3 px-4 text-[#667085] whitespace-nowrap">
                            @if ($promo['discount_type'] === 'percentage')
                                Diskon {{ number_format($promo['discount_value'], 0) }}%
                            @else
                                Potongan Rp {{ number_format($promo['discount_value'], 0, ',', '.') }}
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-semibold text-[#101828] whitespace-nowrap">{{ $promo['usage_count'] }} kali</td>
                        <td class="py-3 px-4 text-right font-semibold text-[#101828] tabular-nums whitespace-nowrap">
                            Rp {{ number_format($promo['total_discount'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-[#667085]">
                            Belum ada penggunaan kode promo pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-cards.card>
