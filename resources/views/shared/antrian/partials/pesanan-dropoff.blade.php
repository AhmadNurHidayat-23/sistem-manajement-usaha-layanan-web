<x-cards.card>
    <div class="flex items-center justify-between mb-4 pb-2 border-b border-[#E4E7EC]">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h4 class="text-base font-semibold text-[#101828] m-0">Tugas Drop-off ({{ $orders->count() }})</h4>
        </div>
        <span class="text-xs text-[#667085] font-medium">Titipan Layanan</span>
    </div>

    @forelse($orders as $order)
    <div class="flex items-start justify-between gap-3 py-3 border-b border-[#E4E7EC] last:border-0 hover:bg-[#F5F8FC]/60 px-2 rounded-lg transition-colors">
        <div class="flex items-start gap-3 min-w-0">
            <div class="w-12 h-10 rounded-lg bg-[#DCE8FF] flex flex-col items-center justify-center shrink-0 font-bold text-[#062072] text-xs leading-none">
                <span>{{ $order->created_at->format('H:i') }}</span>
                <span class="text-[9px] font-normal text-[#0B3A9E] mt-0.5">WIB</span>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-[#101828] truncate m-0">{{ $order->user?->name ?? 'Pelanggan' }}</p>
                <p class="text-xs text-[#475467] truncate mt-0.5 m-0 font-medium">{{ $order->service?->name ?? 'Layanan' }}</p>
                <p class="text-[11px] text-[#667085] truncate mt-0.5 m-0">{{ $order->branch?->display_name ?? 'Usaha' }}</p>
            </div>
        </div>
        <div class="flex flex-col items-end gap-1.5 shrink-0">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
            <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-white border border-[#D0D5DD] text-[#062072] hover:bg-[#EBF2FE] hover:border-[#062072] transition-colors">
                Detail
            </a>
        </div>
    </div>
    @empty
    <div class="py-10 text-center text-[#667085]">
        <svg class="w-10 h-10 mx-auto mb-2 text-[#D0D5DD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        <p class="text-sm font-semibold text-[#101828] m-0">Tidak ada order drop-off hari ini</p>
        <p class="text-xs text-[#667085] mt-1 m-0">Semua pesanan drop-off masuk akan muncul di sini.</p>
    </div>
    @endforelse
</x-cards.card>
