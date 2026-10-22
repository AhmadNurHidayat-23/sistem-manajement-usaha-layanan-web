{{-- Kartu Detail Utama Booking --}}
<div class="bg-white border border-[#E4E7EC] rounded-2xl shadow-sm overflow-hidden">
    <div class="p-5 sm:p-7 space-y-6">
        
        {{-- Jadwal & Waktu Pelaksanaan --}}
        <div class="bg-[#F8F9FC] border border-[#E4E7EC] rounded-xl p-4 sm:p-5">
            <span class="text-[11px] font-bold text-[#667085] uppercase tracking-wider block mb-3">Jadwal Pelaksanaan</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-white border border-[#E4E7EC] text-[#062072] flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-[#667085] block">Hari & Tanggal</span>
                        <span class="text-sm sm:text-base font-bold text-[#101828] block mt-0.5">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-white border border-[#E4E7EC] text-[#062072] flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-[#667085] block">Waktu / Jam Booking</span>
                        <span class="text-sm sm:text-base font-bold text-[#101828] block mt-0.5">
                            {{ $booking->booking_time }} WIB
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Layanan Utama --}}
        <div>
            <span class="text-[11px] font-bold text-[#667085] uppercase tracking-wider block mb-2.5">Layanan Utama</span>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3.5 sm:p-4 rounded-xl bg-white border border-[#E4E7EC]">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm sm:text-base font-bold text-[#101828]">{{ $booking->service?->name ?? '-' }}</span>
                        @if($booking->service?->category)
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#F5F8FC] text-[#344054] border border-[#E4E7EC]">
                                {{ $booking->service->category->name }}
                            </span>
                        @endif
                    </div>
                    @if($booking->service?->formatted_duration)
                        <p class="text-xs text-[#667085] m-0">Durasi estimasi: {{ $booking->service->formatted_duration }}</p>
                    @endif
                </div>
                @if($booking->service)
                    <div class="text-left sm:text-right shrink-0">
                        <span class="text-xs text-[#667085] block sm:hidden">Tarif Layanan</span>
                        <span class="text-base sm:text-lg font-bold text-[#062072]">Rp {{ number_format($booking->service->price, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Layanan Tambahan (Extra Services) --}}
        @if($booking->details && $booking->details->isNotEmpty())
        <div>
            <span class="text-[11px] font-bold text-[#667085] uppercase tracking-wider block mb-2.5">Layanan Tambahan</span>
            <div class="space-y-2">
                @foreach($booking->details as $detail)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#F8F9FC] border border-[#E4E7EC]">
                        <span class="text-xs sm:text-sm font-medium text-[#101828]">{{ $detail->service?->name ?? '-' }}</span>
                        <span class="text-xs sm:text-sm font-semibold text-[#062072]">Rp {{ number_format($detail->price, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <hr class="border-[#E4E7EC] m-0">

        {{-- Lokasi Cabang & Petugas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <span class="text-[11px] font-bold text-[#667085] uppercase tracking-wider block mb-1.5">Cabang Pelayanan</span>
                <span class="text-sm font-bold text-[#101828] block">{{ $booking->branch?->display_name ?? $booking->branch?->name ?? '-' }}</span>
                @if($booking->branch?->address)
                    <p class="text-xs text-[#667085] m-0 mt-1 leading-relaxed">{{ $booking->branch->address }}</p>
                @endif
                @if($booking->branch?->phone)
                    <p class="text-xs text-[#062072] font-medium m-0 mt-1">Telp: {{ $booking->branch->phone }}</p>
                @endif
            </div>

            <div>
                <span class="text-[11px] font-bold text-[#667085] uppercase tracking-wider block mb-1.5">Petugas Layanan</span>
                @if($booking->employee)
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-[#101828]">{{ $booking->employee->name }}</span>
                    </div>
                    <p class="text-xs text-[#667085] m-0 mt-1">Staf operasional cabang</p>
                @else
                    <span class="text-sm font-medium text-[#344054] block">Siapa saja yang tersedia</span>
                    <p class="text-xs text-[#667085] m-0 mt-1">Sistem akan otomatis mengalokasikan staf cabang yang siap melayani Anda.</p>
                @endif
            </div>
        </div>

        {{-- Catatan --}}
        @if($booking->notes)
        <div>
            <span class="text-[11px] font-bold text-[#667085] uppercase tracking-wider block mb-1.5">Catatan Khusus</span>
            <div class="p-3.5 rounded-xl bg-[#F8F9FC] border border-[#E4E7EC] text-xs sm:text-sm text-[#344054] leading-relaxed">
                {{ $booking->notes }}
            </div>
        </div>
        @endif

        <hr class="border-[#E4E7EC] m-0">

        {{-- Timestamps --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-[#667085]">
            <div>
                <span class="block text-[11px] uppercase tracking-wider text-[#98A2B3]">Dibuat</span>
                <span class="font-medium text-[#101828] mt-0.5 block">{{ \Carbon\Carbon::parse($booking->created_at)->translatedFormat('d F Y, H:i') }} WIB</span>
            </div>
            <div>
                <span class="block text-[11px] uppercase tracking-wider text-[#98A2B3]">Terakhir Diperbarui</span>
                <span class="font-medium text-[#101828] mt-0.5 block">{{ \Carbon\Carbon::parse($booking->updated_at)->translatedFormat('d F Y, H:i') }} WIB</span>
            </div>
        </div>
    </div>

    {{-- Baris Aksi Footer --}}
    <div class="px-5 sm:px-7 py-4 bg-[#F8F9FC] border-t border-[#E4E7EC] flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <a href="{{ route('customer.booking.index') }}"
            class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-[#D0D5DD] bg-white text-xs sm:text-sm font-semibold text-[#344054] hover:bg-neutral-50 transition-colors no-underline text-center">
            Kembali ke Daftar Janji Temu
        </a>

        @if(in_array($booking->status->value, ['pending', 'confirmed']))
            <button type="button"
                id="btn-open-cancel-modal"
                data-modal-open="modal-cancel-booking"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-rose-300 text-rose-700 hover:bg-rose-50 hover:border-rose-400 rounded-xl text-xs sm:text-sm font-semibold transition-colors cursor-pointer shadow-2xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>Batalkan Janji Temu</span>
            </button>
        @endif
    </div>
</div>
