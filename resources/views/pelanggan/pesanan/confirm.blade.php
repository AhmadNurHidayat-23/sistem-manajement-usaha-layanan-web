@extends('layouts.pelanggan', ['title' => 'Konfirmasi Pesanan'])

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col gap-1">
        <h2 class="text-xl sm:text-2xl font-bold text-[#101828] dark:text-[#F8FAFC] m-0">
            Konfirmasi Pesanan
        </h2>
        <p class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8] m-0">
            Periksa kembali rincian pesanan layanan Anda sebelum diproses
        </p>
    </div>

    <!-- Ringkasan Pesanan Card -->
    <div class="bg-white dark:bg-[#111C35] rounded-2xl border border-[#E4E7EC] dark:border-[#1E2D4A] shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E4E7EC] dark:border-[#1E2D4A] bg-[#F8FAFC] dark:bg-[#0E172E] flex items-center justify-between">
            <h3 class="text-sm sm:text-base font-bold text-[#101828] dark:text-[#F8FAFC] m-0 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#062072] dark:text-[#93C5FD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Ringkasan Pesanan
            </h3>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ !empty($data['pickup_option']) ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                {{ !empty($data['pickup_option']) ? 'Layanan Antar-Jemput' : 'Serahkan di Cabang' }}
            </span>
        </div>
        <div class="p-5 sm:p-6">
            <dl class="divide-y divide-[#E4E7EC] dark:divide-[#1E2D4A]/70 m-0">
                <div class="py-3 flex items-center justify-between gap-4">
                    <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8]">Cabang Layanan</dt>
                    <dd class="text-xs sm:text-sm font-semibold text-[#101828] dark:text-[#F8FAFC] text-right m-0">
                        {{ $branch->display_name }}
                    </dd>
                </div>
                <div class="py-3 flex items-center justify-between gap-4">
                    <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8]">Nama Layanan</dt>
                    <dd class="text-xs sm:text-sm font-semibold text-[#101828] dark:text-[#F8FAFC] text-right m-0">
                        {{ $service->name }}
                    </dd>
                </div>
                <div class="py-3 flex items-center justify-between gap-4">
                    <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8]">Kategori</dt>
                    <dd class="text-xs sm:text-sm font-medium text-[#101828] dark:text-[#F8FAFC] text-right m-0">
                        {{ $service->category?->name ?? 'Layanan Reguler' }}
                    </dd>
                </div>

                <div class="py-3 flex items-center justify-between gap-4">
                    <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8]">Jenis Layanan</dt>
                    <dd class="text-xs sm:text-sm font-medium text-[#101828] dark:text-[#F8FAFC] text-right m-0">
                        @if($service->service_type === 'appointment')
                            Janji Temu
                        @elseif(!empty($data['pickup_option']))
                            Layanan Antar-Jemput
                        @else
                            Serahkan di Cabang
                        @endif
                    </dd>
                </div>

                @if ($isWeightBased)
                    <div class="py-3 flex items-center justify-between gap-4">
                        <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8]">Berat Cucian</dt>
                        <dd class="text-xs sm:text-sm font-medium text-amber-700 dark:text-amber-400 text-right m-0">
                            Ditimbang oleh petugas saat diterima di cabang
                        </dd>
                    </div>
                    @if (!empty($data['estimated_quantity']))
                        <div class="py-3 flex items-center justify-between gap-4">
                            <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8]">Perkiraan Barang/Kantong</dt>
                            <dd class="text-xs sm:text-sm font-medium text-[#101828] dark:text-[#F8FAFC] text-right m-0">
                                {{ (float) $data['estimated_quantity'] }} kantong/item
                            </dd>
                        </div>
                    @endif
                @else
                    <div class="py-3 flex items-center justify-between gap-4">
                        <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8]">Jumlah Layanan</dt>
                        <dd class="text-xs sm:text-sm font-medium text-[#101828] dark:text-[#F8FAFC] text-right m-0">
                            {{ !empty($data['estimated_quantity']) ? (float) $data['estimated_quantity'] . ' paket' : '1 paket / sesi' }}
                        </dd>
                    </div>
                @endif

                <div class="py-3 flex items-center justify-between gap-4">
                    <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8]">Estimasi Waktu Pengerjaan</dt>
                    <dd class="text-xs sm:text-sm font-medium text-[#101828] dark:text-[#F8FAFC] text-right m-0">
                        {{ \App\Models\Service::formatEstimatedDuration($estimatedDuration) }}
                    </dd>
                </div>

                <div class="py-3 flex items-center justify-between gap-4">
                    <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8]">Metode Pengambilan</dt>
                    <dd class="text-xs sm:text-sm font-medium text-[#101828] dark:text-[#F8FAFC] text-right m-0">
                        {{ !empty($data['pickup_option']) ? 'Layanan Antar-Jemput' : 'Drop-off Mandiri di Cabang' }}
                    </dd>
                </div>

                @if (!empty($data['pickup_option']) && !empty($data['pickup_address']))
                    <div class="py-3 flex items-start justify-between gap-4">
                        <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8] shrink-0">Alamat Penjemputan</dt>
                        <dd class="text-xs sm:text-sm font-medium text-[#101828] dark:text-[#F8FAFC] text-right max-w-sm break-words m-0">
                            {{ $data['pickup_address'] }}
                        </dd>
                    </div>
                    @if (!empty($data['different_delivery_location']) && !empty($data['delivery_address']))
                        <div class="py-3 flex items-start justify-between gap-4">
                            <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8] shrink-0">Alamat Pengantaran</dt>
                            <dd class="text-xs sm:text-sm font-medium text-[#101828] dark:text-[#F8FAFC] text-right max-w-sm break-words m-0">
                                {{ $data['delivery_address'] }}
                            </dd>
                        </div>
                    @endif
                    @if (!empty($data['location_notes']))
                        <div class="py-3 flex items-start justify-between gap-4">
                            <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8] shrink-0">Catatan Lokasi</dt>
                            <dd class="text-xs sm:text-sm text-[#101828] dark:text-[#F8FAFC] text-right max-w-sm break-words m-0">
                                {{ $data['location_notes'] }}
                            </dd>
                        </div>
                    @endif
                @endif

                @if (!empty($data['notes']))
                    <div class="py-3 flex items-start justify-between gap-4">
                        <dt class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8] shrink-0">Catatan Pesanan</dt>
                        <dd class="text-xs sm:text-sm text-[#101828] dark:text-[#F8FAFC] text-right max-w-sm break-words m-0">
                            {{ $data['notes'] }}
                        </dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Informasi Tarif & Harga Card -->
    <div class="bg-white dark:bg-[#111C35] rounded-2xl border border-[#E4E7EC] dark:border-[#1E2D4A] shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E4E7EC] dark:border-[#1E2D4A] bg-[#F8FAFC] dark:bg-[#0E172E]">
            <h3 class="text-sm sm:text-base font-bold text-[#101828] dark:text-[#F8FAFC] m-0 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#062072] dark:text-[#93C5FD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Informasi Tarif & Harga
            </h3>
        </div>
        <div class="p-5 sm:p-6 space-y-3">
            @if ($isWeightBased)
                <div class="flex items-center justify-between text-xs sm:text-sm">
                    <span class="text-[#667085] dark:text-[#94A3B8]">Tarif Layanan</span>
                    <span class="font-semibold text-[#101828] dark:text-[#F8FAFC]">Rp {{ number_format($service->price, 0, ',', '.') }} / kg</span>
                </div>

                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-900/50 text-xs text-amber-900 dark:text-amber-200 leading-relaxed">
                    Harga awal merupakan tarif per kilogram. Total akhir pembayaran ditentukan setelah penimbangan oleh petugas saat barang diterima di cabang.
                </div>

                @if (!empty($promoResult) && !empty($promoResult['valid']))
                    <div class="flex items-center justify-between text-xs sm:text-sm text-emerald-700 dark:text-emerald-400">
                        <span>Diskon Promo ({{ $data['promo_code'] }})</span>
                        <span class="font-semibold">- Rp {{ number_format($promoResult['discount'], 0, ',', '.') }}</span>
                    </div>
                @elseif (!empty($promoResult) && empty($promoResult['valid']))
                    <div class="p-2.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-xl text-xs text-rose-700 dark:text-rose-300">
                        {{ $promoResult['message'] }}
                    </div>
                @endif

                <div class="flex items-center justify-between text-base sm:text-lg pt-3 border-t border-[#E4E7EC] dark:border-[#1E2D4A]">
                    <span class="font-bold text-[#101828] dark:text-[#F8FAFC]">Estimasi Total</span>
                    <span class="font-bold text-[#062072] dark:text-[#93C5FD]">Menunggu Penimbangan</span>
                </div>
                <p class="text-[11px] text-[#667085] dark:text-[#94A3B8] m-0">* Total akhir disesuaikan dengan berat aktual hasil penimbangan petugas.</p>
            @else
                <div class="flex items-center justify-between text-xs sm:text-sm">
                    <span class="text-[#667085] dark:text-[#94A3B8]">Harga Layanan</span>
                    <span class="font-semibold text-[#101828] dark:text-[#F8FAFC]">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-xs sm:text-sm">
                    <span class="text-[#667085] dark:text-[#94A3B8]">Subtotal Tagihan</span>
                    <span class="font-semibold text-[#101828] dark:text-[#F8FAFC]">Rp {{ number_format($estimatedPrice, 0, ',', '.') }}</span>
                </div>

                @if (!empty($promoResult) && !empty($promoResult['valid']))
                    <div class="flex items-center justify-between text-xs sm:text-sm text-emerald-700 dark:text-emerald-400">
                        <span>Diskon Promo ({{ $data['promo_code'] }})</span>
                        <span class="font-semibold">- Rp {{ number_format($promoResult['discount'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-base sm:text-lg pt-3 border-t border-[#E4E7EC] dark:border-[#1E2D4A]">
                        <span class="font-bold text-[#101828] dark:text-[#F8FAFC]">Total Pembayaran</span>
                        <span class="font-bold text-[#062072] dark:text-[#93C5FD]">Rp {{ number_format($promoResult['final_total'], 0, ',', '.') }}</span>
                    </div>
                @elseif (!empty($promoResult) && empty($promoResult['valid']))
                    <div class="p-2.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-xl text-xs text-rose-700 dark:text-rose-300">
                        {{ $promoResult['message'] }}
                    </div>
                    <div class="flex items-center justify-between text-base sm:text-lg pt-3 border-t border-[#E4E7EC] dark:border-[#1E2D4A]">
                        <span class="font-bold text-[#101828] dark:text-[#F8FAFC]">Total Pembayaran</span>
                        <span class="font-bold text-[#062072] dark:text-[#93C5FD]">Rp {{ number_format($estimatedPrice, 0, ',', '.') }}</span>
                    </div>
                @else
                    <div class="flex items-center justify-between text-base sm:text-lg pt-3 border-t border-[#E4E7EC] dark:border-[#1E2D4A]">
                        <span class="font-bold text-[#101828] dark:text-[#F8FAFC]">Total Pembayaran</span>
                        <span class="font-bold text-[#062072] dark:text-[#93C5FD]">Rp {{ number_format($estimatedPrice, 0, ',', '.') }}</span>
                    </div>
                @endif
                <p class="text-[11px] text-[#667085] dark:text-[#94A3B8] m-0">* Harga tetap sesuai paket layanan yang dipilih.</p>
            @endif
        </div>
    </div>

    <!-- Tombol Aksi Halaman -->
    <div class="flex items-center justify-between gap-4 pt-2">
        <a href="{{ route('customer.pesanan.create', [
                'branch_id' => $data['branch_id'],
                'service_id' => $data['service_id'],
                'estimated_quantity' => $data['estimated_quantity'] ?? null,
                'pickup_option' => $data['pickup_option'] ?? null,
                'pickup_address' => $data['pickup_address'] ?? null,
                'different_delivery_location' => $data['different_delivery_location'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'location_notes' => $data['location_notes'] ?? null,
                'notes' => $data['notes'] ?? null,
                'promo_code' => $data['promo_code'] ?? null,
            ]) }}"
            onclick="if (window.history.length > 1) { window.history.back(); return false; }"
            id="btn-back-to-order"
            class="inline-flex items-center px-4 py-2.5 border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-xl text-xs sm:text-sm font-semibold text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] transition-colors cursor-pointer bg-white dark:bg-[#152342] shadow-xs no-underline">
            Kembali Edit
        </a>

        <form method="POST" action="{{ route('customer.pesanan.store') }}" id="form-confirm-order" class="m-0">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $data['branch_id'] }}">
            <input type="hidden" name="service_id" value="{{ $data['service_id'] }}">
            <input type="hidden" name="estimated_quantity" value="{{ $data['estimated_quantity'] ?? '' }}">
            <input type="hidden" name="pickup_option" value="{{ $data['pickup_option'] ?? 0 }}">
            <input type="hidden" name="pickup_address" value="{{ $data['pickup_address'] ?? '' }}">
            <input type="hidden" name="pickup_latitude" value="{{ $data['pickup_latitude'] ?? '' }}">
            <input type="hidden" name="pickup_longitude" value="{{ $data['pickup_longitude'] ?? '' }}">
            <input type="hidden" name="different_delivery_location" value="{{ !empty($data['different_delivery_location']) ? '1' : '0' }}">
            <input type="hidden" name="delivery_address" value="{{ $data['delivery_address'] ?? '' }}">
            <input type="hidden" name="delivery_latitude" value="{{ $data['delivery_latitude'] ?? '' }}">
            <input type="hidden" name="delivery_longitude" value="{{ $data['delivery_longitude'] ?? '' }}">
            <input type="hidden" name="location_notes" value="{{ $data['location_notes'] ?? '' }}">
            <input type="hidden" name="notes" value="{{ $data['notes'] ?? '' }}">
            @if (!empty($data['promo_code']))
                <input type="hidden" name="promo_code" value="{{ $data['promo_code'] }}">
            @endif
            <button type="submit" id="btn-submit-order"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white rounded-xl text-xs sm:text-sm font-semibold transition-colors cursor-pointer shadow-sm border-0 disabled:opacity-60 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Pesan Sekarang</span>
            </button>
        </form>
    </div>
</div>
@endsection
