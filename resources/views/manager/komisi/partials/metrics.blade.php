<!-- KPI Metric Overview Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- 1. Estimasi Pending -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Estimasi Pending</p>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">Rp {{ number_format($overview['pending'], 0, ',', '.') }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Menunggu persetujuan</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="money" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- 2. Disetujui (Siap Cair) -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Komisi Disetujui</p>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">Rp {{ number_format($overview['approved'], 0, ',', '.') }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Siap dicairkan</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="check" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- 3. Telah Dicairkan -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Telah Dicairkan (Paid)</p>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">Rp {{ number_format($overview['paid'], 0, ',', '.') }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Sudah dibayarkan</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="transaction" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- 4. Total Akumulasi -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Total Nilai Komisi</p>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">Rp {{ number_format($overview['total'], 0, ',', '.') }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">{{ $overview['count'] }} transaksi komisi</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="report" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>
</div>

