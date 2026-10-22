{{-- Dialog Konfirmasi Pembatalan Janji Temu --}}
@if(in_array($booking->status->value, ['pending', 'confirmed']))
<x-modals.modal id="modal-cancel-booking" maxWidth="max-w-md">
    <div class="space-y-4">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-bold text-[#101828] m-0">Batalkan Janji Temu?</h3>
                <p class="text-xs sm:text-sm text-[#667085] mt-1 m-0 leading-relaxed">
                    Apakah Anda yakin ingin membatalkan janji temu <strong>#{{ $booking->id }}</strong> untuk layanan <strong>{{ $booking->service?->name }}</strong>?
                </p>
            </div>
        </div>

        <div class="bg-[#F8F9FC] border border-[#E4E7EC] rounded-xl p-3 text-xs space-y-1.5 text-[#344054]">
            <div class="flex justify-between">
                <span class="text-[#667085]">Tanggal:</span>
                <span class="font-semibold text-[#101828]">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#667085]">Waktu:</span>
                <span class="font-semibold text-[#101828]">{{ $booking->booking_time }} WIB</span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#667085]">Cabang:</span>
                <span class="font-semibold text-[#101828]">{{ $booking->branch?->display_name ?? $booking->branch?->name }}</span>
            </div>
        </div>

        <p class="text-[11px] text-rose-700 bg-rose-50 border border-rose-200 rounded-lg p-2.5 m-0 font-medium">
            Tindakan pembatalan ini tidak dapat diurungkan kembali. Slot waktu akan dibuka kembali untuk pelanggan lain.
        </p>

        <form id="form-cancel-booking" action="{{ route('customer.booking.cancel', $booking) }}" method="POST" class="m-0 pt-2 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5">
            @csrf
            @method('PATCH')
            <button type="button"
                data-modal-close="modal-cancel-booking"
                id="btn-cancel-dialog-back"
                class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-[#D0D5DD] bg-white text-xs sm:text-sm font-semibold text-[#344054] hover:bg-neutral-50 transition-colors cursor-pointer text-center">
                Kembali
            </button>
            <button type="submit"
                id="btn-cancel-dialog-confirm"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-[0.98] text-white text-xs sm:text-sm font-semibold transition-all cursor-pointer shadow-sm flex items-center justify-center gap-1.5">
                <span>Ya, Batalkan</span>
            </button>
        </form>
    </div>
</x-modals.modal>
@endif
