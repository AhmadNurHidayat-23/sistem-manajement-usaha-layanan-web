<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach([
        ['Appointment',      $metrics['appointments'], 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'Janji temu terjadwal'],
        ['Drop-off',         $metrics['dropoffs'],     'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4', 'Pesanan titipan layanan'],
        ['Sedang Dikerjakan', $metrics['in_progress'], 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'Dalam proses pengerjaan'],
        ['Selesai',          $metrics['completed'],    'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'Tugas tuntas hari ini'],
    ] as [$label, $count, $icon, $subtitle])
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">{{ $label }}</p>
                <h3 class="text-xl sm:text-[22px] font-bold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">{{ $count }} Tugas</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">{{ $subtitle }}</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/>
                </svg>
            </div>
        </div>
    </div>
    @endforeach
</div>

