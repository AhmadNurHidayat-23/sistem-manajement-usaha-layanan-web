@extends('layouts.app', ['title' => 'Pilih Metode Pembayaran | SIMAUL'])

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-[#E4E7EC]">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">Pilih Metode Pembayaran</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Pilih metode pembayaran yang akan digunakan untuk menyelesaikan transaksi ini.</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($pendingPayment)
                <a href="{{ route('kasir.pembayaran.waiting', $pendingPayment) }}"
                    class="inline-flex items-center px-3.5 py-2 text-xs font-semibold border border-[#E4E7EC] rounded-lg bg-white text-[#344054] hover:bg-[#F5F8FC] transition-colors shadow-2xs">
                    Kembali ke Status Pembayaran
                </a>
            @else
                <a href="{{ route('pos.index') }}"
                    class="inline-flex items-center px-3.5 py-2 text-xs font-semibold border border-[#E4E7EC] rounded-lg bg-white text-[#344054] hover:bg-[#F5F8FC] transition-colors shadow-2xs">
                    Kembali ke Terminal POS
                </a>
            @endif
        </div>
    </div>

    {{-- Notifikasi Error --}}
    @if (session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-xs flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Status Pembayaran Menunggu --}}
    @if ($pendingPayment)
        <div class="bg-amber-50 border border-amber-200 px-4 py-3 rounded-lg text-xs flex items-start gap-2.5">
            <svg class="w-4 h-4 shrink-0 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="space-y-0.5">
                <p class="font-medium text-amber-900 m-0">
                    Ada pembayaran yang masih menunggu konfirmasi ({{ strtoupper(str_replace('_', ' ', $pendingPayment->payment_method)) }}).
                </p>
                <p class="text-amber-800 m-0">
                    Memilih metode lain akan mengganti proses pembayaran yang sedang berjalan.
                    <a href="{{ route('kasir.pembayaran.waiting', $pendingPayment) }}" class="underline font-semibold ml-1 text-amber-900">Lihat status pembayaran</a>
                </p>
            </div>
        </div>
    @endif

    {{-- Ringkasan Transaksi Compact --}}
    <div class="bg-white border border-[#E4E7EC] rounded-xl p-4 sm:p-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-medium text-[#667085] block">Total yang Harus Dibayar</span>
                <div class="text-2xl sm:text-3xl font-bold text-[#062072] mt-0.5">
                    Rp {{ number_format((float) $transaction->total, 0, ',', '.') }}
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs border-t sm:border-t-0 sm:border-l border-[#E4E7EC] pt-3 sm:pt-0 sm:pl-5">
                <div>
                    <span class="text-[#667085] block">Nomor Referensi</span>
                    <span class="font-semibold text-[#101828]">{{ $transaction->transaction_code }}</span>
                </div>
                @if ($transaction->branch?->display_name ?? $transaction->branch?->name)
                    <div>
                        <span class="text-[#667085] block">Cabang</span>
                        <span class="font-medium text-[#101828]">{{ $transaction->branch->display_name ?? $transaction->branch->name }}</span>
                    </div>
                @endif
                @php
                    $customerName = $transaction->booking?->user?->name ?? $transaction->order?->user?->name ?? null;
                @endphp
                @if ($customerName)
                    <div>
                        <span class="text-[#667085] block">Pelanggan</span>
                        <span class="font-medium text-[#101828]">{{ $customerName }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Kelompok 1: QRIS --}}
    <div class="space-y-2">
        <h4 class="text-xs font-bold text-[#101828] uppercase tracking-wider m-0">QRIS</h4>
        <div class="bg-white border border-[#E4E7EC] hover:border-[#062072]/30 rounded-xl p-3.5 sm:p-4 shadow-2xs transition-colors">
            <form method="POST" action="{{ route('payment.gateway.initiate', $transaction) }}" class="m-0">
                @csrf
                <input type="hidden" name="method" value="qris">
                <input type="hidden" name="channel" value="qris">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-lg bg-[#F5F8FC] border border-[#E4E7EC] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-[#101828] m-0">QRIS</p>
                            <p class="text-xs text-[#667085] m-0 truncate">GoPay, OVO, DANA, ShopeePay, LinkAja & Seluruh Mobile Banking</p>
                        </div>
                    </div>
                    <button type="submit" id="btn-pay-qris"
                        class="px-4 py-2 bg-[#062072] hover:bg-[#04184F] text-white text-xs font-semibold rounded-lg transition-colors shrink-0 shadow-2xs">
                        Pilih
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Kelompok 2: E-Wallet --}}
    <div class="space-y-2">
        <h4 class="text-xs font-bold text-[#101828] uppercase tracking-wider m-0">E-Wallet</h4>
        @php
            $wallets = [
                ['channel' => 'gopay', 'label' => 'GoPay', 'desc' => 'Dompet digital GoPay & Gojek'],
                ['channel' => 'ovo', 'label' => 'OVO', 'desc' => 'Dompet digital OVO'],
                ['channel' => 'dana', 'label' => 'DANA', 'desc' => 'Dompet digital DANA'],
                ['channel' => 'shopeepay', 'label' => 'ShopeePay', 'desc' => 'Dompet digital ShopeePay'],
            ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach ($wallets as $wallet)
                <div class="bg-white border border-[#E4E7EC] hover:border-[#062072]/30 rounded-xl p-3.5 shadow-2xs transition-colors">
                    <form method="POST" action="{{ route('payment.gateway.initiate', $transaction) }}" class="m-0">
                        @csrf
                        <input type="hidden" name="method" value="e_wallet">
                        <input type="hidden" name="channel" value="{{ $wallet['channel'] }}">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-[#F5F8FC] border border-[#E4E7EC] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-[#101828] m-0">{{ $wallet['label'] }}</p>
                                    <p class="text-[11px] text-[#667085] m-0 truncate">{{ $wallet['desc'] }}</p>
                                </div>
                            </div>
                            <button type="submit" id="btn-pay-{{ $wallet['channel'] }}"
                                class="px-3.5 py-1.5 bg-[#062072] hover:bg-[#04184F] text-white text-xs font-semibold rounded-lg transition-colors shrink-0 shadow-2xs">
                                Pilih
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Kelompok 3: Virtual Account --}}
    <div class="space-y-2">
        <h4 class="text-xs font-bold text-[#101828] uppercase tracking-wider m-0">Virtual Account</h4>
        @php
            $banks = [
                ['channel' => 'bca', 'label' => 'BCA Virtual Account', 'desc' => 'Transfer via ATM / KlikBCA / myBCA'],
                ['channel' => 'bni', 'label' => 'BNI Virtual Account', 'desc' => 'Transfer via ATM / BNI Mobile'],
                ['channel' => 'bri', 'label' => 'BRI Virtual Account', 'desc' => 'Transfer via ATM / BRImo'],
                ['channel' => 'mandiri', 'label' => 'Mandiri Virtual Account', 'desc' => 'Transfer via Livin\' / ATM Mandiri'],
                ['channel' => 'permata', 'label' => 'Permata Virtual Account', 'desc' => 'Transfer via PermataMobile / ATM'],
            ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach ($banks as $bank)
                <div class="bg-white border border-[#E4E7EC] hover:border-[#062072]/30 rounded-xl p-3.5 shadow-2xs transition-colors">
                    <form method="POST" action="{{ route('payment.gateway.initiate', $transaction) }}" class="m-0">
                        @csrf
                        <input type="hidden" name="method" value="virtual_account">
                        <input type="hidden" name="channel" value="{{ $bank['channel'] }}">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-[#F5F8FC] border border-[#E4E7EC] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-[#101828] m-0">{{ $bank['label'] }}</p>
                                    <p class="text-[11px] text-[#667085] m-0 truncate">{{ $bank['desc'] }}</p>
                                </div>
                            </div>
                            <button type="submit" id="btn-pay-va-{{ $bank['channel'] }}"
                                class="px-3.5 py-1.5 bg-[#062072] hover:bg-[#04184F] text-white text-xs font-semibold rounded-lg transition-colors shrink-0 shadow-2xs">
                                Pilih
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
