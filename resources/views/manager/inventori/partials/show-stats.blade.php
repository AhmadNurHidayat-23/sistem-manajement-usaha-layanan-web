<!-- Ringkasan metrik persediaan bahan -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white dark:bg-[#111C35] rounded-xl border border-[#E4E7EC] dark:border-[#1E2D4A] shadow-2xs py-3.5 px-4 flex flex-col justify-between h-full">
        <p class="text-[11px] sm:text-xs font-semibold text-[#667085] dark:text-[#94A3B8] uppercase tracking-wider m-0 truncate">Stok Saat Ini</p>
        <div class="flex items-baseline gap-2 mt-2">
            <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-[#101828] dark:text-white mb-0 tabular-nums leading-tight">
                {{ rtrim(rtrim(number_format($item->current_stock, 2, ',', '.'), '0'), ',') }}
            </h3>
            <span class="text-xs font-semibold text-[#667085] dark:text-[#94A3B8]">{{ $item->unit }}</span>
        </div>
        <p class="text-[11px] sm:text-xs text-[#667085] dark:text-[#94A3B8] mt-1 m-0 leading-snug">Ketersediaan fisik di gudang</p>
    </div>

    <div class="bg-white dark:bg-[#111C35] rounded-xl border border-[#E4E7EC] dark:border-[#1E2D4A] shadow-2xs py-3.5 px-4 flex flex-col justify-between h-full">
        <p class="text-[11px] sm:text-xs font-semibold text-[#667085] dark:text-[#94A3B8] uppercase tracking-wider m-0 truncate">Batas Stok Minimum</p>
        <div class="flex items-baseline gap-2 mt-2">
            <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-[#101828] dark:text-white mb-0 tabular-nums leading-tight">
                {{ rtrim(rtrim(number_format($item->minimum_stock, 2, ',', '.'), '0'), ',') }}
            </h3>
            <span class="text-xs font-semibold text-[#667085] dark:text-[#94A3B8]">{{ $item->unit }}</span>
        </div>
        <p class="text-[11px] sm:text-xs text-[#667085] dark:text-[#94A3B8] mt-1 m-0 leading-snug">Ambang batas peringatan stok</p>
    </div>

    <div class="bg-white dark:bg-[#111C35] rounded-xl border border-[#E4E7EC] dark:border-[#1E2D4A] shadow-2xs py-3.5 px-4 flex flex-col justify-between h-full">
        <p class="text-[11px] sm:text-xs font-semibold text-[#667085] dark:text-[#94A3B8] uppercase tracking-wider m-0 truncate">Harga Modal / Satuan</p>
        <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-[#101828] dark:text-white mt-2 mb-0 tabular-nums leading-tight">
            Rp {{ number_format($item->cost_per_unit, 0, ',', '.') }}
        </h3>
        <p class="text-[11px] sm:text-xs text-[#667085] dark:text-[#94A3B8] mt-1 m-0 leading-snug">Biaya per {{ $item->unit }}</p>
    </div>

    <div class="bg-white dark:bg-[#111C35] rounded-xl border border-[#E4E7EC] dark:border-[#1E2D4A] shadow-2xs py-3.5 px-4 flex flex-col justify-between h-full">
        <p class="text-[11px] sm:text-xs font-semibold text-[#667085] dark:text-[#94A3B8] uppercase tracking-wider m-0 truncate">Total Nilai Persediaan</p>
        <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-[#101828] dark:text-white mt-2 mb-0 tabular-nums leading-tight">
            Rp {{ number_format($item->current_stock * $item->cost_per_unit, 0, ',', '.') }}
        </h3>
        <p class="text-[11px] sm:text-xs text-[#667085] dark:text-[#94A3B8] mt-1 m-0 leading-snug">Akumulasi modal stok saat ini</p>
    </div>
</div>
