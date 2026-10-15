<!-- Metrics Cards Grid: Style konsisten untuk semua KPI (Option 2) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Shift Status -->
    <a href="{{ route('pos.shift.index', ['branch_id' => $activeBranch?->id]) }}" class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full hover:border-[#062072]/30 hover:bg-[#F5F8FC]/50 transition-colors group" title="Buka Halaman Shift Kasir">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Status Shift</p>
                @if ($activeShift)
                    <h3 class="text-[20px] font-semibold tracking-tight text-emerald-600 mt-1 mb-0 leading-[1.3] break-words">Shift Aktif</h3>
                    <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Sejak {{ $activeShift->opened_at->format('H:i') }} &bull; Kelola</p>
                @else
                    <h3 class="text-[20px] font-semibold tracking-tight text-amber-600 mt-1 mb-0 leading-[1.3] break-words">Belum Buka</h3>
                    <p class="text-[11px] sm:text-xs text-amber-700 mt-0.5 m-0 truncate leading-snug">Klik untuk buka shift</p>
                @endif
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0 group-hover:bg-[#062072] group-hover:text-white transition-colors">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
    </a>

    <!-- Card 2: Total Transaksi -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Transaksi Hari Ini</p>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums leading-[1.3] break-words">{{ $shiftSummary['total_transactions'] ?? 0 }} Trx</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Transaksi penjualan kasir</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="cart" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- Card 3: Penjualan Shift / Hari Ini -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Penjualan Shift</p>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums leading-[1.3] break-words">Rp {{ number_format($shiftSummary['total_sales'] ?? 0, 0, ',', '.') }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Total omset shift berjalan</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <x-icons.icon name="money" class="w-[18px] h-[18px]" />
            </div>
        </div>
    </div>

    <!-- Card 4: Kas di Laci -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs py-3 px-3.5 sm:py-3.5 sm:px-4 flex flex-col justify-between h-full">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-[#667085] uppercase tracking-wider m-0 truncate">Kas Tunai di Laci</p>
                <h3 class="text-[20px] font-semibold tracking-tight text-[#101828] mt-1 mb-0 tabular-nums leading-[1.3] break-words">Rp {{ number_format($shiftSummary['expected_cash'] ?? ($activeShift?->opening_cash ?? 0), 0, ',', '.') }}</h3>
                <p class="text-[11px] sm:text-xs text-[#667085] mt-0.5 m-0 truncate leading-snug">Termasuk modal awal kasir</p>
            </div>
            <div class="w-9 sm:w-10 h-9 sm:h-10 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

