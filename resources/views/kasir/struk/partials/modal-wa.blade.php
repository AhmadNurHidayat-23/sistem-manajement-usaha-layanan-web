{{-- Modal Input Nomor WhatsApp Pelanggan --}}
<div id="modal-wa-receipt"
     class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/50 backdrop-blur-[2px] flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-wa-title"
     data-backdrop-close>
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-[#E7ECF3] overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7ECF3] bg-white sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div>
                    <h3 id="modal-wa-title" class="text-base font-bold text-[#101828] m-0">Kirim Struk WhatsApp</h3>
                    <p class="text-xs text-[#667085] mt-0.5 m-0">No. Transaksi #{{ $transaction->transaction_code }}</p>
                </div>
            </div>
            <button type="button" data-modal-close="modal-wa-receipt" class="text-[#667085] hover:text-[#101828] p-1.5 rounded-lg hover:bg-[#F5F8FC] transition-colors cursor-pointer" aria-label="Tutup Modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Form Kirim Struk --}}
        <form id="form-send-wa-modal" method="POST" action="{{ route('pos.transactions.send-receipt', $transaction) }}" class="m-0" data-prevent-duplicate>
            @csrf
            <div class="p-6 space-y-4">
                {{-- Ringkasan Pelanggan & Transaksi --}}
                <div class="p-3.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] text-xs text-[#475467] space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[#667085]">Nama Pelanggan:</span>
                        <span class="font-semibold text-[#101828]">{{ $transaction->customer_name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[#667085]">Total Tagihan:</span>
                        <span class="font-bold text-emerald-700">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[#667085]">Status Pembayaran:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ $transaction->status->label() }}
                        </span>
                    </div>
                </div>

                {{-- Input Nomor WhatsApp --}}
                <div>
                    <label for="wa-receipt-phone" class="block text-xs font-semibold text-[#344054] mb-1.5">
                        Nomor WhatsApp Pelanggan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="tel"
                               name="phone"
                               id="wa-receipt-phone"
                               required
                               placeholder="Contoh: 081234567890 atau 6281234567890"
                               value="{{ old('phone', $transaction->customer_phone) }}"
                               autocomplete="tel"
                               class="block w-full rounded-lg border border-[#D0D5DD] px-3.5 py-2.5 text-xs text-[#101828] placeholder-[#98A2B3] focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden transition-all font-sans">
                    </div>
                    <p id="wa-phone-error" class="hidden text-[11px] text-rose-600 mt-1.5 font-medium"></p>
                    <p class="text-[11px] text-[#667085] mt-1.5 leading-relaxed">
                        Mendukung format <strong class="text-[#344054]">08</strong>, <strong class="text-[#344054]">62</strong>, atau <strong class="text-[#344054]">+62</strong>. Struk akan dikirim ke WhatsApp pelanggan tanpa perlu mendaftarkan akun baru.
                    </p>
                </div>

                {{-- Persetujuan Pelanggan --}}
                <div class="flex items-start gap-2.5 p-3 rounded-xl bg-amber-50/60 border border-amber-200/80">
                    <input type="checkbox"
                           id="wa-consent-check"
                           required
                           checked
                           class="mt-0.5 rounded border-amber-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                    <label for="wa-consent-check" class="text-[11px] text-amber-900 leading-normal cursor-pointer select-none">
                        Pelanggan telah memberikan nomor telepon dan menyetujui penerimaan struk transaksi digital via WhatsApp.
                    </label>
                </div>
            </div>

            {{-- Footer Tombol Aksi --}}
            <div class="px-6 py-3.5 bg-[#F8FAFC] border-t border-[#E4E7EC] flex items-center justify-end gap-2.5">
                <button type="button"
                        data-modal-close="modal-wa-receipt"
                        class="px-4 py-2 rounded-lg text-xs font-semibold text-[#475467] hover:text-[#101828] hover:bg-neutral-100 border border-[#D0D5DD] transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        id="btn-submit-wa-modal"
                        class="btn-send-wa-submit inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <span class="btn-text">Kirim Struk</span>
                </button>
            </div>
        </form>
    </div>
</div>
