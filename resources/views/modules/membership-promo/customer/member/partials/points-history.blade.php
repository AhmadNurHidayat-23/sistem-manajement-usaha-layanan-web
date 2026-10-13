<div class="lg:col-span-2 space-y-5">
    <!-- Cara peroleh poin -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5">
        <h3 class="text-sm font-bold text-[#101828] mb-3">Cara Memperoleh Poin</h3>
        <div class="space-y-2">
            <div class="flex items-center gap-3 p-3 bg-[#F5F8FC] rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-[#DCE8FF] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#101828] m-0">1 Poin per Rp 10.000 Transaksi</p>
                    <p class="text-[10px] text-[#667085] m-0">Poin otomatis bertambah setelah pembayaran lunas</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-[#F5F8FC] rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-[#DCE8FF] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#101828] m-0">10 Poin Bonus Pendaftaran</p>
                    <p class="text-[10px] text-[#667085] m-0">Langsung diperoleh saat pertama kali daftar member</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat transaksi -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5">
        <h3 class="text-sm font-bold text-[#101828] mb-3">Riwayat Poin</h3>
        @if ($membership->loyaltyTransactions && $membership->loyaltyTransactions->count())
            <div class="divide-y divide-[#E4E7EC] max-h-80 overflow-y-auto">
                @foreach($membership->loyaltyTransactions as $trx)
                <div class="py-3 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium text-[#101828] m-0">{{ $trx->description }}</p>
                        <p class="text-[10px] text-[#667085] m-0">{{ $trx->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="text-sm font-bold {{ $trx->points >= 0 ? 'text-emerald-600' : 'text-rose-600' }} whitespace-nowrap">
                        {{ $trx->points >= 0 ? '+' : '' }}{{ $trx->points }} Poin
                    </span>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-[#667085] text-center py-4 m-0">Belum ada riwayat poin.</p>
        @endif
    </div>
</div>
