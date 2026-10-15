<!-- Performance KPI Summary Cards (Ringkasan Kinerja Karyawan) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- 1. Completed Bookings -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Layanan Selesai</p>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">{{ $performance['completed_bookings'] }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Dari {{ $performance['total_bookings_assigned'] }} order ditugaskan</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="check" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- 2. Attendance Rate -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Tingkat Kehadiran</p>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">{{ $performance['attendance_rate'] }}%</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">
                    Hadir: {{ $performance['present_shifts'] }} | Telat: {{ $performance['late_shifts'] }} | Absen: {{ $performance['absent_shifts'] }}
                </p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="clock" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- 3. Pending Commission (Estimasi Komisi) -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Estimasi Komisi (Pending)</p>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">Rp {{ number_format($performance['pending_commission'], 0, ',', '.') }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Siap dicairkan: Rp {{ number_format($performance['approved_commission'], 0, ',', '.') }}</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="money" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- 4. Total Commission Paid -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Total Komisi Cair</p>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">Rp {{ number_format($performance['paid_commission'], 0, ',', '.') }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Total akumulasi: Rp {{ number_format($performance['total_commission'], 0, ',', '.') }}</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="transaction" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>
</div>

