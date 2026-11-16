<!-- Modal Terima Pesanan Drop-off -->
<div id="modal-terima-pesanan"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-terima-title"
    data-backdrop-close="true">
    <div class="bg-white rounded-2xl shadow-xl border border-[#E4E7EC] w-full max-w-md overflow-hidden transform transition-all">
        <!-- Header Modal -->
        <div class="px-5 py-4 border-b border-[#E4E7EC] flex items-center justify-between bg-[#F8FAFC]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-[#062072] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <h3 id="modal-terima-title" class="text-base font-bold text-[#101828] m-0">Terima Pesanan</h3>
                    <p class="text-xs text-[#667085] m-0 mt-0.5">Konfirmasi barang fisik telah sampai di toko.</p>
                </div>
            </div>
            <button type="button"
                data-action="close-receive-modal"
                data-modal-close="modal-terima-pesanan"
                class="text-[#98A2B3] hover:text-[#475467] p-1.5 rounded-lg hover:bg-[#F2F4F7] transition-colors cursor-pointer"
                aria-label="Tutup Modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Body Form -->
        <form id="form-terima-pesanan" class="p-5 space-y-4">
            <input type="hidden" id="receive-order-id-input" name="order_id" value="">
            <input type="hidden" id="receive-submit-url" value="">
            <input type="hidden" id="receive-is-weight-based" value="0">

            <!-- Detail Pesanan Card -->
            <div class="bg-[#F8FAFC] border border-[#E4E7EC] rounded-xl p-3.5 space-y-2 text-xs">
                <div class="flex justify-between items-center">
                    <span class="text-[#667085]">No. Pesanan:</span>
                    <span id="receive-order-code" class="font-bold text-[#101828]">#--</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[#667085]">Pelanggan:</span>
                    <span id="receive-customer-name" class="font-semibold text-[#101828]">--</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[#667085]">Layanan:</span>
                    <span id="receive-service-name" class="font-medium text-[#101828]">--</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[#667085]">Estimasi Barang:</span>
                    <span id="receive-estimated-qty" class="font-semibold text-[#101828]">--</span>
                </div>
            </div>

            <!-- Pesan Konfirmasi Penerimaan -->
            <div class="p-3 rounded-xl bg-slate-50 border border-[#E4E7EC] text-xs text-[#344054] flex items-start gap-2.5">
                <div class="w-5 h-5 rounded-full bg-blue-100 text-[#062072] flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p id="receive-confirmation-message" class="text-xs text-[#475467] leading-relaxed m-0">
                    Konfirmasi penerimaan barang fisik di toko.
                </p>
            </div>

            <!-- Error Box -->
            <div id="receive-modal-error" class="hidden p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700"></div>

            <!-- Dropdown Petugas untuk Layanan Non-Kiloan -->
            <div id="receive-employee-container">
                <label for="receive-employee-select" class="block text-xs font-semibold text-[#344054] mb-1.5">
                    Petugas Pengerjaan <span class="text-red-500">*</span>
                </label>
                <select id="receive-employee-select"
                    name="employee_id"
                    class="w-full px-3.5 py-2.5 text-xs bg-white border border-[#D0D5DD] rounded-lg text-[#101828] font-medium focus:outline-hidden focus:ring-2 focus:ring-[#062072] focus:border-[#062072]">
                    <option value="">— Pilih Petugas —</option>
                </select>
                <p class="text-[11px] text-[#667085] mt-1 m-0">Hanya menampilkan karyawan aktif dari cabang pesanan ini.</p>
            </div>

            <!-- Info Box untuk Layanan Kiloan -->
            <div id="receive-kiloan-info" class="hidden p-3 rounded-xl bg-blue-50/70 border border-blue-200 text-xs text-[#062072] space-y-1">
                <div class="font-semibold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#062072] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Layanan Laundry Kiloan
                </div>
                <p class="text-[11px] text-[#475467] m-0">
                    Setelah pesanan diterima, tombol aksi akan berubah menjadi <strong>Timbang</strong> untuk penimbangan berat aktual dan penugasan petugas.
                </p>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-3 border-t border-[#E4E7EC] flex items-center justify-end gap-2.5">
                <button type="button"
                    data-action="close-receive-modal"
                    data-modal-close="modal-terima-pesanan"
                    class="px-4 py-2 text-xs font-semibold text-[#344054] bg-white border border-[#D0D5DD] rounded-lg hover:bg-[#F9FAFB] transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    id="btn-submit-receive"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-[#062072] hover:bg-[#0B3A9E] rounded-lg shadow-2xs transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg id="btn-submit-receive-spinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="btn-submit-receive-text">Terima</span>
                </button>
            </div>
        </form>
    </div>
</div>
