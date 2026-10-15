<!-- Metric Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- 1. Total Promos -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Total Promo</p>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">{{ number_format($statistics['total_promos']) }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Semua kupon terdaftar</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="ticket" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- 2. Active Promos -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Promo Aktif</p>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                </div>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">{{ number_format($statistics['active_promos']) }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Siap digunakan kasir</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="check" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- 3. Total Usages -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Total Penggunaan</p>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">{{ number_format($statistics['total_usages']) }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Kupon telah ditukarkan</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="transaction" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- 4. Expired Promos -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Kedaluwarsa</p>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Expired</span>
                </div>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">{{ number_format($statistics['expired_promos']) }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Lewat masa berlaku</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="clock" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>
</div>

