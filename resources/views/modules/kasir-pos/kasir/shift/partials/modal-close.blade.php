<!-- Modal: Tutup Shift Kasir -->
@if ($activeShift)
<div id="modal-close-shift" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full border border-[#E4E7EC] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E4E7EC] bg-[#F5F8FC] flex items-center justify-between">
            <h4 class="text-sm font-bold text-[#101828] m-0 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Tutup Shift Kasir
            </h4>
            <button type="button" onclick="closeCloseShiftModal()" class="text-[#667085] hover:text-[#101828]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('pos.shift.close') }}" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $activeShift->branch_id }}">

            <div class="bg-[#F5F8FC] p-3.5 rounded-lg border border-[#E4E7EC] space-y-1.5 text-xs">
                <div class="flex justify-between text-[#667085]">
                    <span>Modal Awal Kas:</span>
                    <span class="font-semibold text-[#101828]">Rp {{ number_format($shiftSummary['opening_cash'] ?? $activeShift->opening_cash ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-[#667085]">
                    <span>Penjualan Tunai:</span>
                    <span class="font-semibold text-emerald-600">+ Rp {{ number_format($shiftSummary['cash_sales'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-[#667085]">
                    <span>Penjualan Non-Tunai:</span>
                    <span class="font-semibold text-blue-600">Rp {{ number_format($shiftSummary['non_cash_sales'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-[#E4E7EC] pt-1.5 flex justify-between font-bold text-sm text-[#062072]">
                    <span>Ekspektasi Uang di Laci:</span>
                    <span id="expected-cash-val" data-expected="{{ $shiftSummary['expected_cash'] ?? $activeShift->opening_cash ?? 0 }}">
                        Rp {{ number_format($shiftSummary['expected_cash'] ?? $activeShift->opening_cash ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div>
                <label for="modal_closing_cash" class="block text-xs font-semibold text-[#101828] mb-1">Total Uang Fisik Hasil Hitung (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" id="modal_closing_cash" name="closing_cash" value="{{ $shiftSummary['expected_cash'] ?? $activeShift->opening_cash ?? 0 }}" min="0" step="1000" oninput="auditCashDiff(this.value)" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm font-bold text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                <div id="diff-alert" class="mt-1.5 text-[11px] font-semibold text-emerald-600">
                    Kas Seimbang (Sesuai ekspektasi)
                </div>
            </div>

            <div>
                <label for="modal_shift_notes" class="block text-xs font-semibold text-[#101828] mb-1">Catatan Penutupan Shift (Opsional)</label>
                <textarea id="modal_shift_notes" name="notes" rows="2" placeholder="Catatan serah terima kas atau alasan selisih kas..." class="w-full px-3 py-1.5 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]"></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeCloseShiftModal()" class="px-4 py-2 bg-[#F5F8FC] hover:bg-[#E4E7EC] text-xs font-semibold text-[#667085] rounded-lg transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-xs font-bold text-white rounded-lg shadow-sm transition-colors">
                    Tutup Shift Kasir
                </button>
            </div>
        </form>
    </div>
</div>
@endif
