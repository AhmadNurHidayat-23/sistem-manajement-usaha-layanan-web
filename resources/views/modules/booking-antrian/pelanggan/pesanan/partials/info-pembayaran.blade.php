@if ($order->transaction)
<div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5 sm:p-6">
    <h3 class="text-sm font-bold text-[#101828] mb-3">Informasi Pembayaran</h3>
    <dl class="divide-y divide-[#E4E7EC]">
        <div class="py-3 flex justify-between gap-4">
            <dt class="text-sm text-[#667085]">Kode Transaksi</dt>
            <dd class="text-sm tabular-nums font-medium text-[#101828]">{{ $order->transaction->transaction_code }}</dd>
        </div>
        <div class="py-3 flex justify-between gap-4">
            <dt class="text-sm text-[#667085]">Total Tagihan</dt>
            <dd class="text-sm font-bold text-[#062072]">Rp {{ number_format($order->transaction->total, 0, ',', '.') }}</dd>
        </div>
        @if ($order->transaction->discount > 0)
        <div class="py-3 flex justify-between gap-4">
            <dt class="text-sm text-[#667085]">Diskon</dt>
            <dd class="text-sm font-medium text-emerald-700">- Rp {{ number_format($order->transaction->discount, 0, ',', '.') }}</dd>
        </div>
        @endif
        <div class="py-3 flex justify-between gap-4">
            <dt class="text-sm text-[#667085]">Status Pembayaran</dt>
            <dd class="text-sm">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $order->transaction->status->value === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $order->transaction->status->label() }}
                </span>
            </dd>
        </div>
        @php
            $latestPayment = $order->transaction->payments->first();
        @endphp
        @if ($latestPayment)
        <div class="py-3 flex justify-between gap-4">
            <dt class="text-sm text-[#667085]">Metode Pembayaran</dt>
            <dd class="text-sm font-medium text-[#101828]">{{ ucfirst(str_replace('_', ' ', $latestPayment->payment_method)) }}</dd>
        </div>
        @endif
    </dl>

    @if ($order->transaction->status->value === 'pending')
        <div class="mt-4 pt-4 border-t border-[#E4E7EC]">
            <a href="{{ route('payment.gateway.show', $order->transaction) }}"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-sm font-semibold transition-colors no-underline">
                Bayar Sekarang
            </a>
            <p class="text-[10px] text-[#667085] mt-2 text-center m-0">Atau bayar langsung di kasir cabang</p>
        </div>
    @endif
</div>
@endif
