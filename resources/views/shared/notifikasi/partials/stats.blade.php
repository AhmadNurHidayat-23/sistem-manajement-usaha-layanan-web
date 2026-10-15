<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    {{-- Terkirim --}}
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Terkirim</p>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">Sukses</span>
                </div>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">{{ number_format($stats['sent']) }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Pesan berhasil sampai</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Gagal --}}
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Gagal</p>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/80">Error</span>
                </div>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">{{ number_format($stats['failed']) }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Gagal terkirim / salah nomor</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Pending --}}
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Pending</p>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">Antrian</span>
                </div>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums truncate leading-tight">{{ number_format($stats['pending']) }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Menunggu antrian kirim</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

