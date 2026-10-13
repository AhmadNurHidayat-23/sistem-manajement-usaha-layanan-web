<!-- 5. Kinerja Penjualan Karyawan & Rekap Komisi -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Pendapatan per Karyawan -->
    <x-cards.card class="overflow-hidden">
        <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-[#101828] m-0">Pendapatan per Karyawan</h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Omzet yang dihasilkan oleh staf dari pengerjaan pesanan servis.</p>
            </div>
            <span class="text-xs font-semibold text-[#667085]">{{ count($report['employees']) }} Staf</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold text-[11px] uppercase tracking-wider">
                        <th class="py-2 px-3">Nama Staf</th>
                        <th class="py-2 px-3">Cabang</th>
                        <th class="py-2 px-3 text-center">Transaksi</th>
                        <th class="py-2 px-3 text-right">Omzet Dihasilkan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                    @forelse ($report['employees'] as $emp)
                        <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                            <td class="py-2.5 px-3 font-bold text-[#101828] whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-[#062072] text-[#DCE8FF] flex items-center justify-center text-[10px] font-bold shrink-0">
                                        {{ strtoupper(substr($emp['name'], 0, 2)) }}
                                    </div>
                                    <span>{{ $emp['name'] }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-[#667085] whitespace-nowrap">{{ $emp['branch_name'] }}</td>
                            <td class="py-2.5 px-3 text-center font-bold">{{ $emp['transaction_count'] }}</td>
                            <td class="py-2.5 px-3 text-right font-semibold text-[#101828] whitespace-nowrap">
                                Rp {{ number_format($emp['total_revenue'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-[#667085]">
                                Belum ada data pengerjaan oleh staf.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-cards.card>

    <!-- Rekap Komisi Karyawan -->
    <x-cards.card class="overflow-hidden">
        <div class="p-4 border-b border-[#E4E7EC]">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-[#101828] m-0">Rekap Komisi Karyawan</h3>
                    <p class="text-xs text-[#667085] mt-0.5 m-0">Perolehan komisi staf, status pencairan, dan riwayat pembayaran.</p>
                </div>
            </div>

            <!-- Komisi Mini Cards -->
            <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-[#E4E7EC]">
                <div class="p-2 rounded-lg bg-[#F8F9FC] border border-[#E4E7EC]">
                    <span class="text-[10px] uppercase font-semibold text-[#667085] block">Total Komisi</span>
                    <span class="text-xs font-bold text-[#101828] block mt-0.5">
                        Rp {{ number_format($report['commissions']['total_commission'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-2 rounded-lg bg-[#F8F9FC] border border-[#E4E7EC]">
                    <span class="text-[10px] uppercase font-semibold text-[#667085] block">Sudah Dibayar</span>
                    <span class="text-xs font-bold text-[#101828] block mt-0.5">
                        Rp {{ number_format($report['commissions']['paid_commission'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-2 rounded-lg bg-[#F8F9FC] border border-[#E4E7EC]">
                    <span class="text-[10px] uppercase font-semibold text-[#667085] block">Pending / Tertunda</span>
                    <span class="text-xs font-bold text-[#101828] block mt-0.5">
                        Rp {{ number_format($report['commissions']['pending_commission'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold text-[11px] uppercase tracking-wider">
                        <th class="py-2 px-3">Nama Staf</th>
                        <th class="py-2 px-3 text-center">Layanan</th>
                        <th class="py-2 px-3 text-right">Total Komisi</th>
                        <th class="py-2 px-3 text-right">Sudah Dibayar</th>
                        <th class="py-2 px-3 text-right">Pending</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                    @forelse ($report['commissions']['by_employee'] as $comm)
                        <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                            <td class="py-2.5 px-3 font-bold text-[#101828] whitespace-nowrap">{{ $comm['employee_name'] }}</td>
                            <td class="py-2.5 px-3 text-center font-medium">{{ $comm['total_services'] }} kali</td>
                            <td class="py-2.5 px-3 text-right font-bold text-[#062072] whitespace-nowrap">
                                Rp {{ number_format($comm['total_commission'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-[#101828] font-medium whitespace-nowrap">
                                Rp {{ number_format($comm['paid_commission'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-[#667085] font-medium whitespace-nowrap">
                                Rp {{ number_format($comm['pending_commission'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-[#667085]">
                                Belum ada catatan komisi pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-cards.card>
</div>
