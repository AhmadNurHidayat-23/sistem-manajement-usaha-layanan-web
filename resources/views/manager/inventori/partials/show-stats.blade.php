<!-- Ringkasan metrik persediaan bahan -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Stok Saat Ini</p>
        <div class="flex items-baseline gap-2 mt-1.5">
            <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mb-0 tabular-nums leading-tight">
                {{ rtrim(rtrim(number_format($item->current_stock, 2, ',', '.'), '0'), ',') }}
            </h3>
            <span class="text-xs font-semibold text-[#667085]">{{ $item->unit }}</span>
        </div>
        <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 leading-snug">Ketersediaan fisik di gudang</p>
    </div>

    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Batas Stok Minimum</p>
        <div class="flex items-baseline gap-2 mt-1.5">
            <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mb-0 tabular-nums leading-tight">
                {{ rtrim(rtrim(number_format($item->minimum_stock, 2, ',', '.'), '0'), ',') }}
            </h3>
            <span class="text-xs font-semibold text-[#667085]">{{ $item->unit }}</span>
        </div>
        <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 leading-snug">Ambang batas peringatan stok</p>
    </div>

    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Harga Modal / Satuan</p>
        <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums leading-tight">
            Rp {{ number_format($item->cost_per_unit, 0, ',', '.') }}
        </h3>
        <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 leading-snug">Biaya per {{ $item->unit }}</p>
    </div>

    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Total Nilai Persediaan</p>
        <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums leading-tight">
            Rp {{ number_format($item->current_stock * $item->cost_per_unit, 0, ',', '.') }}
        </h3>
        <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 leading-snug">Akumulasi modal stok saat ini</p>
    </div>
</div>

