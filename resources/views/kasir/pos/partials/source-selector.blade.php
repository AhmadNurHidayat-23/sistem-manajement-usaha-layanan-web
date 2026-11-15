<!-- Transaction Source Selector: Pilihan Walk-in, Booking, & Drop-off -->
<div class="bg-white border border-[#E4E7EC] rounded-xl p-4 sm:p-5 shadow-2xs space-y-3.5">
    <div class="flex items-center justify-between">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#667085] m-0">Tipe Transaksi Pelanggan</h4>
        <span id="source-badge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase bg-blue-50 text-[#062072] border border-blue-200">
            Transaksi Langsung (Walk-in)
        </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs font-semibold bg-[#F5F8FC] p-1.5 rounded-xl border border-[#E4E7EC]">
        <button type="button" onclick="selectSource('direct')" id="tab-direct" class="py-2.5 px-3 rounded-lg text-center font-bold bg-white text-[#062072] shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            Langsung (Walk-in)
        </button>
        <button type="button" onclick="selectSource('booking')" id="tab-booking" class="py-2.5 px-3 rounded-lg text-center text-[#667085] hover:text-[#101828] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Dari Booking Janji Temu
        </button>
        <button type="button" onclick="selectSource('order')" id="tab-order" class="py-2.5 px-3 rounded-lg text-center text-[#667085] hover:text-[#101828] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            Dari Drop-off / Antar-Jemput
        </button>
    </div>

    <!-- Pilihan booking appointment -->
    <div id="booking-selector-container" class="space-y-2 hidden pt-2 border-t border-[#E4E7EC]">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <label for="select_booking_id" class="text-xs font-bold text-[#101828]">Pilih Booking Appointment Siap Bayar:</label>
            <div id="booking-action-bar" class="hidden">
                <button type="button" id="btn-booking-checkout" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-bold rounded-lg shadow-2xs transition-colors cursor-pointer">
                    <span>Lanjutkan Pembayaran Booking</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
        <select id="select_booking_id" onchange="loadBookingData(this.value)" class="w-full px-3 py-2 bg-[#F8FAFC] border border-[#D0D5DD] rounded-lg text-xs font-medium text-[#101828] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#062072]">
            <option value="">-- Pilih Booking Appointment --</option>
            @forelse ($eligibleBookings as $eb)
                <option value="{{ $eb->uuid ?? $eb->id }}">
                    [{{ $eb->status === \App\Enums\BookingStatus::Pending ? 'Belum Diverifikasi' : 'Terverifikasi' }}] {{ $eb->user?->name ?? 'Pelanggan' }} &bull; {{ $eb->service?->name }} ({{ is_string($eb->booking_time) ? substr($eb->booking_time, 0, 5) : $eb->booking_time?->format('H:i') }})
                </option>
            @empty
                <option value="" disabled>Tidak ada booking berstatus siap bayar untuk cabang ini</option>
            @endforelse
        </select>
    </div>

    <!-- Pilihan pesanan drop-off -->
    <div id="order-selector-container" class="space-y-2 hidden pt-2 border-t border-[#E4E7EC]">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <label for="select_order_id" class="text-xs font-bold text-[#101828]">Pilih Pesanan Drop-off Siap Bayar:</label>
            <div id="order-action-bar" class="hidden">
                <button type="button" id="btn-order-checkout" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-bold rounded-lg shadow-2xs transition-colors cursor-pointer">
                    <span>Lanjutkan Pembayaran Drop-off</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
        <select id="select_order_id" onchange="loadOrderData(this.value)" class="w-full px-3 py-2 bg-[#F8FAFC] border border-[#D0D5DD] rounded-lg text-xs font-medium text-[#101828] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#062072]">
            <option value="">-- Pilih Pesanan Drop-off --</option>
            @forelse ($eligibleOrders as $eo)
                <option value="{{ $eo->uuid ?? $eo->id }}">
                    {{ $eo->user?->name ?? 'Pelanggan' }} &bull; {{ $eo->service?->name }} (Rp {{ number_format($eo->total_amount, 0, ',', '.') }})
                </option>
            @empty
                <option value="" disabled>Tidak ada pesanan drop-off siap bayar untuk cabang ini</option>
            @endforelse
        </select>
    </div>
</div>
