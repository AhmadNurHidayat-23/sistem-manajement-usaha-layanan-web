<!-- Reusable Component: Metode Pembayaran Kasir -->
<div class="space-y-4">
    <div>
        <label class="block text-xs font-semibold text-[#101828] mb-1.5">Metode Pembayaran</label>
        <div class="grid grid-cols-5 gap-1.5">
            <label class="payment-method-label cursor-pointer border border-[#E4E7EC] rounded-lg p-2 flex flex-col items-center justify-center text-center gap-1 hover:border-[#062072] has-[:checked]:border-[#062072] has-[:checked]:bg-blue-50/50 transition-all">
                <input type="radio" name="payment_method" value="cash" checked onchange="togglePaymentFields('cash')" class="sr-only">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="text-[11px] font-bold text-[#101828]">Tunai</span>
            </label>

            <label class="payment-method-label cursor-pointer border border-[#E4E7EC] rounded-lg p-2 flex flex-col items-center justify-center text-center gap-1 hover:border-[#062072] has-[:checked]:border-[#062072] has-[:checked]:bg-blue-50/50 transition-all">
                <input type="radio" name="payment_method" value="qris" onchange="togglePaymentFields('qris')" class="sr-only">
                <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                </svg>
                <span class="text-[11px] font-bold text-[#101828]">QRIS</span>
            </label>

            <label class="payment-method-label cursor-pointer border border-[#E4E7EC] rounded-lg p-2 flex flex-col items-center justify-center text-center gap-1 hover:border-[#062072] has-[:checked]:border-[#062072] has-[:checked]:bg-blue-50/50 transition-all">
                <input type="radio" name="payment_method" value="transfer" onchange="togglePaymentFields('transfer')" class="sr-only">
                <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span class="text-[11px] font-bold text-[#101828]">Transfer</span>
            </label>

            <label class="payment-method-label cursor-pointer border border-[#E4E7EC] rounded-lg p-2 flex flex-col items-center justify-center text-center gap-1 hover:border-[#062072] has-[:checked]:border-[#062072] has-[:checked]:bg-blue-50/50 transition-all">
                <input type="radio" name="payment_method" value="debit" onchange="togglePaymentFields('debit')" class="sr-only">
                <svg class="w-4 h-4 text-[#0B3A9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <span class="text-[11px] font-bold text-[#101828]">Debit</span>
            </label>

            <label class="payment-method-label cursor-pointer border border-[#E4E7EC] rounded-lg p-2 flex flex-col items-center justify-center text-center gap-1 hover:border-[#062072] has-[:checked]:border-[#062072] has-[:checked]:bg-blue-50/50 transition-all">
                <input type="radio" name="payment_method" value="credit" onchange="togglePaymentFields('credit')" class="sr-only">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <span class="text-[11px] font-bold text-[#101828]">Kredit</span>
            </label>
        </div>
    </div>

    <!-- Cash Specific Fields -->
    <div id="cash-fields" class="space-y-2">
        <div>
            <label for="cash_received" class="block text-xs font-semibold text-[#101828] mb-1">Nominal Uang Diterima (Rp)</label>
            <input type="number" id="cash_received" name="cash_received" value="0" min="0" oninput="calculateChange()" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm font-bold text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
        </div>

        <!-- Quick Cash Buttons -->
        <div class="grid grid-cols-4 gap-1.5 text-[10px] font-bold">
            <button type="button" onclick="setExactCash()" class="py-1 bg-[#F5F8FC] hover:bg-[#DCE8FF] text-[#062072] border border-[#E4E7EC] rounded transition-colors">Uang Pas</button>
            <button type="button" onclick="addCash(50000)" class="py-1 bg-[#F5F8FC] hover:bg-[#DCE8FF] text-[#062072] border border-[#E4E7EC] rounded transition-colors">50k</button>
            <button type="button" onclick="addCash(100000)" class="py-1 bg-[#F5F8FC] hover:bg-[#DCE8FF] text-[#062072] border border-[#E4E7EC] rounded transition-colors">100k</button>
            <button type="button" onclick="addCash(200000)" class="py-1 bg-[#F5F8FC] hover:bg-[#DCE8FF] text-[#062072] border border-[#E4E7EC] rounded transition-colors">200k</button>
        </div>

        <div class="p-2.5 bg-emerald-50 border border-emerald-100 rounded-lg flex justify-between items-center text-xs">
            <span class="font-medium text-emerald-800">Kembalian:</span>
            <span id="change-display" class="font-bold text-emerald-700 text-sm">Rp 0</span>
        </div>
    </div>

    <!-- Non-Cash Reference Field -->
    <div id="non-cash-fields" class="hidden space-y-1">
        <label for="reference_number" class="block text-xs font-semibold text-[#101828]">Nomor Referensi / Approval Code</label>
        <input type="text" id="reference_number" name="reference_number" placeholder="Contoh: REF-9281729 / 4 digit kartu" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
    </div>
</div>
