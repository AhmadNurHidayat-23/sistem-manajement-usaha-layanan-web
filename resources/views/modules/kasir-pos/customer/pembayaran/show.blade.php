@extends('layouts.pelanggan', ['title' => 'Pembayaran Pesanan'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-[#667085]">
        <a href="{{ route('customer.pesanan.index') }}" class="hover:text-[#062072] no-underline text-[#667085]">Pesanan Saya</a>
        <svg class="w-3 h-3 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('customer.pesanan.detail') }}" class="hover:text-[#062072] no-underline text-[#667085]">Detail Pesanan</a>
        <svg class="w-3 h-3 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-[#101828] font-medium">Pembayaran</span>
    </nav>

    @php $transaction = $order->transaction; @endphp

    <!-- Ringkasan transaksi -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5 sm:p-6">
        <h3 class="text-sm font-bold text-[#101828] mb-4">Ringkasan Pembayaran</h3>
        <dl class="divide-y divide-[#E4E7EC]">
            <div class="py-3 flex justify-between gap-4">
                <dt class="text-sm text-[#667085]">Kode Transaksi</dt>
                <dd class="text-sm tabular-nums font-medium text-[#101828]">{{ $transaction->transaction_code }}</dd>
            </div>
            <div class="py-3 flex justify-between gap-4">
                <dt class="text-sm text-[#667085]">Layanan</dt>
                <dd class="text-sm font-medium text-[#101828]">{{ $order->service?->name ?? '-' }}</dd>
            </div>
            <div class="py-3 flex justify-between gap-4">
                <dt class="text-sm text-[#667085]">Subtotal</dt>
                <dd class="text-sm text-[#101828]">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</dd>
            </div>
            @if ($transaction->discount > 0)
            <div class="py-3 flex justify-between gap-4">
                <dt class="text-sm text-[#667085]">Diskon</dt>
                <dd class="text-sm font-medium text-emerald-700">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</dd>
            </div>
            @endif
            <div class="py-3 flex justify-between gap-4">
                <dt class="text-base font-bold text-[#101828]">Total Tagihan</dt>
                <dd class="text-base font-bold text-[#062072]">Rp {{ number_format($transaction->total, 0, ',', '.') }}</dd>
            </div>
            <div class="py-3 flex justify-between gap-4">
                <dt class="text-sm text-[#667085]">Status Pembayaran</dt>
                <dd class="text-sm">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $transaction->status->value === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ $transaction->status->label() }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>

    <!-- Payment Methods / Actions -->
    @if ($transaction->status->value === 'pending')
        <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5 sm:p-6">
            <h3 class="text-sm font-bold text-[#101828] mb-2">Metode Pembayaran</h3>
            <p class="text-xs text-[#667085] mb-4">Pilih cara pembayaran yang Anda inginkan</p>

            <div class="space-y-3">
                <!-- Gateway Payment -->
                <a href="{{ route('payment.gateway.show', $transaction) }}"
                    class="flex items-center gap-4 p-4 border border-[#E4E7EC] rounded-lg hover:border-[#062072] hover:bg-[#F5F8FC] transition-all no-underline group">
                    <div class="w-10 h-10 rounded-lg bg-[#DCE8FF] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-[#101828] m-0 group-hover:text-[#062072]">Pembayaran Digital</p>
                        <p class="text-xs text-[#667085] m-0">QRIS, E-Wallet, atau Virtual Account</p>
                    </div>
                    <svg class="w-5 h-5 text-[#667085] group-hover:text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <!-- Pay at Cashier -->
                <div class="flex items-center gap-4 p-4 border border-[#E4E7EC] rounded-lg bg-[#F5F8FC]">
                    <div class="w-10 h-10 rounded-lg bg-white border border-[#E4E7EC] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-[#101828] m-0">Bayar di Kasir</p>
                        <p class="text-xs text-[#667085] m-0">Lakukan pembayaran tunai langsung di kasir {{ $order->branch?->display_name }}</p>
                    </div>
                </div>
            </div>
        </div>
    @elseif ($transaction->status->value === 'paid')
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-6 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-base font-bold text-emerald-800 mb-1">Pembayaran Lunas</h3>
            <p class="text-sm text-emerald-700 m-0">Pembayaran untuk pesanan ini telah berhasil dikonfirmasi.</p>
        </div>
    @endif

    <!-- Payment History -->
    @if ($transaction->payments->count())
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5 sm:p-6">
        <h3 class="text-sm font-bold text-[#101828] mb-3">Riwayat Pembayaran</h3>
        <div class="divide-y divide-[#E4E7EC]">
            @foreach ($transaction->payments as $payment)
            <div class="py-3 flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-[#101828] m-0">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
                    <p class="text-xs text-[#667085] m-0">{{ $payment->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-[#101828] m-0">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $payment->isPaid() ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $payment->status->label() }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Back -->
    <div class="flex justify-start">
        <a href="{{ route('customer.pesanan.detail') }}"
            class="inline-flex items-center px-4 py-2 border border-[#E4E7EC] rounded-lg text-sm font-medium text-[#667085] hover:text-[#101828] hover:bg-[#F5F8FC] transition-colors no-underline">
            Kembali ke Detail Pesanan
        </a>
    </div>
</div>
@endsection
