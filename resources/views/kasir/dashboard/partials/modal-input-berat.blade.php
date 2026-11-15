<!-- Modal Input Berat Laundry Kiloan -->
<div id="modal-input-berat" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs hidden" role="dialog" aria-modal="true" aria-labelledby="modal-input-berat-title">
    <div class="bg-white rounded-2xl shadow-xl border border-[#E4E7EC] w-full max-w-md overflow-hidden transform transition-all">
        <!-- Header Modal -->
        <div class="px-5 py-4 border-b border-[#E4E7EC] flex items-center justify-between bg-[#F8FAFC]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-[#062072] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                    </svg>
                </div>
                <div>
                    <h3 id="modal-input-berat-title" class="text-base font-bold text-[#101828] m-0">Timbang Laundry Kiloan</h3>
                    <p class="text-xs text-[#667085] m-0 mt-0.5">Catat berat riil untuk menghitung tagihan pesanan.</p>
                </div>
            </div>
            <button type="button" data-action="close-weight-modal" class="text-[#98A2B3] hover:text-[#475467] p-1.5 rounded-lg hover:bg-[#F2F4F7] transition-colors cursor-pointer" aria-label="Tutup Modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Body Form -->
        <form id="form-input-berat" class="p-5 space-y-4">
            <input type="hidden" id="weight-order-id-input" name="order_id" value="">
            <input type="hidden" id="weight-submit-url" value="">
            <input type="hidden" id="weight-price-per-kg-raw" value="0">

            <!-- Detail Pesanan Card -->
            <div class="bg-[#F8FAFC] border border-[#E4E7EC] rounded-xl p-3.5 space-y-2 text-xs">
                <div class="flex justify-between items-center">
                    <span class="text-[#667085]">No. Pesanan:</span>
                    <span id="weight-order-code" class="font-bold text-[#101828]">#--</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[#667085]">Pelanggan:</span>
                    <span id="weight-customer-name" class="font-semibold text-[#101828]">--</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[#667085]">Layanan:</span>
                    <span id="weight-service-name" class="font-medium text-[#101828]">--</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-[#E4E7EC]">
                    <span class="text-[#667085]">Tarif per Kg:</span>
                    <span id="weight-price-per-kg-display" class="font-bold text-[#062072]">Rp 0</span>
                </div>
            </div>

            <!-- Error Box -->
            <div id="weight-modal-error" class="hidden p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700"></div>

            <!-- Input Berat Riil -->
            <div>
                <label for="weight-actual-input" class="block text-xs font-semibold text-[#344054] mb-1.5">
                    Berat Aktual Timbangan <span class="text-red-500">*</span>
                </label>
                <div class="relative rounded-lg shadow-2xs">
                    <input type="number"
                        id="weight-actual-input"
                        name="actual_weight"
                        step="0.01"
                        min="0.01"
                        max="999.99"
                        placeholder="Contoh: 4.5"
                        required
                        class="w-full pl-3.5 pr-12 py-2.5 text-sm bg-white border border-[#D0D5DD] rounded-lg text-[#101828] font-semibold focus:outline-hidden focus:ring-2 focus:ring-[#062072] focus:border-[#062072] tabular-nums">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-xs font-bold text-[#667085]">
                        kg
                    </div>
                </div>
                <p class="text-[11px] text-[#667085] mt-1 m-0">Gunakan titik (.) untuk desimal, contoh: 3.25 kg.</p>
            </div>

            <!-- Dropdown Petugas Pengerjaan -->
            <div>
                <label for="weight-employee-select" class="block text-xs font-semibold text-[#344054] mb-1.5">
                    Petugas Pengerjaan <span class="text-red-500">*</span>
                </label>
                <select id="weight-employee-select"
                    name="employee_id"
                    required
                    class="w-full px-3.5 py-2.5 text-xs bg-white border border-[#D0D5DD] rounded-lg text-[#101828] font-medium focus:outline-hidden focus:ring-2 focus:ring-[#062072] focus:border-[#062072]">
                    <option value="">— Pilih Petugas —</option>
                </select>
                <p class="text-[11px] text-[#667085] mt-1 m-0">Karyawan yang bertugas mengerjakan cucian ini.</p>
            </div>

            <!-- Kalkulasi Tagihan Otomatis -->
            <div class="bg-blue-50/60 border border-blue-200/80 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-[#062072] uppercase tracking-wider block">Total Tagihan Baru</span>
                    <span id="weight-calc-formula" class="text-[11px] text-[#475467] block">0 kg × Rp 0</span>
                </div>
                <div class="text-right">
                    <span id="weight-calculated-total" class="text-base font-extrabold text-[#062072] tabular-nums block">Rp 0</span>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-3 border-t border-[#E4E7EC] flex items-center justify-end gap-2.5">
                <button type="button"
                    data-action="close-weight-modal"
                    class="px-4 py-2 text-xs font-semibold text-[#344054] bg-white border border-[#D0D5DD] rounded-lg hover:bg-[#F9FAFB] transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    id="btn-submit-weight"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-[#062072] hover:bg-[#0B3A9E] rounded-lg shadow-2xs transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg id="btn-submit-weight-spinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="btn-submit-weight-text">Kirim</span>
                </button>
            </div>
        </form>
    </div>
</div>

