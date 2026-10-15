@extends('layouts.pelanggan', ['title' => 'Konfirmasi Pesanan'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-xl font-bold text-[#101828] m-0">Konfirmasi Pesanan</h2>
        <p class="text-xs text-[#667085] mt-1 m-0">Periksa kembali detail pesanan sebelum disubmit</p>
    </div>

    <!-- Ringkasan pesanan -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs">
        <div class="p-5 sm:p-6 border-b border-[#E4E7EC]">
            <h3 class="text-sm font-bold text-[#101828] m-0">Ringkasan Pesanan</h3>
        </div>
        <div class="p-5 sm:p-6">
            <dl class="divide-y divide-[#E4E7EC]">
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-sm text-[#667085]">Cabang</dt>
                    <dd class="text-sm font-medium text-[#101828]">{{ $branch->display_name }}</dd>
                </div>
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-sm text-[#667085]">Layanan</dt>
                    <dd class="text-sm font-medium text-[#101828]">{{ $service->name }}</dd>
                </div>
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-sm text-[#667085]">Kategori</dt>
                    <dd class="text-sm font-medium text-[#101828]">{{ $service->category?->name ?? 'Umum' }}</dd>
                </div>

                @if ($isWeightBased)
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-sm text-[#667085]">Berat Cucian</dt>
                    <dd class="text-sm font-medium text-[#101828] text-right">Ditimbang oleh petugas saat diterima</dd>
                </div>
                @if (!empty($data['estimated_quantity']))
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-sm text-[#667085]">Perkiraan Barang/Kantong</dt>
                    <dd class="text-sm font-medium text-[#101828]">
                        {{ (float) $data['estimated_quantity'] }} kantong/item
                    </dd>
                </div>
                @endif
                @endif

                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-sm text-[#667085]">Antar-Jemput</dt>
                    <dd class="text-sm font-medium text-[#101828]">
                        {{ !empty($data['pickup_option']) ? 'Ya (Dijemput)' : 'Tidak (Drop-off di cabang)' }}
                    </dd>
                </div>
                @if (!empty($data['notes']))
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-sm text-[#667085]">Catatan</dt>
                    <dd class="text-sm text-[#101828] text-right">{{ $data['notes'] }}</dd>
                </div>
                @endif
                <div class="py-3 flex justify-between gap-4">
                    <dt class="text-sm text-[#667085]">Estimasi Waktu Selesai</dt>
                    <dd class="text-sm font-medium text-[#101828]">{{ \App\Models\Service::formatEstimatedDuration($estimatedDuration) }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Pricing Card -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5 sm:p-6">
        <h3 class="text-sm font-bold text-[#101828] mb-4">Informasi Tarif & Harga</h3>
        <div class="space-y-2.5">
            @if ($isWeightBased)
                <div class="flex justify-between text-sm">
                    <span class="text-[#667085]">Tarif Layanan</span>
                    <span class="font-medium text-[#101828]">Rp {{ number_format($service->price, 0, ',', '.') }} / kg</span>
                </div>

                <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-xs text-amber-900 leading-relaxed">
                    Harga awal merupakan tarif per kilogram. Total akhir pembayaran ditentukan setelah penimbangan oleh petugas saat barang diterima.
                </div>

                @if ($promoResult && $promoResult['valid'])
                    <div class="flex justify-between text-sm text-emerald-700">
                        <span>Diskon Promo ({{ $data['promo_code'] }})</span>
                        <span class="font-medium">- Rp {{ number_format($promoResult['discount'], 0, ',', '.') }}</span>
                    </div>
                @elseif ($promoResult && !$promoResult['valid'])
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-700">
                        {{ $promoResult['message'] }}
                    </div>
                @endif

                <div class="flex justify-between text-base pt-2 border-t border-[#E4E7EC]">
                    <span class="font-bold text-[#101828]">Estimasi Total</span>
                    <span class="font-bold text-[#062072]">Menunggu Penimbangan</span>
                </div>
                <p class="text-[11px] text-[#667085] mt-2 m-0">* Total akhir disesuaikan dengan berat aktual hasil penimbangan petugas.</p>
            @else
                <div class="flex justify-between text-sm">
                    <span class="text-[#667085]">Harga Layanan</span>
                    <span class="font-medium text-[#101828]">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-[#667085]">Subtotal</span>
                    <span class="font-medium text-[#101828]">Rp {{ number_format($estimatedPrice, 0, ',', '.') }}</span>
                </div>

                @if ($promoResult && $promoResult['valid'])
                    <div class="flex justify-between text-sm text-emerald-700">
                        <span>Diskon Promo ({{ $data['promo_code'] }})</span>
                        <span class="font-medium">- Rp {{ number_format($promoResult['discount'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-base pt-2 border-t border-[#E4E7EC]">
                        <span class="font-bold text-[#101828]">Total Pembayaran</span>
                        <span class="font-bold text-[#062072]">Rp {{ number_format($promoResult['final_total'], 0, ',', '.') }}</span>
                    </div>
                @elseif ($promoResult && !$promoResult['valid'])
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-700">
                        {{ $promoResult['message'] }}
                    </div>
                    <div class="flex justify-between text-base pt-2 border-t border-[#E4E7EC]">
                        <span class="font-bold text-[#101828]">Total Pembayaran</span>
                        <span class="font-bold text-[#062072]">Rp {{ number_format($estimatedPrice, 0, ',', '.') }}</span>
                    </div>
                @else
                    <div class="flex justify-between text-base pt-2 border-t border-[#E4E7EC]">
                        <span class="font-bold text-[#101828]">Total Pembayaran</span>
                        <span class="font-bold text-[#062072]">Rp {{ number_format($estimatedPrice, 0, ',', '.') }}</span>
                    </div>
                @endif
                <p class="text-[11px] text-[#667085] mt-2 m-0">* Harga tetap sesuai paket layanan yang dipilih.</p>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between gap-4">
        <button type="button" onclick="history.back()"
            class="inline-flex items-center px-4 py-2.5 border border-[#E4E7EC] rounded-lg text-sm font-medium text-[#667085] hover:text-[#101828] hover:bg-[#F5F8FC] transition-colors cursor-pointer bg-transparent">
            Kembali Edit
        </button>

        <form method="POST" action="{{ route('customer.pesanan.store') }}" id="form-confirm-order">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $data['branch_id'] }}">
            <input type="hidden" name="service_id" value="{{ $data['service_id'] }}">
            <input type="hidden" name="estimated_quantity" value="{{ $data['estimated_quantity'] ?? '' }}">
            <input type="hidden" name="pickup_option" value="{{ $data['pickup_option'] ?? 0 }}">
            <input type="hidden" name="notes" value="{{ $data['notes'] ?? '' }}">
            <button type="submit" id="btn-submit-order"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-sm font-semibold transition-colors cursor-pointer shadow-sm border-0"
                onclick="this.disabled=true; this.form.submit();">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Konfirmasi & Kirim Pesanan
            </button>
        </form>
    </div>
</div>
@endsection
