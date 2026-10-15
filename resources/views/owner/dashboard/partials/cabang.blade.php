<x-cards.card class="overflow-hidden h-full flex flex-col">
    <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">Performa Tiap Cabang</h3>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Perbandingan transaksi, omzet, dan booking per outlet.</p>
        </div>
        <x-badges.badge variant="primary" class="text-xs">
            {{ count($branch_performance) }} Cabang
        </x-badges.badge>
    </div>

    <div class="overflow-x-auto flex-1">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold text-[11px] uppercase tracking-wider">
                    <th class="py-2 px-3">Nama Cabang</th>
                    <th class="py-2 px-3 text-center">Transaksi</th>
                    <th class="py-2 px-3 text-center">Booking</th>
                    <th class="py-2 px-3 text-right">Total Omzet</th>
                    <th class="py-2 px-3 text-right">Rata-Rata / Trx</th>
                    <th class="py-2 px-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                @forelse ($branch_performance as $bp)
                    <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                        <td class="py-2.5 px-3 font-bold text-[#101828]">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-md bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0 font-bold text-[10px]">
                                    {{ strtoupper(substr($bp['display_name'] ?? $bp['name'], 0, 2)) }}
                                </div>
                                <span class="truncate max-w-[160px]" title="{{ $bp['name'] }}">{{ $bp['display_name'] ?? $bp['name'] }}</span>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-center font-bold">{{ number_format($bp['total_transactions']) }}</td>
                        <td class="py-2.5 px-3 text-center font-medium">{{ number_format($bp['total_bookings']) }}</td>
                        <td class="py-2.5 px-3 text-right font-semibold text-[#101828] whitespace-nowrap">
                            Rp {{ number_format($bp['total_omzet'], 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3 text-right text-[#667085] font-medium whitespace-nowrap">
                            Rp {{ number_format($bp['average_transaction_value'], 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3 text-center">
                            <x-badges.status :status="$bp['is_active']" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-[#667085]">
                            Belum ada data cabang terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-cards.card>
