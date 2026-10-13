<x-cards.card class="overflow-hidden">
    <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">Katalog Bahan & Stok Terkini</h3>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Menampilkan {{ $items->total() }} bahan terdaftar.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-[13px]">
            <thead>
                <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold text-xs uppercase tracking-wider">
                    <th class="py-3 px-4">Kode</th>
                    <th class="py-3 px-4">Nama Bahan</th>
                    @if ($branches->isNotEmpty())
                        <th class="py-3 px-4">Cabang</th>
                    @endif
                    <th class="py-3 px-4 text-center">Stok Saat Ini</th>
                    <th class="py-3 px-4 text-center">Batas Min.</th>
                    <th class="py-3 px-4 text-right">Biaya / Satuan</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                @forelse ($items as $item)
                    <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                        <td class="py-3 px-4 font-semibold text-[#062072] whitespace-nowrap">
                            {{ $item->code ?? '-' }}
                        </td>
                        <td class="py-2.5 px-3 font-semibold text-[#101828]">
                            <a href="{{ route('inventori.show', $item) }}" class="hover:underline text-[#062072] whitespace-nowrap">
                                {{ $item->name }}
                            </a>
                            @if ($item->description)
                                <p class="text-[11px] text-[#667085] font-normal truncate max-w-xs mt-0.5 m-0">{{ $item->description }}</p>
                            @endif
                        </td>
                        @if ($branches->isNotEmpty())
                            <td class="py-2.5 px-3 text-[#667085] truncate max-w-[140px]" title="{{ $item->branch?->name ?? '-' }}">
                                {{ $item->branch?->name ?? '-' }}
                            </td>
                        @endif
                        <td class="py-2.5 px-3 text-center font-bold text-xs whitespace-nowrap">
                            <span class="{{ (float) $item->current_stock <= 0 ? 'text-rose-600' : ((float) $item->current_stock <= (float) $item->minimum_stock ? 'text-amber-600' : 'text-emerald-600') }}">
                                {{ rtrim(rtrim(number_format($item->current_stock, 2, ',', '.'), '0'), ',') }}
                            </span>
                            <span class="text-[11px] text-[#667085] font-normal">{{ $item->unit }}</span>
                        </td>
                        <td class="py-2.5 px-3 text-center text-[#667085] font-medium whitespace-nowrap">
                            {{ rtrim(rtrim(number_format($item->minimum_stock, 2, ',', '.'), '0'), ',') }} {{ $item->unit }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-medium text-[#101828] whitespace-nowrap">
                            Rp {{ number_format($item->cost_per_unit, 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                            <x-badges.status :status="$item->status" />
                        </td>
                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a
                                    href="{{ route('inventori.show', $item) }}"
                                    class="h-7 px-2.5 inline-flex items-center justify-center rounded bg-[#DCE8FF] text-[#062072] font-semibold text-xs hover:bg-[#0B3A9E] hover:text-white transition-colors"
                                >
                                    Kelola Stok
                                </a>
                                <a
                                    href="{{ route('inventori.edit', $item) }}"
                                    data-action="edit-inventory"
                                    data-payload="{{ json_encode([
                                        'id' => $item->id,
                                        'code' => $item->code,
                                        'name' => $item->name,
                                        'unit' => $item->unit,
                                        'minimum_stock' => $item->minimum_stock,
                                        'cost_per_unit' => $item->cost_per_unit,
                                        'description' => $item->description,
                                    ]) }}"
                                    class="h-7 w-7 inline-flex items-center justify-center rounded text-[#667085] hover:text-[#062072] hover:bg-slate-100 transition-colors cursor-pointer"
                                    title="Edit Bahan"
                                >
                                    <x-icons.icon name="edit" class="w-3.5 h-3.5" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $branches->isNotEmpty() ? 8 : 7 }}" class="py-8 text-center text-[#667085]">
                            Tidak ada data bahan yang cocok dengan pencarian atau filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->hasPages())
        <div class="p-4 border-t border-[#E4E7EC]">
            {{ $items->links() }}
        </div>
    @endif
</x-cards.card>
