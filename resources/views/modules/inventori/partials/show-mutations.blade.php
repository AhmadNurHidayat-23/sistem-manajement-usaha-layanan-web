<!-- Riwayat mutasi stok bahan -->
<x-cards.card class="overflow-hidden">
    <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">Riwayat Perubahan Stok Bahan Ini</h3>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Log seluruh aktivitas masuk dan keluar bahan secara kronologis.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold">
                    <th class="py-3 px-4">Waktu</th>
                    <th class="py-3 px-4 text-center">Tipe</th>
                    <th class="py-3 px-4 text-right">Jumlah</th>
                    <th class="py-3 px-4 text-center">Sebelum &rarr; Sesudah</th>
                    <th class="py-3 px-4">Referensi</th>
                    <th class="py-3 px-4">Petugas</th>
                    <th class="py-3 px-4">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                @forelse ($mutations as $m)
                    <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                        <td class="py-3 px-4 text-[#667085] whitespace-nowrap">
                            {{ $m->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <x-badges.status :status="$m->type" />
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-sm whitespace-nowrap">
                            @if ($m->type->value === 'in')
                                <span class="text-emerald-600">+{{ rtrim(rtrim(number_format($m->quantity, 2, ',', '.'), '0'), ',') }}</span>
                            @else
                                <span class="text-rose-600">-{{ rtrim(rtrim(number_format($m->quantity, 2, ',', '.'), '0'), ',') }}</span>
                            @endif
                            <span class="text-[11px] text-[#667085] font-normal">{{ $item->unit }}</span>
                        </td>
                        <td class="py-3 px-4 text-center text-xs whitespace-nowrap font-medium">
                            <span class="text-[#667085]">{{ rtrim(rtrim(number_format($m->stock_before, 2, ',', '.'), '0'), ',') }}</span>
                            <span class="mx-1 text-[#062072] font-bold">&rarr;</span>
                            <span class="font-bold text-[#101828]">{{ rtrim(rtrim(number_format($m->stock_after, 2, ',', '.'), '0'), ',') }}</span>
                        </td>
                        <td class="py-3 px-4 text-[#667085] capitalize">
                            {{ str_replace('_', ' ', $m->reference_type ?? '-') }}
                        </td>
                        <td class="py-3 px-4 font-medium text-[#101828]">
                            {{ $m->user?->name ?? 'Sistem' }}
                        </td>
                        <td class="py-3 px-4 text-[#667085] max-w-xs truncate">
                            {{ $m->notes ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-[#667085]">
                            Belum ada riwayat mutasi stok untuk bahan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($mutations->hasPages())
        <div class="p-4 border-t border-[#E4E7EC]">
            {{ $mutations->links() }}
        </div>
    @endif
</x-cards.card>
