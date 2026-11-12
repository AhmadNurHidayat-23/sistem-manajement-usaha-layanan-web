<!-- 6. Penggunaan Promo & Voucher Diskon -->
<x-cards.card class="overflow-hidden">
    {{-- Header Section Promo & Diskon --}}
    <div class="p-4 border-b border-[#E4E7EC] flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">Penggunaan Promo & Kupon Diskon</h3>
            <p class="text-xs text-[#667085] mt-0.5 m-0 leading-relaxed">Rincian kupon promo yang digunakan pada transaksi pelanggan.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs">
            <div class="px-2.5 py-1.5 rounded-lg bg-[#F8F9FC] border border-[#E4E7EC] flex items-center gap-1.5 shadow-2xs">
                <span class="text-[#667085]">Total Dipakai:</span>
                <strong class="text-[#101828] font-bold">{{ $report['promotions']['total_usages'] }} kali</strong>
            </div>
            <div class="px-2.5 py-1.5 rounded-lg bg-rose-50/70 border border-rose-200/70 flex items-center gap-1.5 shadow-2xs">
                <span class="text-rose-700 font-medium">Total Diskon:</span>
                <strong class="text-rose-900 font-bold">Rp {{ number_format($report['promotions']['total_discount'], 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    {{-- Desktop Table View (>= 768px) --}}
    <div class="hidden md:block overflow-x-auto">
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
                        <td class="py-3 px-4 font-semibold text-[#101828]">{{ $promo['name'] }}</td>
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

    {{-- Mobile Cards View (< 768px) --}}
    <div class="block md:hidden p-3.5 sm:p-4 space-y-3 bg-[#F8F9FC]/40">
        @forelse ($report['promotions']['list'] as $promo)
            <div class="p-3.5 sm:p-4 rounded-xl border border-[#E4E7EC] bg-white shadow-2xs space-y-3">
                {{-- Header Kartu: Kode & Frekuensi Pemakaian --}}
                <div class="flex items-center justify-between gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md font-bold text-xs bg-[#DCE8FF] text-[#062072] border border-[#0B3A9E]/20 tracking-wider">
                        {{ $promo['code'] }}
                    </span>
                    <span class="text-xs font-semibold text-[#101828] bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-md">
                        {{ $promo['usage_count'] }}x digunakan
                    </span>
                </div>

                {{-- Nama Promo (Lengkap, Tidak Terpotong) --}}
                <div>
                    <span class="text-[10px] text-[#667085] uppercase tracking-wider block font-medium">Nama Promo</span>
                    <h4 class="text-sm font-bold text-[#101828] m-0 break-words leading-snug mt-0.5">
                        {{ $promo['name'] }}
                    </h4>
                </div>

                {{-- Grid Detail Jenis Nilai & Total Diskon --}}
                <div class="grid grid-cols-2 gap-2.5 pt-2.5 border-t border-[#F2F4F7] text-xs">
                    <div class="p-2.5 rounded-lg bg-[#F8F9FC] border border-[#E4E7EC]/80">
                        <span class="text-[10px] text-[#667085] uppercase tracking-wider block font-medium">Jenis & Nilai Promo</span>
                        <span class="text-xs font-semibold text-[#344054] block mt-0.5 break-words">
                            @if ($promo['discount_type'] === 'percentage')
                                Diskon {{ number_format($promo['discount_value'], 0) }}%
                            @else
                                Potongan Rp {{ number_format($promo['discount_value'], 0, ',', '.') }}
                            @endif
                        </span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-rose-50/50 border border-rose-200/60">
                        <span class="text-[10px] text-rose-700 uppercase tracking-wider block font-medium">Total Diskon Diberikan</span>
                        <span class="text-xs sm:text-sm font-bold text-rose-800 block mt-0.5 break-words">
                            Rp {{ number_format($promo['total_discount'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-xs text-[#667085] bg-white rounded-xl border border-[#E4E7EC]">
                Belum ada penggunaan kode promo pada periode ini.
            </div>
        @endforelse
    </div>
</x-cards.card>
