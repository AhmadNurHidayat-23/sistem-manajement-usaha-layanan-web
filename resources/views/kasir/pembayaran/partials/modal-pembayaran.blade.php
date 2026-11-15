{{-- Modal Keranjang & Pembayaran Terpadu SIMAUL (Dashboard Kasir & Katalog Layanan) --}}
<div id="modal-pembayaran-kasir" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/50 backdrop-blur-[3px] flex items-center justify-center p-4 transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="pay-modal-title">
    <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-[#E7ECF3] overflow-hidden transform transition-all max-h-[92vh] flex flex-col text-left">
        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E4E7EC] bg-[#F8FAFC] shrink-0">
            <div>
                <h3 id="pay-modal-title" class="text-base sm:text-lg font-bold text-[#101828] m-0 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>Keranjang & Pembayaran</span>
                </h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Periksa detail layanan dan selesaikan transaksi kasir.</p>
            </div>
            <button type="button" id="btn-close-pay-modal" class="text-[#667085] hover:text-[#101828] p-1.5 rounded-lg hover:bg-[#E4E7EC]/50 transition-colors cursor-pointer" aria-label="Tutup Modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Konten Modal (Scrollable) --}}
        <div class="p-6 overflow-y-auto space-y-5 flex-1" id="pay-modal-body">
            {{-- State Sukses Pembayaran --}}
            <div id="pay-success-state" class="hidden text-center py-6 space-y-4">
                <div class="w-16 h-16 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-[#101828] m-0">Pembayaran Berhasil!</h4>
                    <p id="pay-success-message" class="text-xs text-[#667085] mt-1 m-0">Transaksi telah lunas dan tercatat dalam sistem.</p>
                </div>
                <div class="bg-[#F8FAFC] border border-[#E4E7EC] rounded-xl p-4 max-w-md mx-auto text-xs space-y-2 text-left">
                    <div class="flex justify-between">
                        <span class="text-[#667085]">No. Transaksi:</span>
                        <span id="pay-success-code" class="font-bold text-[#101828]">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#667085]">Total Dibayar:</span>
                        <span id="pay-success-total" class="font-bold text-[#062072]">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#667085]">Metode:</span>
                        <span id="pay-success-method" class="font-semibold text-[#101828] uppercase">-</span>
                    </div>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <a id="pay-success-receipt-btn" href="#" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <span>Cetak / Lihat Struk</span>
                    </a>
                    <button type="button" id="btn-success-close-modal" class="px-4 py-2 border border-[#D0D5DD] bg-white text-[#344054] hover:bg-[#F5F8FC] text-xs font-semibold rounded-lg transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>

            {{-- Form Pembayaran --}}
            <form id="cashier-pay-form" class="space-y-5">
                <input type="hidden" id="pay_branch_id" name="branch_id" value="">
                <input type="hidden" id="pay_booking_id" name="booking_id" value="">
                <input type="hidden" id="pay_order_id" name="order_id" value="">
                <input type="hidden" id="pay_customer_name" name="customer_name" value="">
                <input type="hidden" id="pay_customer_phone" name="customer_phone" value="">
                <input type="hidden" id="pay_promo_code" name="promo_code" value="">
                <input type="hidden" id="pay_discount_amount" name="discount" value="0">

                {{-- Alert Error --}}
                <div id="pay-error-banner" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-start gap-2">
                    <svg class="w-4 h-4 shrink-0 text-rose-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span id="pay-error-text">Terjadi kesalahan pada proses pembayaran.</span>
                </div>

                {{-- Sub-komponen 1: Identitas & List Item Layanan (dengan Add Service) --}}
                @include('kasir.pembayaran.partials.modal-items-section')

                {{-- Sub-komponen 2: Ringkasan Kalkulasi & Promo --}}
                @include('kasir.pembayaran.partials.modal-summary-section')

                {{-- Sub-komponen 3: Metode Pembayaran (Tunai & Digital) --}}
                @include('kasir.pembayaran.partials.modal-method-section')

                {{-- Footer Tombol Submit --}}
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#E4E7EC]">
                    <button type="button" id="btn-cancel-pay-modal" class="px-4 py-2.5 text-xs font-semibold rounded-lg border border-[#D0D5DD] bg-white text-[#344054] hover:bg-[#F5F8FC] transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-cashier-pay-submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-bold rounded-lg bg-[#062072] text-white hover:bg-[#0B3A9E] transition-all cursor-pointer shadow-md">
                        <span id="cashier-pay-spinner" class="hidden">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <svg id="cashier-pay-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span id="cashier-pay-text">Proses Pembayaran</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

