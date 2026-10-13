<!-- 1. Ringkasan Omzet & Metrik Finansial -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Omzet Bersih -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Total Omzet Bersih</p>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">
                    Rp {{ number_format($report['summary']['total_omzet'], 0, ',', '.') }}
                </h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Setelah diskon & promo</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="money" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- Jumlah Transaksi -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Jumlah Transaksi</p>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">
                    {{ number_format($report['summary']['total_transactions']) }} Trx
                </h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Transaksi berbayar lunas</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="transaction" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- Rata-rata Nilai Transaksi (AOV) -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Rata-Rata Transaksi (AOV)</p>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">
                    Rp {{ number_format($report['summary']['average_transaction_value'], 0, ',', '.') }}
                </h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Rata-rata per customer</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Diskon Diberikan -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Total Potongan Diskon</p>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">
                    Rp {{ number_format($report['summary']['total_discount'], 0, ',', '.') }}
                </h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Gross: Rp {{ number_format($report['summary']['gross_omzet'], 0, ',', '.') }}</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="tag" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>
</div>

