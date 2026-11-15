{{-- Modal Kustom Konfirmasi Tugas Layanan Karyawan --}}
<div id="modal-konfirmasi-tugas" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/50 backdrop-blur-[3px] flex items-center justify-center p-4 transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="task-confirm-title">
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-[#E7ECF3] overflow-hidden transform transition-all p-6 text-left">
        {{-- Tombol Tutup X --}}
        <button type="button" data-task-modal-close onclick="closeTaskConfirmModal()" class="absolute top-4 right-4 text-[#667085] hover:text-[#101828] p-1.5 rounded-lg hover:bg-[#F5F8FC] transition-colors cursor-pointer" aria-label="Tutup Modal">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        {{-- Icon Header --}}
        <div class="flex items-center gap-3.5 mb-4">
            <div id="task-confirm-icon-start" class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 text-[#062072] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div id="task-confirm-icon-complete" class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 hidden">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h3 id="task-confirm-title" class="text-base sm:text-lg font-bold text-[#101828] m-0">
                    Konfirmasi Tugas?
                </h3>
                <p id="task-confirm-desc" class="text-xs text-[#667085] mt-0.5 m-0">
                    Apakah Anda yakin ingin memperbarui status tugas ini?
                </p>
            </div>
        </div>

        {{-- Detail Tugas Singkat --}}
        <div class="p-3 bg-[#F8FAFC] border border-[#E4E7EC] rounded-xl text-xs space-y-1.5 mb-5">
            <div class="flex items-center justify-between">
                <span class="text-[#667085]">Layanan:</span>
                <span id="task-confirm-service" class="font-semibold text-[#101828] text-right truncate max-w-[220px]">-</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[#667085]">Pelanggan:</span>
                <span id="task-confirm-customer" class="font-medium text-[#101828] text-right truncate max-w-[220px]">-</span>
            </div>
        </div>

        {{-- Form Eksekusi --}}
        <form id="form-task-confirm" method="POST" action="" onsubmit="handleTaskConfirmSubmit(event)">
            @csrf
            @method('PATCH')
            <input type="hidden" id="task-confirm-status" name="status" value="">

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" data-task-modal-close onclick="closeTaskConfirmModal()" class="px-4 py-2 text-xs font-semibold rounded-lg border border-[#D0D5DD] bg-white text-[#344054] hover:bg-[#F5F8FC] transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btn-task-confirm-submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-[#062072] text-white hover:bg-[#04184F] transition-all cursor-pointer shadow-2xs">
                    <span id="task-confirm-btn-spinner" class="hidden">
                        <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span id="task-confirm-btn-text">Ya, Lanjutkan</span>
                </button>
            </div>
        </form>
    </div>
</div>

