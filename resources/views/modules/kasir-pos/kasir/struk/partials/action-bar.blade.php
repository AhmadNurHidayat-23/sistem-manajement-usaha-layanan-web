{{-- Action Bar (Not Printed) --}}
<div class="no-print w-full max-w-2xl mb-6">
    <div class="flex flex-wrap items-center justify-center gap-2.5 sm:gap-3">
        {{-- Terminal POS --}}
        <a href="{{ route('pos.index') }}" id="btn-pos-terminal"
            class="inline-flex items-center justify-center gap-2 h-10 px-4 bg-white border border-[#D0D5DD] hover:bg-[#F8FAFC] text-[#344054] text-xs font-semibold rounded-lg shadow-xs transition-colors whitespace-nowrap">
            <svg class="w-4 h-4 text-[#475467]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span>Terminal POS</span>
        </a>

        {{-- Riwayat Transaksi --}}
        <a href="{{ route('pos.transactions.index') }}" id="btn-riwayat-transaksi"
            class="inline-flex items-center justify-center gap-2 h-10 px-4 bg-white border border-[#D0D5DD] hover:bg-[#F8FAFC] text-[#344054] text-xs font-semibold rounded-lg shadow-xs transition-colors whitespace-nowrap">
            <svg class="w-4 h-4 text-[#475467]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span>Riwayat Transaksi</span>
        </a>

        {{-- Bayar Digital: HANYA jika transaksi belum lunas dan masih pending --}}
        @if ($transaction->status === \App\Enums\StatusTransaksi::Pending)
            <a href="{{ route('payment.gateway.show', $transaction) }}" id="btn-bayar-digital"
                class="inline-flex items-center justify-center gap-2 h-10 px-4 bg-[#EEF4FF] border border-[#C7DCFF] hover:bg-[#DCE8FF] text-[#062072] text-xs font-semibold rounded-lg shadow-xs transition-colors whitespace-nowrap">
                <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>Bayar Digital</span>
            </a>
        @endif

        {{-- Kirim Struk WhatsApp: Khusus transaksi yang sudah lunas --}}
        @if ($transaction->status === \App\Enums\StatusTransaksi::Paid)
            <form method="POST" action="{{ route('pos.transactions.send-receipt', $transaction) }}" class="inline m-0">
                @csrf
                <button type="submit" id="btn-send-wa"
                    class="inline-flex items-center justify-center gap-2 h-10 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>Kirim Struk WA</span>
                </button>
            </form>
        @endif

        {{-- Cetak Struk: Aksi Utama --}}
        <button type="button" data-action="print" id="btn-print-receipt"
            class="inline-flex items-center justify-center gap-2 h-10 px-5 bg-[#062072] hover:bg-[#0A2F9E] text-white text-xs font-bold rounded-lg shadow-xs transition-colors cursor-pointer whitespace-nowrap">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Cetak Struk</span>
        </button>
    </div>
</div>
