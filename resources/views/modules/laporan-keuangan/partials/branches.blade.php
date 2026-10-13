<!-- 3. Laporan Gabungan Seluruh Cabang (Owner Only) -->
@if ($report['is_combined'] && count($report['branch_performance']) > 0)
    <x-cards.card class="overflow-hidden">
        <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-[#101828] m-0">Laporan Konsolidasi Seluruh Cabang</h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Perbandingan volume penjualan dan omzet antar cabang outlet.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-[#DCE8FF] text-[#062072]">
                {{ count($report['branch_performance']) }} Cabang Aktif
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold text-[11px] uppercase tracking-wider">
                        <th class="py-2 px-3">Nama Cabang</th>
                        <th class="py-2 px-3">Alamat Outlet</th>
                        <th class="py-2 px-3 text-center">Jumlah Transaksi</th>
                        <th class="py-2 px-3 text-right">Total Omzet</th>
                        <th class="py-2 px-3 text-right">Potongan Diskon</th>
                        <th class="py-2 px-3 text-right">Rata-Rata Transaksi</th>
                        <th class="py-2 px-3 text-center">Kontribusi</th>
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
    </x-cards.card>
@endif
