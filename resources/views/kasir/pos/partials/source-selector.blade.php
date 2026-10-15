<!-- Transaction Source Selector -->
<div class="bg-white border border-[#E4E7EC] rounded-xl p-4 shadow-sm">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-xs font-bold uppercase tracking-wider text-[#667085] m-0">Tipe Transaksi</h4>
        <span id="source-badge" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-50 text-[#062072]">
            Transaksi Langsung
        </span>
    </div>

    <div class="grid grid-cols-3 gap-2 text-xs font-semibold bg-[#F5F8FC] p-1 rounded-lg">
        <button type="button" onclick="selectSource('direct')" id="tab-direct" class="py-2 px-3 rounded-md text-center font-bold bg-white text-[#062072] shadow-sm transition-all flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            Langsung (Walk-in)
        </button>
        <button type="button" onclick="selectSource('booking')" id="tab-booking" class="py-2 px-3 rounded-md text-center text-[#667085] hover:text-[#101828] transition-all flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Dari Booking
        </button>
        <button type="button" onclick="selectSource('order')" id="tab-order" class="py-2 px-3 rounded-md text-center text-[#667085] hover:text-[#101828] transition-all flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            Dari Drop-off
        </button>
    </div>

    <!-- Pilihan booking -->
    <div id="booking-selector-container" class="mt-3 hidden">
        <label for="select_booking_id" class="block text-xs font-semibold text-[#101828] mb-1">Pilih Booking Siap Bayar:</label>
        <select id="select_booking_id" onchange="loadBookingData(this.value)" class="w-full px-3 py-2 bg-[#F5F8FC] border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#062072]">
            <option value="">-- Pilih Booking Appointment --</option>
            @forelse ($eligibleBookings as $eb)
                <option value="{{ $eb->uuid ?? $eb->id }}">
                    {{ $eb->user?->name ?? 'Pelanggan' }} &bull; {{ $eb->service?->name }} ({{ is_string($eb->booking_time) ? substr($eb->booking_time, 0, 5) : $eb->booking_time?->format('H:i') }})
                </option>
            @empty
                <option value="" disabled>Tidak ada booking berstatus siap bayar</option>
            @endforelse
        </select>
    </div>

    <!-- Pilihan pesanan -->
    <div id="order-selector-container" class="mt-3 hidden">
        <label for="select_order_id" class="block text-xs font-semibold text-[#101828] mb-1">Pilih Pesanan Drop-off Siap Bayar:</label>
        <select id="select_order_id" onchange="loadOrderData(this.value)" class="w-full px-3 py-2 bg-[#F5F8FC] border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#062072]">
            <option value="">-- Pilih Pesanan Drop-off --</option>
            @forelse ($eligibleOrders as $eo)
                <option value="{{ $eo->uuid ?? $eo->id }}">
                    {{ $eo->user?->name ?? 'Pelanggan' }} &bull; {{ $eo->service?->name }} (Rp {{ number_format($eo->total_amount, 0, ',', '.') }})
                </option>
            @empty
                <option value="" disabled>Tidak ada pesanan drop-off siap bayar</option>
            @endforelse
        </select>
    </div>
</div>
