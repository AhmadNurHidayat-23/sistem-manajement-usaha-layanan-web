@extends('layouts.app', ['title' => 'Status Pembayaran | SIMAUL'])

@section('content')
<div class="space-y-6" data-payment-polling="true" data-poll-url="{{ route('kasir.pembayaran.poll', $transaction) }}" data-is-pending="{{ $payment->isPending() ? 'true' : 'false' }}">
    {{-- Header Konten --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-[#E4E7EC]">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">Status Pembayaran</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">
                Nomor Referensi: <span class="font-semibold text-[#062072]">{{ $transaction->transaction_code }}</span>
                @if ($transaction->branch?->display_name ?? $transaction->branch?->name)
                    &bull; <span class="font-medium text-[#344054]">{{ $transaction->branch->display_name ?? $transaction->branch->name }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if (!$payment->isPaid())
                <a href="{{ route('kasir.pembayaran.show', $transaction) }}"
                    class="inline-flex items-center px-3.5 py-2 text-xs font-semibold border border-[#E4E7EC] rounded-lg bg-white text-[#344054] hover:bg-[#F5F8FC] hover:border-[#D0D5DD] transition-colors shadow-2xs">
                    Ganti Metode Pembayaran
                </a>
            @endif
        </div>
    </div>

    {{-- Layout Desktop: 2 Kolom Seimbang (5 : 7); Mobile: 1 Kolom --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- KOLOM KIRI: Ringkasan Pembayaran --}}
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-2xs space-y-4" id="payment-status-block">
                <div class="border-b border-[#E4E7EC] pb-3">
                    <h4 class="text-sm font-bold text-[#101828] m-0">Ringkasan Pembayaran</h4>
                </div>

                <div>
                    <span class="text-xs font-medium text-[#667085] block">Total yang Harus Dibayar</span>
                    <div class="text-2xl sm:text-3xl font-bold text-[#062072] mt-0.5">
                        Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                    </div>
                </div>

                <div class="border-t border-[#E4E7EC] pt-3.5 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[#667085]">Metode</span>
                        <span class="font-semibold uppercase text-[#101828]">
                            {{ str_replace('_', ' ', $payment->payment_method) }}
                            @if ($payment->channel && $payment->channel !== $payment->payment_method)
                                ({{ strtoupper($payment->channel) }})
                            @endif
                        </span>
                    </div>

                    @if ($payment->expired_at)
                        <div class="flex items-center justify-between">
                            <span class="text-[#667085]">Batas Pembayaran</span>
                            <span class="font-semibold text-[#101828]">
                                {{ $payment->expired_at->format('H.i') }} WIB
                            </span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <span class="text-[#667085]">Status</span>
                        <span id="status-badge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $payment->status->badgeClass() }}">
                            {{ $payment->status->label() }}
                        </span>
                    </div>
                </div>

                <div class="border-t border-[#E4E7EC] pt-3.5">
                    <span class="text-xs font-medium text-[#667085] block mb-1">Informasi:</span>
                    <div id="status-message" class="text-xs text-[#344054] p-3 rounded-lg bg-[#F5F8FC] border border-[#E4E7EC] leading-relaxed">
                        @if ($payment->isPaid())
                            Pembayaran telah berhasil dikonfirmasi dan transaksi telah lunas.
                        @elseif ($payment->status->value === 'expired')
                            Batas waktu pembayaran telah kedaluwarsa. Silakan ganti metode pembayaran untuk melanjutkan.
                        @elseif ($payment->status->value === 'failed')
                            Pembayaran gagal diproses oleh gateway. Silakan coba kembali atau pilih metode lain.
                        @else
                            Menunggu konfirmasi pembayaran. Status akan diperbarui secara otomatis setelah pembayaran diterima.
                        @endif
                    </div>
                </div>

                @if (!$payment->isPaid())
                    <div class="pt-2">
                        <a href="{{ route('kasir.pembayaran.show', $transaction) }}"
                            class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-[#E4E7EC] rounded-lg bg-white text-[#344054] font-semibold text-xs hover:bg-[#F5F8FC] transition-colors shadow-2xs">
                            Ganti Metode Pembayaran
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- KOLOM KANAN: Instruksi Pembayaran Aktual --}}
        <div class="lg:col-span-7 space-y-4">
            @php
                $payload = $payment->gateway_payload ?? [];
                $displayQr = $payload['qr_url_v1'] ?? $payload['qr_url'] ?? null;
                $copyQrUrl = $payload['qr_url_v2'] ?? $payload['qr_url'] ?? $displayQr;
            @endphp

            @if ($payment->isPaid())
                <div class="bg-white border border-[#E4E7EC] rounded-xl p-6 shadow-2xs text-center space-y-3">
                    <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-[#101828] m-0">Pembayaran Berhasil Diterima</h4>
                    <p class="text-xs text-[#667085] max-w-sm mx-auto m-0">
                        Tagihan sebesar <strong class="text-[#101828]">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</strong> telah lunas. Struk transaksi resmi siap dicetak.
                    </p>
                    <div class="pt-3">
                        <a href="{{ route('kasir.struk', $transaction) }}"
                            class="inline-flex items-center px-4 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white font-semibold text-xs rounded-lg transition-colors shadow-xs">
                            Lihat Struk
                        </a>
                    </div>
                </div>
            @elseif ($payment->payment_method === 'qris' && !empty($displayQr) && $payment->isPending())
                <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-2xs space-y-4">
                    <div class="border-b border-[#E4E7EC] pb-3">
                        <h4 class="text-sm font-bold text-[#101828] m-0">Scan Kode QRIS</h4>
                        <p class="text-xs text-[#667085] mt-0.5 m-0">Arahkan kamera aplikasi pembayaran ke kode QR di bawah untuk membayar</p>
                    </div>

                    {{-- QR Code Bersih (Hanya Barcode/QR di atas background netral tanpa template poster/GPN) --}}
                    <div class="flex flex-col items-center justify-center py-2">
                        <div class="w-60 sm:w-64 aspect-square bg-white border border-[#E4E7EC] rounded-xl p-3 flex items-center justify-center shadow-xs">
                            <img src="{{ $displayQr }}" alt="Kode QRIS" class="w-full h-full object-contain" id="qris-image">
                        </div>
                        <p class="text-[11px] text-[#667085] mt-2 m-0 text-center">QR code resmi &bull; Dapat dipindai seluruh aplikasi pembayaran</p>
                    </div>

                    <div class="flex justify-center">
                        <button type="button" id="btn-copy-qris-url" data-copy-target="{{ $copyQrUrl }}"
                            class="text-xs px-3.5 py-1.5 border border-[#E4E7EC] rounded-lg bg-white hover:bg-[#F5F8FC] text-[#344054] font-semibold transition-colors shadow-2xs inline-flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span>Salin URL Gambar QRIS</span>
                        </button>
                    </div>

                    <div class="bg-[#F5F8FC] border border-[#E4E7EC] rounded-lg p-3.5 space-y-2 text-xs">
                        <span class="font-bold text-[#101828] block">Cara Melakukan Pembayaran:</span>
                        <ol class="text-[#667085] space-y-1 pl-4 list-decimal m-0 leading-relaxed">
                            <li>Buka aplikasi e-wallet (GoPay, OVO, DANA, ShopeePay) atau Mobile Banking.</li>
                            <li>Pilih menu <strong>Scan / Bayar</strong> dan arahkan kamera ke kode QR di atas.</li>
                            <li>Periksa nominal tagihan <span class="font-semibold text-[#101828]">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</span> lalu konfirmasi pembayaran.</li>
                        </ol>
                    </div>
                </div>
            @elseif ($payment->payment_method === 'virtual_account' && isset($payload['va_number']) && $payment->isPending())
                <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-2xs space-y-4">
                    <div class="border-b border-[#E4E7EC] pb-3">
                        <h4 class="text-sm font-bold text-[#101828] m-0">Instruksi Virtual Account</h4>
                        <p class="text-xs text-[#667085] mt-0.5 m-0">{{ $payload['va_display'] ?? 'Transfer ke nomor Virtual Account resmi berikut' }}</p>
                    </div>

                    <div class="text-center bg-[#F5F8FC] border border-[#E4E7EC] rounded-xl p-4 space-y-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[#667085] block">
                            {{ strtoupper($payload['bank'] ?? 'Bank') }} Virtual Account
                        </span>
                        <div class="text-2xl sm:text-3xl tabular-nums font-bold tracking-wider text-[#062072]">
                            {{ $payload['va_number'] }}
                        </div>
                        <div>
                            <button type="button" id="btn-copy-va" data-copy-target="{{ $payload['va_number'] }}"
                                class="text-xs px-3.5 py-1.5 border border-[#E4E7EC] rounded-lg bg-white hover:bg-[#F5F8FC] text-[#344054] font-semibold transition-colors shadow-2xs">
                                Salin Nomor VA
                            </button>
                        </div>
                    </div>

                    <div class="bg-[#F5F8FC] border border-[#E4E7EC] rounded-lg p-3.5 space-y-2 text-xs">
                        <span class="font-bold text-[#101828] block">Petunjuk Transfer:</span>
                        <ol class="text-[#667085] space-y-1 pl-4 list-decimal m-0 leading-relaxed">
                            <li>Masuk ke ATM atau aplikasi Mobile Banking {{ strtoupper($payload['bank'] ?? 'Bank') }}.</li>
                            <li>Pilih menu <strong>Transfer / Pembayaran > Virtual Account</strong>.</li>
                            <li>Masukkan nomor Virtual Account di atas.</li>
                            <li>Pastikan nominal tagihan sesuai lalu selesaikan transaksi.</li>
                        </ol>
                    </div>
                </div>
            @elseif ($payment->payment_method === 'e_wallet' && isset($payload['checkout_url']) && !isset($payload['redirect_url']) && $payment->isPending())
                <div class="bg-white border border-[#E4E7EC] rounded-xl p-6 shadow-2xs text-center space-y-3">
                    <h4 class="text-sm font-bold text-[#101828] m-0">Pembayaran E-Wallet</h4>
                    <p class="text-xs text-[#667085] m-0">Klik tombol di bawah untuk melanjutkan ke proses pembayaran {{ strtoupper($payment->channel ?? 'E-Wallet') }}:</p>
                    <div class="pt-2">
                        <a href="{{ $payload['checkout_url'] }}" target="_blank" id="btn-open-ewallet"
                            class="inline-flex items-center px-5 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white text-xs font-semibold rounded-lg transition-colors shadow-xs">
                            Buka Aplikasi {{ strtoupper($payment->channel ?? 'E-Wallet') }}
                        </a>
                    </div>
                </div>
            @elseif ($payment->payment_method !== 'qris' && isset($payload['redirect_url']) && $payment->isPending())
                <div class="bg-white border border-[#E4E7EC] rounded-xl p-6 shadow-2xs text-center space-y-3">
                    <h4 class="text-sm font-bold text-[#101828] m-0">Halaman Pembayaran Gateway</h4>
                    <p class="text-xs text-[#667085] m-0">Klik tombol di bawah untuk membuka portal pembayaran yang aman:</p>
                    <div class="pt-2">
                        <a href="{{ $payload['redirect_url'] }}" target="_blank" id="btn-open-snap"
                            class="inline-flex items-center px-5 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white text-xs font-semibold rounded-lg transition-colors shadow-xs">
                            Buka Halaman Pembayaran
                        </a>
                    </div>
                </div>
            @elseif ($payment->status->value === 'expired')
                <div class="bg-white border border-[#E4E7EC] rounded-xl p-6 shadow-2xs text-center space-y-3">
                    <h4 class="text-sm font-bold text-[#101828] m-0">Pembayaran Kedaluwarsa</h4>
                    <p class="text-xs text-[#667085] m-0">Waktu pembayaran telah habis. Tidak ada tagihan yang diproses.</p>
                    <div class="pt-2">
                        <a href="{{ route('kasir.pembayaran.show', $payment->transaction->transaction_code) }}"
                            class="inline-flex items-center px-4 py-2 border border-[#E4E7EC] rounded-lg bg-white text-xs font-semibold text-[#062072] hover:bg-[#F5F8FC] transition-colors shadow-2xs">
                            Pilih Metode Baru
                        </a>
                    </div>
                </div>
            @elseif ($payment->status->value === 'failed')
                <div class="bg-white border border-[#E4E7EC] rounded-xl p-6 shadow-2xs text-center space-y-3">
                    <h4 class="text-sm font-bold text-[#101828] m-0">Pembayaran Gagal</h4>
                    <p class="text-xs text-[#667085] m-0">Pembayaran gagal diproses oleh penyedia layanan.</p>
                    <div class="pt-2">
                        <a href="{{ route('kasir.pembayaran.show', $payment->transaction->transaction_code) }}"
                            class="inline-flex items-center px-4 py-2 border border-[#E4E7EC] rounded-lg bg-white text-xs font-semibold text-[#062072] hover:bg-[#F5F8FC] transition-colors shadow-2xs">
                            Coba Metode Lain
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
