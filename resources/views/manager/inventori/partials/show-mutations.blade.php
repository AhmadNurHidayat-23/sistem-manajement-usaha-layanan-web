<!-- Riwayat mutasi stok bahan -->
<x-cards.card class="overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-[#E4E7EC] dark:border-[#1E2D4A] flex items-center justify-between flex-wrap gap-2">
        <div>
            <h3 class="text-sm sm:text-base font-bold text-[#101828] dark:text-white m-0">Riwayat Stok Bahan</h3>
            <p class="text-xs text-[#667085] dark:text-[#94A3B8] mt-0.5 m-0">Log seluruh aktivitas masuk dan keluar bahan secara kronologis.</p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
            {{ $mutations->total() }} Mutasi
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#F8F9FC] dark:bg-[#0D1527] border-b border-[#E4E7EC] dark:border-[#1E2D4A] text-[#667085] dark:text-[#94A3B8] font-semibold">
                    <th class="py-3 px-4 whitespace-nowrap">Waktu</th>
                    <th class="py-3 px-4 text-center whitespace-nowrap">Jenis Mutasi</th>
                    <th class="py-3 px-4 text-right whitespace-nowrap">Jumlah</th>
                    <th class="py-3 px-4 text-center whitespace-nowrap">Stok Sebelum &rarr; Sesudah</th>
                    <th class="py-3 px-4 whitespace-nowrap">Referensi</th>
                    <th class="py-3 px-4 whitespace-nowrap">Petugas</th>
                    <th class="py-3 px-4">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC] dark:divide-[#1E2D4A] text-[#101828] dark:text-white">
                @forelse ($mutations as $m)
                    <tr class="hover:bg-[#F8F9FC]/60 dark:hover:bg-[#1E2D4A]/40 transition-colors">
                        <td class="py-3 px-4 text-[#667085] dark:text-[#94A3B8] whitespace-nowrap">
                            {{ $m->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if ($m->type->value === 'in')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-400 dark:border-emerald-800">
                                    Masuk
                                </span>
                            @elseif ($m->type->value === 'out')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/50 dark:text-rose-400 dark:border-rose-800">
                                    Keluar
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/50 dark:text-blue-400 dark:border-blue-800">
                                    Penyesuaian
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-sm whitespace-nowrap">
                            @if ($m->type->value === 'in')
                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">+{{ rtrim(rtrim(number_format($m->quantity, 2, ',', '.'), '0'), ',') }}</span>
                            @elseif ($m->type->value === 'out')
                                <span class="text-rose-600 dark:text-rose-400 font-semibold">-{{ rtrim(rtrim(number_format($m->quantity, 2, ',', '.'), '0'), ',') }}</span>
                            @else
                                <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ rtrim(rtrim(number_format($m->quantity, 2, ',', '.'), '0'), ',') }}</span>
                            @endif
                            <span class="text-[11px] text-[#667085] dark:text-[#94A3B8] font-normal ml-0.5">{{ $item->unit }}</span>
                        </td>
                        <td class="py-3 px-4 text-center text-xs whitespace-nowrap font-medium">
                            <span class="text-[#667085] dark:text-[#94A3B8]">{{ rtrim(rtrim(number_format($m->stock_before, 2, ',', '.'), '0'), ',') }}</span>
                            <span class="mx-1 text-[#062072] dark:text-[#93C5FD] font-bold">&rarr;</span>
                            <span class="font-bold text-[#101828] dark:text-white">{{ rtrim(rtrim(number_format($m->stock_after, 2, ',', '.'), '0'), ',') }}</span>
                        </td>
                        <td class="py-3 px-4 text-[#667085] dark:text-[#94A3B8] whitespace-nowrap capitalize">
                            @php
                                $refLabels = [
                                    'restock' => 'Pembelian / Tambah Stok',
                                    'pos_sale' => 'Penjualan POS',
                                    'service_usage' => 'Pemakaian Layanan',
                                    'usage' => 'Pemakaian Operasional',
                                    'transfer_in' => 'Transfer Masuk',
                                    'transfer_out' => 'Transfer Keluar',
                                    'return_customer' => 'Retur Pelanggan',
                                    'refund' => 'Pengembalian / Refund',
                                    'correction' => 'Koreksi Penyesuaian',
                                    'adjustment' => 'Penyesuaian Stok',
                                ];
                                $refKey = $m->reference_type ?? '';
                                $refDisplay = $refLabels[$refKey] ?? str_replace('_', ' ', $refKey ?: '-');
                            @endphp
                            {{ $refDisplay }}
                        </td>
                        <td class="py-3 px-4 font-medium text-[#101828] dark:text-white whitespace-nowrap">
                            {{ $m->user?->name ?? 'Sistem' }}
                        </td>
                        <td class="py-3 px-4 text-[#667085] dark:text-[#94A3B8] max-w-xs truncate" title="{{ $m->notes }}">
                            {{ $m->notes ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-[#667085] dark:text-[#94A3B8]">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-[#98A2B3] dark:text-[#475467]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <span class="font-medium">Belum ada riwayat mutasi stok untuk bahan ini.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($mutations->hasPages())
        <div class="p-4 border-t border-[#E4E7EC] dark:border-[#1E2D4A]">
            {{ $mutations->links() }}
        </div>
    @endif
</x-cards.card>
