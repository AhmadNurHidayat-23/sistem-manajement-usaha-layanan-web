<!-- Modal Tambah Stok Bahan -->
<div id="modal-tambah-stok" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="modal-tambah-stok-title">
    <!-- Backdrop overlay gelap dengan efek blur -->
    <div id="modal-tambah-stok-backdrop" class="fixed inset-0 bg-[#0A1224]/70 dark:bg-[#030712]/80 backdrop-blur-xs transition-opacity" aria-hidden="true"></div>

    <!-- Modal Box: solid, tajam, elegan, mendukung Dark Mode -->
    <div class="relative w-full max-w-lg bg-white dark:bg-[#111C35] rounded-2xl border border-[#E4E7EC] dark:border-[#1E2D4A] shadow-2xl overflow-hidden z-10 transition-all transform scale-100 flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E4E7EC] dark:border-[#1E2D4A] bg-[#F8F9FC] dark:bg-[#0D1527] shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-200 dark:border-emerald-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </div>
                <div>
                    <h3 id="modal-tambah-stok-title" class="text-base font-bold text-[#101828] dark:text-white m-0">Tambah Stok Bahan</h3>
                    <p class="text-xs text-[#667085] dark:text-[#94A3B8] mt-0.5 m-0 line-clamp-1">{{ $item->name }}</p>
                </div>
            </div>

            <!-- Tombol X Tutup Modal -->
            <button
                type="button"
                id="modal-tambah-stok-btn-close"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-[#667085] hover:text-[#101828] dark:text-[#94A3B8] dark:hover:text-white hover:bg-white dark:hover:bg-[#1E2D4A] border border-transparent hover:border-[#E4E7EC] dark:hover:border-[#2E3F66] transition-colors cursor-pointer"
                title="Tutup Modal (Esc)"
                aria-label="Tutup Modal"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Form Tambah Stok -->
        <form method="POST" action="{{ route('inventori.stock-in', $item) }}" id="form-tambah-stok" class="flex flex-col flex-1 overflow-y-auto">
            @csrf

            <div class="p-6 space-y-4 flex-1">
                <!-- Info Ringkas Stok Saat Ini -->
                <div class="flex items-center justify-between p-3.5 bg-[#F5F8FC] dark:bg-[#0A1224] rounded-xl border border-[#E4E7EC] dark:border-[#1E2D4A]">
                    <div>
                        <span class="text-[11px] font-semibold text-[#667085] dark:text-[#94A3B8] uppercase tracking-wider block">Stok Saat Ini</span>
                        <span class="text-sm font-bold text-[#101828] dark:text-white">
                            {{ rtrim(rtrim(number_format($item->current_stock, 2, ',', '.'), '0'), ',') }} {{ $item->unit }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-[11px] font-semibold text-[#667085] dark:text-[#94A3B8] uppercase tracking-wider block">Cabang</span>
                        <span class="text-xs font-semibold text-[#062072] dark:text-[#93C5FD]">
                            {{ $item->branch?->name ?? '-' }}
                        </span>
                    </div>
                </div>

                <!-- Field Jumlah Masuk -->
                <div>
                    <label for="modal_in_quantity" class="block text-xs font-semibold text-[#344054] dark:text-[#CBD5E1] mb-1.5">
                        Jumlah Masuk ({{ $item->unit }}) <span class="text-rose-600 dark:text-rose-400">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            step="0.01"
                            min="0.01"
                            name="quantity"
                            id="modal_in_quantity"
                            required
                            placeholder="0.00"
                            class="w-full text-sm rounded-xl border border-[#D0D5DD] dark:border-[#2E3F66] bg-white dark:bg-[#0D1527] px-3.5 py-2.5 text-[#101828] dark:text-white placeholder-[#98A2B3] focus:border-[#062072] dark:focus:border-[#3B82F6] focus:ring-2 focus:ring-[#062072]/20 dark:focus:ring-[#3B82F6]/20 transition-all outline-none"
                        >
                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-medium text-[#667085] dark:text-[#94A3B8] pointer-events-none">
                            {{ $item->unit }}
                        </span>
                    </div>
                    <p class="text-[11px] text-[#667085] dark:text-[#94A3B8] mt-1 m-0">Masukkan kuantitas fisik bahan yang diterima.</p>
                </div>

                <!-- Field Kategori Referensi -->
                <div>
                    <label for="modal_in_reference" class="block text-xs font-semibold text-[#344054] dark:text-[#CBD5E1] mb-1.5">
                        Kategori Referensi <span class="text-rose-600 dark:text-rose-400">*</span>
                    </label>
                    <select
                        name="reference_type"
                        id="modal_in_reference"
                        required
                        class="w-full text-sm rounded-xl border border-[#D0D5DD] dark:border-[#2E3F66] bg-white dark:bg-[#0D1527] px-3.5 py-2.5 text-[#101828] dark:text-white focus:border-[#062072] dark:focus:border-[#3B82F6] focus:ring-2 focus:ring-[#062072]/20 dark:focus:ring-[#3B82F6]/20 transition-all outline-none"
                    >
                        <option value="restock">Pembelian / Tambah Stok Supplier</option>
                        <option value="transfer_in">Transfer Masuk dari Cabang Lain</option>
                        <option value="return_customer">Retur / Pengembalian</option>
                        <option value="correction">Koreksi Penambahan Stok</option>
                    </select>
                </div>

                <!-- Field Catatan / No Faktur -->
                <div>
                    <label for="modal_in_notes" class="block text-xs font-semibold text-[#344054] dark:text-[#CBD5E1] mb-1.5">
                        Catatan / No. Faktur
                    </label>
                    <input
                        type="text"
                        name="notes"
                        id="modal_in_notes"
                        placeholder="Contoh: Faktur Toko Sejahtera #PO-1049"
                        class="w-full text-sm rounded-xl border border-[#D0D5DD] dark:border-[#2E3F66] bg-white dark:bg-[#0D1527] px-3.5 py-2.5 text-[#101828] dark:text-white placeholder-[#98A2B3] focus:border-[#062072] dark:focus:border-[#3B82F6] focus:ring-2 focus:ring-[#062072]/20 dark:focus:ring-[#3B82F6]/20 transition-all outline-none"
                    >
                    <p class="text-[11px] text-[#667085] dark:text-[#94A3B8] mt-1 m-0">Keterangan faktur, nomor PO, atau nama supplier (opsional).</p>
                </div>
            </div>

            <!-- Modal Footer Aksi -->
            <div class="px-6 py-4 bg-[#F8F9FC] dark:bg-[#0D1527] border-t border-[#E4E7EC] dark:border-[#1E2D4A] flex items-center justify-end gap-3 shrink-0">
                <button
                    type="button"
                    id="modal-tambah-stok-btn-cancel"
                    class="px-4 py-2.5 rounded-xl border border-[#D0D5DD] dark:border-[#2E3F66] bg-white dark:bg-[#111C35] hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] text-[#344054] dark:text-[#CBD5E1] text-xs font-semibold transition-colors cursor-pointer shadow-2xs"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    id="modal-tambah-stok-btn-submit"
                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition-colors cursor-pointer shadow-xs flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Simpan Stok</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        const modal = document.getElementById('modal-tambah-stok');
        const backdrop = document.getElementById('modal-tambah-stok-backdrop');
        const btnClose = document.getElementById('modal-tambah-stok-btn-close');
        const btnCancel = document.getElementById('modal-tambah-stok-btn-cancel');
        const inputQty = document.getElementById('modal_in_quantity');
        const form = document.getElementById('form-tambah-stok');

        function openModal() {
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                if (inputQty) inputQty.focus();
            }, 100);
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            if (form) form.reset();
        }

        window.openModalTambahStok = openModal;
        window.closeModalTambahStok = closeModal;

        if (btnClose) btnClose.addEventListener('click', closeModal);
        if (btnCancel) btnCancel.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();
</script>
