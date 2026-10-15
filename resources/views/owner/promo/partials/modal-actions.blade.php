<!-- Single Reusable Modals Konfirmasi Aksi Promo & Voucher -->
@if (auth()->user()->role?->name === 'owner')
    <!-- Modal Konfirmasi Setujui Reusable -->
    <div id="modal-action-approve" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-approve-title" data-backdrop-close>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-[#E7ECF3] overflow-hidden p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <h3 id="modal-approve-title" class="text-base font-bold text-[#101828] m-0">Setujui Pengajuan Promo</h3>
                    <p class="text-xs text-[#667085] mt-0.5 m-0">Promo akan langsung disetujui dan aktif sesuai periode.</p>
                </div>
            </div>
            <div class="text-xs text-[#344054] bg-[#F8F9FC] p-3 rounded-lg border border-[#E4E7EC] space-y-1">
                <p class="m-0 font-semibold text-[#101828]"><span id="approve-promo-code"></span> — <span id="approve-promo-name"></span></p>
                <p class="m-0 text-[#667085]">Cabang: <span id="approve-promo-branch"></span></p>
            </div>
            <form id="form-action-approve" action="" method="POST" class="pt-2 flex items-center justify-end gap-2.5">
                @csrf
                <button type="button" data-modal-close="modal-action-approve" class="h-9 px-4 rounded-lg text-xs font-semibold text-[#062072] bg-[#EAF2FF] hover:bg-[#DCE8FF] transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="h-9 px-4 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors cursor-pointer shadow-2xs">
                    Ya, Setujui Promo
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Tolak Reusable -->
    <div id="modal-action-reject" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-reject-title" data-backdrop-close>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-[#E7ECF3] overflow-hidden p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div>
                    <h3 id="modal-reject-title" class="text-base font-bold text-[#101828] m-0">Tolak Pengajuan Promo</h3>
                    <p class="text-xs text-[#667085] mt-0.5 m-0">Promo tidak akan diterbitkan ke katalog pelanggan.</p>
                </div>
            </div>
            <div class="text-xs text-[#344054] bg-[#F8F9FC] p-3 rounded-lg border border-[#E4E7EC] space-y-1">
                <p class="m-0 font-semibold text-[#101828]"><span id="reject-promo-code"></span> — <span id="reject-promo-name"></span></p>
                <p class="m-0 text-[#667085]">Cabang: <span id="reject-promo-branch"></span></p>
            </div>
            <form id="form-action-reject" action="" method="POST" class="pt-2 flex items-center justify-end gap-2.5">
                @csrf
                <button type="button" data-modal-close="modal-action-reject" class="h-9 px-4 rounded-lg text-xs font-semibold text-[#062072] bg-[#EAF2FF] hover:bg-[#DCE8FF] transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="h-9 px-4 rounded-lg text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 transition-colors cursor-pointer shadow-2xs">
                    Ya, Tolak Promo
                </button>
            </form>
        </div>
    </div>
@endif

<!-- Modal Konfirmasi Toggle Status Reusable -->
<div id="modal-action-toggle" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="toggle-promo-title" data-backdrop-close>
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-[#E7ECF3] overflow-hidden p-6 space-y-4">
        <div class="flex items-center gap-3">
            <div id="toggle-promo-icon-active" class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                </svg>
            </div>
            <div id="toggle-promo-icon-inactive" class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <div>
                <h3 id="toggle-promo-title" class="text-base font-bold text-[#101828] m-0">Nonaktifkan Promo</h3>
                <p id="toggle-promo-desc" class="text-xs text-[#667085] mt-0.5 m-0">Promo tidak akan dapat digunakan kasir atau pelanggan.</p>
            </div>
        </div>
        <div class="text-xs text-[#344054] bg-[#F8F9FC] p-3 rounded-lg border border-[#E4E7EC]">
            <p id="toggle-promo-label" class="m-0 font-semibold text-[#101828]"></p>
        </div>
        <form id="form-action-toggle" action="" method="POST" class="pt-2 flex items-center justify-end gap-2.5">
            @csrf
            <button type="button" data-modal-close="modal-action-toggle" class="h-9 px-4 rounded-lg text-xs font-semibold text-[#062072] bg-[#EAF2FF] hover:bg-[#DCE8FF] transition-colors cursor-pointer">
                Batal
            </button>
            <button type="submit" id="toggle-promo-submit" class="h-9 px-4 rounded-lg text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 transition-colors cursor-pointer shadow-2xs">
                Ya, Nonaktifkan
            </button>
        </form>
    </div>
</div>
