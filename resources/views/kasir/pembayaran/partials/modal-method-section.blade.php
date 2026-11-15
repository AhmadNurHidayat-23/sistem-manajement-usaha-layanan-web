{{-- Sub-komponen Modal Pembayaran: Pemilihan Metode Pembayaran & Input Tunai/Non-Tunai --}}
<div class="space-y-3">
    <label class="block text-xs font-bold text-[#101828] m-0">Metode Pembayaran</label>

    {{-- Radio Cards Metode Pembayaran --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
        <label class="cursor-pointer border border-[#E4E7EC] rounded-xl p-2.5 flex flex-col items-center justify-center text-center gap-1.5 hover:border-[#062072] has-[:checked]:border-[#062072] has-[:checked]:bg-blue-50/50 transition-all">
            <input type="radio" name="pay_payment_method" value="cash" checked class="sr-only">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span class="text-xs font-bold text-[#101828]">Tunai</span>
        </label>

        <label class="cursor-pointer border border-[#E4E7EC] rounded-xl p-2.5 flex flex-col items-center justify-center text-center gap-1.5 hover:border-[#062072] has-[:checked]:border-[#062072] has-[:checked]:bg-blue-50/50 transition-all">
            <input type="radio" name="pay_payment_method" value="qris" class="sr-only">
            <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
            </svg>
            <span class="text-xs font-bold text-[#101828]">QRIS</span>
        </label>

        <label class="cursor-pointer border border-[#E4E7EC] rounded-xl p-2.5 flex flex-col items-center justify-center text-center gap-1.5 hover:border-[#062072] has-[:checked]:border-[#062072] has-[:checked]:bg-blue-50/50 transition-all">
            <input type="radio" name="pay_payment_method" value="transfer" class="sr-only">
            <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
            </svg>
            <span class="text-xs font-bold text-[#101828]">Transfer</span>
        </label>

        <label class="cursor-pointer border border-[#E4E7EC] rounded-xl p-2.5 flex flex-col items-center justify-center text-center gap-1.5 hover:border-[#062072] has-[:checked]:border-[#062072] has-[:checked]:bg-blue-50/50 transition-all">
            <input type="radio" name="pay_payment_method" value="debit" class="sr-only">
            <svg class="w-4 h-4 text-[#0B3A9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
            <span class="text-xs font-bold text-[#101828]">Debit</span>
        </label>

        <label class="cursor-pointer border border-[#E4E7EC] rounded-xl p-2.5 flex flex-col items-center justify-center text-center gap-1.5 hover:border-[#062072] has-[:checked]:border-[#062072] has-[:checked]:bg-blue-50/50 transition-all">
            <input type="radio" name="pay_payment_method" value="credit" class="sr-only">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
            <span class="text-xs font-bold text-[#101828]">Kredit</span>
        </label>
    </div>

    {{-- Field Khusus Tunai (Cash) --}}
    <div id="pay-cash-container" class="space-y-3 bg-[#F8FAFC] border border-[#E4E7EC] rounded-xl p-3.5">
        <div>
            <label for="pay_cash_received" class="block text-xs font-bold text-[#101828] mb-1">
                Nominal Uang Diterima (Rp) <span class="text-rose-500">*</span>
            </label>
            <input type="number" id="pay_cash_received" name="cash_received" value="0" min="0" class="w-full px-3 py-2 bg-white border border-[#D0D5DD] rounded-lg text-sm font-bold text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
        </div>

        {{-- Quick Cash Buttons --}}
        <div class="grid grid-cols-4 gap-1.5 text-xs font-semibold">
            <button type="button" data-cash-action="exact" class="py-1.5 bg-white hover:bg-[#DCE8FF] text-[#062072] border border-[#E4E7EC] rounded-lg transition-colors cursor-pointer">Uang Pas</button>
            <button type="button" data-cash-action="add" data-cash-val="50000" class="py-1.5 bg-white hover:bg-[#DCE8FF] text-[#062072] border border-[#E4E7EC] rounded-lg transition-colors cursor-pointer">+50k</button>
            <button type="button" data-cash-action="add" data-cash-val="100000" class="py-1.5 bg-white hover:bg-[#DCE8FF] text-[#062072] border border-[#E4E7EC] rounded-lg transition-colors cursor-pointer">+100k</button>
            <button type="button" data-cash-action="add" data-cash-val="200000" class="py-1.5 bg-white hover:bg-[#DCE8FF] text-[#062072] border border-[#E4E7EC] rounded-lg transition-colors cursor-pointer">+200k</button>
        </div>

        {{-- Kembalian Display --}}
        <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-lg flex justify-between items-center text-xs">
            <span class="font-bold text-emerald-900">Kembalian:</span>
            <span id="pay-change-display" class="font-extrabold text-emerald-700 text-sm">Rp 0</span>
        </div>

        {{-- Warning uang kurang --}}
        <div id="pay-cash-warning" class="hidden text-[11px] text-rose-600 font-semibold flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span>Uang yang diterima kurang dari sisa tagihan yang harus dibayar.</span>
        </div>
    </div>

    {{-- Field Khusus Non-Tunai / Digital --}}
    <div id="pay-non-cash-container" class="hidden space-y-2 bg-[#F8FAFC] border border-[#E4E7EC] rounded-xl p-3.5 text-xs">
        <label for="pay_reference_number" class="block font-bold text-[#101828]">Nomor Referensi / Approval Code (Opsional)</label>
        <input type="text" id="pay_reference_number" name="reference_number" placeholder="Contoh: REF-9281729 / 4 digit nomor kartu" class="w-full px-3 py-2 bg-white border border-[#D0D5DD] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
        <p class="text-[11px] text-[#667085] m-0">Untuk QRIS Midtrans, kode QR atau halaman pembayaran akan diproses melalui gateway SIMAUL.</p>
    </div>
</div>

