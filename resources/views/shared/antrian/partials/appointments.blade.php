<x-cards.card>
    <div class="flex items-center justify-between mb-4 pb-2 border-b border-[#E4E7EC]">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h4 class="text-base font-semibold text-[#101828] m-0">Tugas Appointment ({{ $bookings->count() }})</h4>
        </div>
        <span class="text-xs text-[#667085] font-medium">Janji Temu</span>
    </div>

    @forelse($bookings as $booking)
    <div class="flex items-start justify-between gap-3 py-3 border-b border-[#E4E7EC] last:border-0 hover:bg-[#F5F8FC]/60 px-2 rounded-lg transition-colors">
        <div class="flex items-start gap-3 min-w-0">
            <div class="w-12 h-10 rounded-lg bg-[#DCE8FF] flex flex-col items-center justify-center shrink-0 font-bold text-[#062072] text-xs leading-none">
                <span>{{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}</span>
                <span class="text-[9px] font-normal text-[#0B3A9E] mt-0.5">WIB</span>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-[#101828] truncate m-0">{{ $booking->user?->name ?? 'Pelanggan' }}</p>
                <p class="text-xs text-[#475467] truncate mt-0.5 m-0 font-medium">{{ $booking->service?->name ?? 'Layanan' }}</p>
                <p class="text-[11px] text-[#667085] truncate mt-0.5 m-0">{{ $booking->branch?->display_name ?? 'Usaha' }}</p>
            </div>
        </div>
        <div class="flex flex-col items-end gap-1.5 shrink-0">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $booking->status->badgeClass() }}">{{ $booking->status->label() }}</span>
            <a href="{{ route('bookings.show', $booking) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-white border border-[#D0D5DD] text-[#062072] hover:bg-[#EBF2FE] hover:border-[#062072] transition-colors">
                Detail
            </a>
        </div>
    </div>
    @empty
    <div class="py-10 text-center text-[#667085]">
        <svg class="w-10 h-10 mx-auto mb-2 text-[#D0D5DD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-sm font-semibold text-[#101828] m-0">Tidak ada tugas appointment hari ini</p>
        <p class="text-xs text-[#667085] mt-1 m-0">Semua janji temu terjadwal akan muncul di sini.</p>
    </div>
    @endforelse
</x-cards.card>
