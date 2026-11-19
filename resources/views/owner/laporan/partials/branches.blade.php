<!-- 3. Laporan Gabungan Seluruh Cabang (Owner Only) -->
@if ($report['is_combined'] && count($report['branch_performance']) > 0)
    <x-cards.card class="overflow-hidden">
        {{-- Header Section Konsolidasi Cabang --}}
        <div class="p-4 border-b border-[#E4E7EC] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
            <div>
                <h3 class="text-sm font-bold text-[#101828] m-0">Laporan Konsolidasi Seluruh Cabang</h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0 leading-relaxed">Perbandingan volume penjualan dan omzet antar cabang.</p>
            </div>
            <span class="self-start sm:self-auto text-xs font-semibold px-2.5 py-1 rounded-full bg-[#DCE8FF] text-[#062072] border border-[#0B3A9E]/15 shrink-0 shadow-2xs">
                {{ count($report['branch_performance']) }} Cabang Aktif
            </span>
        </div>

        {{-- Desktop Table View (>= 768px) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold text-[11px] uppercase tracking-wider">
                        <th class="py-2.5 px-3">Nama Cabang</th>
                        <th class="py-2.5 px-3">Alamat Cabang</th>
                        <th class="py-2.5 px-3 text-center">Jumlah Transaksi</th>
                        <th class="py-2.5 px-3 text-right">Total Omzet</th>
                        <th class="py-2.5 px-3 text-right">Potongan Diskon</th>
                        <th class="py-2.5 px-3 text-right">Rata-Rata Transaksi</th>
                        <th class="py-2.5 px-3 text-center">Kontribusi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                    @php $allOmzet = max(1, $report['summary']['total_omzet']); @endphp
                    @foreach ($report['branch_performance'] as $bp)
                        @php $contrib = round(($bp['total_omzet'] / $allOmzet) * 100, 1); @endphp
                        <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                            <td class="py-2.5 px-3 font-bold text-[#101828] whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-md bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0 font-bold text-[10px]">
                                        {{ strtoupper(substr($bp['branch_name'], 0, 2)) }}
                                    </div>
                                    <span>{{ $bp['branch_name'] }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-[#667085] max-w-xs truncate">{{ $bp['address'] ?? '-' }}</td>
                            <td class="py-2.5 px-3 text-center font-bold">{{ number_format($bp['total_transactions']) }}</td>
                            <td class="py-2.5 px-3 text-right font-semibold text-[#101828] whitespace-nowrap">
                                Rp {{ number_format($bp['total_omzet'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-[#667085] whitespace-nowrap">
                                Rp {{ number_format($bp['total_discount'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-medium text-[#667085] whitespace-nowrap">
                                Rp {{ number_format($bp['average_transaction_value'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                    {{ $contrib }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (< 768px) --}}
        <div class="block md:hidden p-3.5 sm:p-4 space-y-3 bg-[#F8F9FC]/40">
            @php $allOmzet = max(1, $report['summary']['total_omzet']); @endphp
            @foreach ($report['branch_performance'] as $bp)
                @php $contrib = round(($bp['total_omzet'] / $allOmzet) * 100, 1); @endphp
                <div class="p-3.5 sm:p-4 rounded-xl border border-[#E4E7EC] bg-white shadow-2xs space-y-3">
                    {{-- Header Kartu: Nama Cabang & Kontribusi --}}
                    <div class="flex items-start justify-between gap-2.5">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0 font-bold text-xs mt-0.5 shadow-2xs">
                                {{ strtoupper(substr($bp['branch_name'], 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <span class="text-[11px] text-[#667085] block font-medium uppercase tracking-wider">Nama Cabang</span>
                                <h4 class="text-sm font-bold text-[#101828] m-0 break-words leading-snug">
                                    {{ $bp['branch_name'] }}
                                </h4>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="text-[10px] text-[#667085] block uppercase font-medium">Kontribusi</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 mt-0.5">
                                {{ $contrib }}%
                            </span>
                        </div>
                    </div>

                    {{-- Alamat Cabang (Lengkap, Tidak Terpotong) --}}
                    <div class="pt-2 border-t border-[#F2F4F7]">
                        <span class="text-[11px] text-[#667085] block font-medium uppercase tracking-wider mb-0.5">Alamat Cabang</span>
                        <p class="text-xs text-[#344054] m-0 break-words leading-relaxed">
                            {{ $bp['address'] ?: 'Alamat belum diatur' }}
                        </p>
                    </div>

                    {{-- Grid Metrik Transaksi & Keuangan --}}
                    <div class="grid grid-cols-2 gap-2.5 pt-2.5 border-t border-[#F2F4F7] text-xs">
                        <div class="p-2.5 rounded-lg bg-[#F8F9FC] border border-[#E4E7EC]/80">
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block font-medium">Total Omzet</span>
                            <span class="text-xs sm:text-sm font-bold text-[#062072] block mt-0.5 break-words">
                                Rp {{ number_format($bp['total_omzet'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-[#F8F9FC] border border-[#E4E7EC]/80">
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block font-medium">Jumlah Transaksi</span>
                            <span class="text-xs sm:text-sm font-bold text-[#101828] block mt-0.5">
                                {{ number_format($bp['total_transactions']) }} transaksi
                            </span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-[#F8F9FC] border border-[#E4E7EC]/80">
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block font-medium">Potongan Diskon</span>
                            <span class="text-xs font-semibold text-[#667085] block mt-0.5 break-words">
                                Rp {{ number_format($bp['total_discount'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-[#F8F9FC] border border-[#E4E7EC]/80">
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block font-medium">Rata-Rata Transaksi</span>
                            <span class="text-xs font-semibold text-[#101828] block mt-0.5 break-words">
                                Rp {{ number_format($bp['average_transaction_value'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-cards.card>
@endif
