@extends('layouts.app', ['title' => 'Detail Transaksi ' . $transaction->transaction_code])

@section('content')
<div class="w-full space-y-5">
    <!-- Header Detail Transaksi -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h3 class="text-xl font-bold text-[#101828] m-0">{{ $transaction->transaction_code }}</h3>
                @php
                    $status = $transaction->status instanceof \App\Enums\StatusTransaksi ? $transaction->status : \App\Enums\StatusTransaksi::tryFrom((string) $transaction->status) ?? \App\Enums\StatusTransaksi::Paid;
                @endphp
                <x-badges.badge :variant="$status->badgeVariant()">{{ $status->label() }}</x-badges.badge>
            </div>
            <p class="text-xs text-[#667085] mt-1 m-0">
                Waktu Transaksi: {{ $transaction->transaction_date->format('d M Y, H:i') }} WIB
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('pos.transactions.index') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 h-[38px] bg-white border border-[#E4E7EC] hover:bg-[#F5F8FC] text-[#101828] rounded-lg text-xs font-semibold shadow-2xs transition-colors">
                <svg class="w-4 h-4 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>

            <a href="{{ route('pos.transactions.receipt', $transaction) }}" class="inline-flex items-center justify-center gap-1.5 px-4 h-[38px] bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-xs font-bold shadow-2xs transition-colors">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Struk
            </a>
        </div>
    </div>

    <!-- 3 Card Informasi Transaksi Seimbang -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Cabang & Kasir -->
        <div class="bg-white border border-[#E4E7EC] rounded-xl p-4 sm:p-5 shadow-2xs flex flex-col justify-between h-full">
            <div>
                <span class="text-[12px] text-[#667085] block mb-1 font-medium">Usaha & Kasir</span>
                <p class="font-semibold text-[#101828] text-[15px] m-0">{{ $transaction->branch->display_name }}</p>
            </div>
            <p class="text-[12px] text-[#667085] m-0 mt-3 pt-2 border-t border-slate-100">
                Dilayani oleh: <strong class="font-semibold text-[#101828]">{{ $transaction->cashier->name }}</strong>
            </p>
        </div>

        <!-- Sumber Transaksi -->
        <div class="bg-white border border-[#E4E7EC] rounded-xl p-4 sm:p-5 shadow-2xs flex flex-col justify-between h-full">
            <div>
                <span class="text-[12px] text-[#667085] block mb-1 font-medium">Sumber Transaksi</span>
                @if ($transaction->booking_id)
                    <p class="font-semibold text-[#062072] text-[15px] m-0">Booking #{{ $transaction->booking_id }}</p>
                @elseif ($transaction->order_id)
                    <p class="font-semibold text-[#062072] text-[15px] m-0">Drop-off Pesanan #{{ $transaction->order_id }}</p>
                @else
                    <p class="font-semibold text-[#101828] text-[15px] m-0">Walk-in Langsung</p>
                @endif
            </div>
            <p class="text-[12px] text-[#667085] m-0 mt-3 pt-2 border-t border-slate-100">
                @if ($transaction->booking_id)
                    Pelanggan: <strong class="font-semibold text-[#101828]">{{ $transaction->booking?->user?->name ?? '-' }}</strong>
                @elseif ($transaction->order_id)
                    Pelanggan: <strong class="font-semibold text-[#101828]">{{ $transaction->order?->user?->name ?? '-' }}</strong>
                @else
                    Transaksi langsung kasir
                @endif
            </p>
        </div>

        <!-- Pelanggan -->
        <div class="bg-white border border-[#E4E7EC] rounded-xl p-4 sm:p-5 shadow-2xs flex flex-col justify-between h-full">
            <div>
                <span class="text-[12px] text-[#667085] block mb-1 font-medium">Pelanggan</span>
                <p class="font-semibold text-[#101828] text-[15px] m-0">{{ $transaction->customer_name ?: 'Pelanggan Umum' }}</p>
            </div>
            <p class="text-[12px] text-[#667085] m-0 mt-3 pt-2 border-t border-slate-100">
                {{ $transaction->customer_phone ?: 'Tanpa nomor telepon' }}
            </p>
        </div>
    </div>

    <!-- Card Rincian Item Transaksi -->
    <div class="bg-white border border-[#E4E7EC] rounded-xl shadow-2xs overflow-hidden">
        <div class="px-4 py-3.5 sm:px-5 border-b border-[#E4E7EC] flex items-center justify-between">
            <h4 class="text-sm font-bold text-[#101828] m-0">Rincian Item Transaksi</h4>
            <span class="text-xs text-[#667085] font-medium">{{ $transaction->details->count() }} item</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] font-semibold uppercase text-[11px]">
                    <tr>
                        <th class="py-2.5 px-4 w-12 text-center">No</th>
                        <th class="py-2.5 px-4">Nama Layanan</th>
                        <th class="py-2.5 px-4 text-right">Harga Satuan</th>
                        <th class="py-2.5 px-4 text-center">Jumlah</th>
                        <th class="py-2.5 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC] text-[13px]">
                    @foreach ($transaction->details as $idx => $d)
                        <tr class="hover:bg-[#F5F8FC]/60 transition-colors">
                            <td class="py-2.5 px-4 text-center text-[#667085] text-xs">{{ $idx + 1 }}</td>
                            <td class="py-2.5 px-4 font-semibold text-[#101828]">{{ $d->service?->name ?? 'Layanan' }}</td>
                            <td class="py-2.5 px-4 text-right text-[#667085]">Rp {{ number_format($d->price, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-4 text-center font-bold text-[#101828]">{{ $d->qty }}</td>
                            <td class="py-2.5 px-4 text-right font-bold text-[#101828]">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Ringkasan Subtotal dan Total di Sisi Kanan Bawah -->
        <div class="border-t border-[#E4E7EC] bg-[#F5F8FC]/40 px-4 py-3 sm:px-6">
            <div class="flex flex-col items-end">
                <div class="w-full sm:w-80 space-y-1.5 text-xs">
                    <div class="flex justify-between items-center text-[#667085]">
                        <span class="text-[13px]">Subtotal</span>
                        <span class="font-semibold text-[13px] text-[#101828]">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                    </div>

                    @if ($transaction->discount > 0)
                        <div class="flex justify-between items-center text-emerald-600">
                            <span class="text-[13px]">Diskon</span>
                            <span class="font-semibold text-[13px]">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if (isset($transaction->tax) && $transaction->tax > 0)
                        <div class="flex justify-between items-center text-[#667085]">
                            <span class="text-[13px]">Pajak</span>
                            <span class="font-semibold text-[13px] text-[#101828]">Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between items-center pt-2.5 border-t border-[#E4E7EC] text-sm">
                        <span class="font-bold text-[#062072]">Total Akhir</span>
                        <span class="font-bold text-base text-[#062072]">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Pembayaran -->
    <div class="bg-white border border-[#E4E7EC] rounded-xl shadow-2xs overflow-hidden">
        <div class="px-4 py-3.5 sm:px-5 border-b border-[#E4E7EC]">
            <h4 class="text-sm font-bold text-[#101828] m-0">Riwayat Pembayaran</h4>
        </div>

        <div class="p-4 sm:p-5">
            @if ($transaction->payments->isEmpty())
                <p class="text-xs text-[#667085] py-4 text-center m-0">Belum ada catatan pembayaran untuk transaksi ini.</p>
            @else
                <div class="space-y-2.5">
                    @foreach ($transaction->payments as $p)
                        @php
                            $paymentStatus = $p->status instanceof \App\Enums\StatusPembayaran ? $p->status : \App\Enums\StatusPembayaran::tryFrom((string) $p->status) ?? \App\Enums\StatusPembayaran::Completed;
                            $pStatusVariant = $paymentStatus->badgeVariant();
                            $pStatusLabel = $paymentStatus->label();
                        @endphp
                        <div class="p-3 border border-[#E4E7EC] rounded-lg flex flex-wrap items-center justify-between gap-3 text-xs bg-white hover:bg-[#F5F8FC]/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $pStatusVariant === 'success' ? 'bg-emerald-50 text-emerald-600' : ($pStatusVariant === 'warning' ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}">
                                    @if ($pStatusVariant === 'success')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @elseif ($pStatusVariant === 'warning')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-[#101828] uppercase text-xs">{{ $p->payment_method }}</span>
                                        <x-badges.badge :variant="$pStatusVariant">
                                            {{ $pStatusLabel }}
                                        </x-badges.badge>
                                    </div>
                                    <span class="text-[11px] text-[#667085] block mt-0.5">
                                        Waktu: {{ $p->paid_at ? $p->paid_at->format('d M Y, H:i') : $p->created_at->format('d M Y, H:i') }} WIB
                                    </span>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="text-sm font-bold text-[#062072] block">Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                                @if ($p->reference_number)
                                    <span class="text-[10px] text-[#667085] font-medium">Ref: {{ $p->reference_number }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
