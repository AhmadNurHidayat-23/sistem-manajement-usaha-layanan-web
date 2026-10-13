<!-- Ringkasan metrik karyawan -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Total Karyawan</p>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">{{ $totalEmployees }} Orang</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Seluruh staf terdaftar</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="employee" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Karyawan Aktif</p>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                </div>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">{{ $activeEmployeesCount }} Orang</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Status bertugas aktif</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="check" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Estimasi Komisi (Pending)</p>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">Rp {{ number_format($commissionOverview['pending'], 0, ',', '.') }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Menunggu persetujuan</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="money" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Komisi Disetujui</p>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">Rp {{ number_format($commissionOverview['approved'], 0, ',', '.') }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Siap dibayarkan</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="transaction" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>
</div>

