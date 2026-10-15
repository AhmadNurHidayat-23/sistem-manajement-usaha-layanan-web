<!-- Payment Calculation Breakdown -->
<div class="py-3.5 border-b border-dashed border-[#CBD5E1] space-y-1.5 text-[11px]">
    <div class="flex justify-between items-center">
        <span class="text-[#667085]">Subtotal</span>
        <span class="text-[#101828] font-medium">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
    </div>

    @if ($transaction->discount > 0)
        <div class="flex justify-between items-center text-emerald-600 font-medium">
            <span>Diskon Promo</span>
            <span>- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
        </div>
    @endif

    <div class="flex justify-between items-baseline text-sm font-black pt-2 border-t border-[#E4E7EC] text-[#062072]">
        <span>TOTAL AKHIR</span>
        <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
    </div>
</div>

<!-- Payment Method Details -->
<div class="py-3.5 border-b border-dashed border-[#CBD5E1] space-y-1.5 text-[11px]">
    <div class="flex justify-between items-center">
        <span class="text-[#667085]">Metode Pembayaran</span>
        <span class="uppercase font-bold text-[#101828]">
            {{ match($transaction->payment_method) {
                'cash' => 'Tunai',
                'qris' => 'QRIS',
                'transfer' => 'Transfer Bank',
                'card' => 'Kartu Debit/Kredit',
                'e_wallet' => 'E-Wallet',
                'virtual_account' => 'Virtual Account',
                default => $transaction->payment_method ?: 'Tunai'
            } }}
        </span>
    </div>

    @php
        $paidPayment = $transaction->successfulPayment() ?? $transaction->payments->first();
        $cashReceived = $paidPayment?->cash_received ?? $transaction->total;
        $cashChange = $paidPayment?->cash_change ?? 0;
    @endphp

    @if ($transaction->payment_method === 'cash')
        <div class="flex justify-between items-center">
            <span class="text-[#667085]">Uang Diterima</span>
            <span class="text-[#101828] font-medium">Rp {{ number_format($cashReceived, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between items-center font-bold text-emerald-700">
            <span>Kembalian</span>
            <span>Rp {{ number_format($cashChange, 0, ',', '.') }}</span>
        </div>
    @elseif ($paidPayment?->reference_number)
        <div class="flex justify-between items-start gap-2">
            <span class="text-[#667085] shrink-0">No. Referensi</span>
            <span class="font-medium text-[#101828] text-right break-words">{{ $paidPayment->reference_number }}</span>
        </div>
    @endif

    <div class="flex justify-between items-center pt-1.5">
        <span class="text-[#667085]">Status Pembayaran</span>
        @if ($transaction->status === \App\Enums\StatusTransaksi::Paid)
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span>LUNAS</span>
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                <span>{{ strtoupper($transaction->status->label()) }}</span>
            </span>
        @endif
    </div>
</div>
