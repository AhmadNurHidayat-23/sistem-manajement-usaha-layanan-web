{{-- Sub-komponen Modal Pembayaran: Ringkasan Kalkulasi, Promo, & Total Tagihan --}}
<div class="bg-white border border-[#E4E7EC] rounded-xl p-4 space-y-2.5 text-xs">
    {{-- Voucher / Promo Code Input --}}
    <div class="pb-2.5 border-b border-[#E4E7EC] space-y-1.5">
        <label for="pay_promo_input" class="block font-bold text-[#101828]">Kode Voucher / Promo (Opsional)</label>
        <div class="flex items-center gap-2">
            <input type="text" id="pay_promo_input" placeholder="Masukkan kode promo" class="flex-1 uppercase px-3 py-1.5 bg-[#F8FAFC] border border-[#D0D5DD] rounded-lg text-xs font-semibold text-[#101828] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#062072]">
            <button type="button" id="btn-apply-promo" class="px-3.5 py-1.5 bg-[#062072] text-white hover:bg-[#0B3A9E] rounded-lg text-xs font-bold transition-colors shadow-2xs cursor-pointer">
                Terapkan
            </button>
        </div>
        <div id="pay-promo-message" class="hidden text-[11px] font-semibold"></div>
    </div>

    {{-- Detail Subtotal & Potongan --}}
    <div class="space-y-1.5 pt-1">
        <div class="flex justify-between text-[#667085]">
            <span>Subtotal Layanan</span>
            <span id="pay-calc-subtotal" class="font-bold text-[#101828]">Rp 0</span>
        </div>
        <div id="pay-row-discount" class="flex justify-between text-emerald-600 hidden">
            <span>Diskon Promo</span>
            <span id="pay-calc-discount" class="font-bold">- Rp 0</span>
        </div>
        <div id="pay-row-paid" class="flex justify-between text-blue-600 hidden">
            <span>Sudah Dibayar (Uang Muka / DP)</span>
            <span id="pay-calc-paid" class="font-bold">- Rp 0</span>
        </div>
        <div class="border-t border-[#E4E7EC] pt-2 flex justify-between items-center text-sm">
            <span class="font-extrabold text-[#101828]">Sisa Tagihan (Wajib Dilunasi)</span>
            <span id="pay-calc-remaining" class="text-base font-black text-[#062072]">Rp 0</span>
        </div>
    </div>
</div>

