<!-- Riwayat poin -->
<x-cards.card class="overflow-hidden">
    <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">Riwayat Mutasi Poin</h3>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Catatan perolehan dan penukaran poin loyalitas Anda.</p>
        </div>
        <span class="text-xs text-[#667085] font-medium">
            Total: {{ $membership->loyaltyPoint?->transactions?->count() ?? 0 }} Transaksi
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold">
                    <th class="py-3 px-4">Tanggal & Waktu</th>
                    <th class="py-3 px-4">Tipe Mutasi</th>
                    <th class="py-3 px-4 text-center">Jumlah Poin</th>
                    <th class="py-3 px-4">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                @forelse ($membership->loyaltyPoint?->transactions ?? [] as $trx)
                    <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                        <td class="py-3 px-4 whitespace-nowrap text-[#667085] tabular-nums text-[11px]">
                            {{ $trx->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if ($trx->points > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <x-icons.icon name="check" class="w-3 h-3" />
                                    Poin Masuk
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                    </svg>
                                    Penukaran Reward
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-sm whitespace-nowrap">
                            @if ($trx->points > 0)
                                <span class="text-emerald-600 tabular-nums">+{{ number_format($trx->points) }}</span>
                            @else
                                <span class="text-rose-600 tabular-nums">{{ number_format($trx->points) }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-[#667085]">
                            {{ $trx->description ?? 'Transaksi loyalitas' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-[#667085]">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-8 h-8 text-[#98A2B3] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-xs font-semibold text-[#101828] m-0">Belum ada riwayat mutasi poin</p>
                                <p class="text-[11px] text-[#667085] mt-0.5 m-0">Poin akan otomatis bertambah ketika Anda melakukan transaksi pembayaran layanan di kasir.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-cards.card>
