{{-- Dialog Konfirmasi Pembatalan Janji Temu --}}
@if(in_array($booking->status->value, ['pending', 'confirmed']))
<x-modals.modal id="modal-cancel-booking" maxWidth="max-w-lg">
    <div class="max-h-[80vh] overflow-y-auto pr-1 -mr-1">
        <form id="form-cancel-booking" action="{{ route('customer.booking.cancel', $booking) }}" method="POST" class="space-y-4 m-0" novalidate>
            @csrf
            @method('PATCH')

            {{-- 1. Judul dan deskripsi pembatalan --}}
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-[#101828] m-0">Batalkan Janji Temu?</h3>
                    <p class="text-xs sm:text-sm text-[#667085] mt-1 m-0 leading-relaxed">
                        Apakah Anda yakin ingin membatalkan janji temu untuk layanan <strong>{{ $booking->service?->name }}</strong>?
                    </p>
                </div>
            </div>

            {{-- 2. Informasi janji temu --}}
            <div class="bg-[#F8F9FC] border border-[#E4E7EC] rounded-xl p-3 sm:p-3.5 text-xs space-y-1.5 text-[#344054]">
                <div class="flex justify-between">
                    <span class="text-[#667085]">Layanan:</span>
                    <span class="font-semibold text-[#101828] text-right">{{ $booking->service?->name ?? '-' }}</span>
                </div>
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
                    <span class="font-semibold text-[#101828] text-right">{{ $booking->branch?->display_name ?? $booking->branch?->name }}</span>
                </div>
            </div>

            {{-- 3. Pilihan alasan pembatalan --}}
            <div class="space-y-2">
                <label class="block text-xs font-bold text-[#101828] uppercase tracking-wider">
                    Pilih Alasan Pembatalan <span class="text-rose-500">*</span>
                </label>
                <div class="space-y-2" id="cancellation-reason-group">
                    @php
                        $reasons = [
                            'Jadwal mendadak',
                            'Salah memilih cabang',
                            'Salah memilih tanggal atau jam',
                            'Tidak dapat hadir',
                            'Tidak jadi menggunakan layanan',
                            'Alasan lainnya',
                        ];
                    @endphp
                    @foreach($reasons as $index => $reason)
                        <label class="flex items-center gap-3 p-2.5 sm:p-3 rounded-xl border border-[#E4E7EC] hover:bg-[#F8F9FC] hover:border-[#D0D5DD] cursor-pointer transition-all has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50/40">
                            <input type="radio"
                                name="cancellation_reason"
                                value="{{ $reason }}"
                                id="reason_{{ $index }}"
                                class="w-4 h-4 text-rose-600 border-[#D0D5DD] focus:ring-rose-500 focus:ring-offset-0 cursor-pointer"
                                {{ old('cancellation_reason') === $reason ? 'checked' : '' }}>
                            <span class="text-xs sm:text-sm font-medium text-[#344054]">{{ $reason }}</span>
                        </label>
                    @endforeach
                </div>
                <div id="cancellation-reason-error" class="hidden text-xs text-rose-600 font-medium mt-1">
                    Silakan pilih salah satu alasan pembatalan di atas.
                </div>
                @error('cancellation_reason')
                    <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 4. Kolom alasan lainnya jika dipilih --}}
            <div id="cancellation-other-wrapper" class="{{ old('cancellation_reason') === 'Alasan lainnya' ? '' : 'hidden' }} space-y-1.5">
                <label for="other_cancellation_reason" class="block text-xs font-semibold text-[#101828]">
                    Tuliskan Alasan Pembatalan <span class="text-rose-500">*</span>
                </label>
                <textarea
                    id="other_cancellation_reason"
                    name="other_cancellation_reason"
                    rows="3"
                    placeholder="Jelaskan alasan pembatalan janji temu Anda secara singkat..."
                    class="w-full px-3 py-2 bg-white border border-[#D0D5DD] rounded-xl text-xs sm:text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-colors">{{ old('other_cancellation_reason') }}</textarea>
                <div id="other-reason-error" class="hidden text-xs text-rose-600 font-medium">
                    Alasan lainnya wajib diisi.
                </div>
                @error('other_cancellation_reason')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- 5. Peringatan pembatalan --}}
            <div class="flex items-start gap-2 text-[11px] text-rose-700 bg-rose-50 border border-rose-200 rounded-xl p-3 font-medium leading-relaxed">
                <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Tindakan pembatalan ini tidak dapat diurungkan kembali. Slot waktu akan dibuka kembali untuk pelanggan lain.</span>
            </div>

            {{-- 6. Tombol Kembali dan Ya, Batalkan --}}
            <div class="pt-2 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5">
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
            </div>
        </form>
    </div>
</x-modals.modal>
@endif
