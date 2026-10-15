<div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5">
    <h3 class="text-sm font-bold text-[#101828] mb-3">Aksi</h3>
    <div class="space-y-2">
        @if (in_array($order->status->value, ['pending', 'received']))
            <form method="POST" action="{{ route('customer.pesanan.cancel', $order) }}">
                @csrf @method('PATCH')
                <button type="submit" data-confirm="Apakah Anda yakin ingin membatalkan pesanan ini?"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer bg-transparent">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Batalkan Pesanan
                </button>
            </form>
        @endif

        @if ($order->transaction && $order->transaction->status->value === 'pending')
            <a href="{{ route('payment.gateway.show', $order->transaction) }}"
                class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-[#062072] text-white hover:bg-[#04184F] transition-colors no-underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Bayar Sekarang
            </a>
        @endif

        @if ($order->status->value === 'completed')
            @php
                $hasReview = $order->transaction?->ulasan !== null;
            @endphp
            @if (!$hasReview)
                <a href="{{ route('customer.ulasan.create', ['order_id' => $order->id]) }}"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-[#062072] text-[#062072] hover:bg-[#DCE8FF]/30 transition-colors no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    Beri Ulasan
                </a>
            @else
                <span class="w-full flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-medium rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Ulasan sudah diberikan
                </span>
            @endif
        @endif
    </div>
</div>
