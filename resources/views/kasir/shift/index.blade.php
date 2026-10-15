<!-- Shift Status Card / Panel -->
@if ($activeShift)
    <div class="bg-white border border-[#E4E7EC] rounded-xl p-4 flex flex-wrap items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">Shift Aktif</span>
                    <span class="text-xs text-[#667085]">Dibuka: {{ $activeShift->opened_at->format('H:i') }} WIB ({{ $activeShift->opened_at->format('d M Y') }})</span>
                </div>
                <p class="text-xs font-semibold text-[#101828] mt-0.5 m-0">
                    Kasir: {{ $activeShift->user?->name ?? 'Kasir' }} &bull; Modal Awal: Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-6 text-xs">
            <div>
                <span class="text-[#667085] block">Total Transaksi</span>
                <span class="font-bold text-[#101828] text-sm">{{ $shiftSummary['total_transactions'] ?? 0 }} TRX</span>
            </div>
            <div>
                <span class="text-[#667085] block">Total Penjualan</span>
                <span class="font-bold text-emerald-600 text-sm">Rp {{ number_format($shiftSummary['total_sales'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-[#667085] block">Kas Tunai di Laci</span>
                <span class="font-bold text-[#062072] text-sm">Rp {{ number_format($shiftSummary['expected_cash'] ?? $activeShift->opening_cash, 0, ',', '.') }}</span>
            </div>
            <button type="button" onclick="openCloseShiftModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg text-xs font-semibold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Tutup Shift
            </button>
        </div>
    </div>
@else
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div>
                <h5 class="text-sm font-bold text-amber-900 m-0">Shift Kasir Belum Dibuka</h5>
                <p class="text-xs text-amber-700 mt-0.5 m-0">Buka shift kasir terlebih dahulu dan catat modal awal laci untuk dapat memproses transaksi penjualan.</p>
            </div>
        </div>
        <button type="button" onclick="openOpenShiftModal()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold whitespace-nowrap shadow-sm transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
            </svg>
            Buka Shift Sekarang
        </button>
    </div>
@endif
