@extends('layouts.pelanggan', ['title' => 'Buat Pesanan'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Buat Pesanan</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Lengkapi detail pesanan layanan Anda.</p>
        </div>
        <a href="{{ route('customer.home') }}"
            class="inline-flex items-center px-3 py-1.5 border border-[#E4E7EC] rounded-lg text-xs font-medium text-[#667085] hover:text-[#101828] hover:bg-[#F5F8FC] transition-colors no-underline">
            Kembali
        </a>
    </div>

    <!-- Alert -->
    @if (session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @php
        $activeBranchId = old('branch_id', $selectedBranchId);
        $filteredServices = $activeBranchId 
            ? $services->filter(fn($s) => (int)$s->branch_id === (int)$activeBranchId && $s->is_active)
            : collect();

        $activeServiceId = old('service_id', $selectedServiceId);
        $currentService = $selectedService ?? ($activeServiceId ? $filteredServices->firstWhere('id', (int) $activeServiceId) : null);
        if ($currentService && (int)$currentService->branch_id !== (int)$activeBranchId) {
            $currentService = null;
            $activeServiceId = null;
        }
        $isWeightService = $currentService ? $currentService->isWeightBased() : false;
        $hasSelectedService = !empty($currentService);
    @endphp

    <!-- Order Form -->
    <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs">
        <div class="p-5 sm:p-6 border-b border-[#E4E7EC]">
            <h3 class="text-sm font-bold text-[#101828] m-0">Detail Pesanan</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Pilih layanan dan lengkapi rincian pesanan Anda</p>
        </div>

        <form method="POST" action="{{ route('customer.pesanan.confirm') }}" class="p-5 sm:p-6 space-y-5" id="form-order-customer">
            @csrf

            <!-- Cabang -->
            <div>
                <label for="branch_id" class="block text-sm font-medium text-[#101828] mb-1.5">
                    Cabang <span class="text-rose-500">*</span>
                </label>
                <select id="branch_id" name="branch_id" required
                    class="w-full h-11 px-3.5 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">— Pilih Cabang —</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ ($activeBranchId == $branch->id) ? 'selected' : '' }}>
                            {{ $branch->display_name }}
                            @if ($branch->address) — {{ $branch->address }} @endif
                        </option>
                    @endforeach
                </select>
                @error('branch_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <!-- Layanan -->
            <div>
                <label for="service_id" class="block text-sm font-medium text-[#101828] mb-1.5">
                    Layanan <span class="text-rose-500">*</span>
                </label>
                <select id="service_id" name="service_id" required
                    class="w-full h-11 px-3.5 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="" data-branch-id="" data-price="0" data-is-weight="0" data-unit="" data-duration="0">— {{ $activeBranchId ? 'Pilih Layanan' : 'Pilih Cabang Terlebih Dahulu' }} —</option>
                    @foreach($filteredServices as $service)
                        <option value="{{ $service->id }}"
                            data-branch-id="{{ $service->branch_id ?? '' }}"
                            data-price="{{ $service->price }}"
                            data-is-weight="{{ $service->isWeightBased() ? '1' : '0' }}"
                            data-unit="{{ $service->unit ?? '' }}"
                            data-duration="{{ $service->estimated_duration ?? 0 }}"
                            data-duration-formatted="{{ $service->formatted_duration }}"
                            {{ ($currentService && $currentService->id == $service->id) ? 'selected' : '' }}>
                            {{ $service->name }} — Rp{{ number_format($service->price, 0, ',', '.') }}{{ $service->isWeightBased() ? ' / kg' : '' }} — {{ $service->formatted_duration }}
                        </option>
                    @endforeach
                </select>
                @error('service_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <!-- Info Berat Cucian untuk Layanan Berbasis Berat -->
            <div id="weight-info-box" class="p-4 bg-amber-50 rounded-lg border border-amber-200 {{ $isWeightService ? '' : 'hidden' }}">
                <div class="flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-amber-900 m-0">Berat Cucian</h4>
                        <p class="text-xs text-amber-800 mt-1 m-0">
                            Berat akan ditimbang oleh petugas saat barang diterima. Harga akhir akan disesuaikan dengan berat aktual.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Field Opsional Perkiraan Jumlah Barang/Kantong (Hanya untuk Layanan Berbasis Berat) -->
            <div id="quantity-field-container" class="{{ $isWeightService ? '' : 'hidden' }}">
                <label for="estimated_quantity" class="block text-sm font-medium text-[#101828] mb-1.5">
                    Perkiraan Jumlah Barang/Kantong
                    <span class="text-[#667085] font-normal text-xs ml-1">(opsional)</span>
                </label>
                <input type="number" id="estimated_quantity" name="estimated_quantity"
                    value="{{ old('estimated_quantity') }}" min="1" step="1"
                    placeholder="Contoh: 1 atau 2 kantong (opsional)"
                    class="w-full h-11 px-3.5 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                <p class="mt-1 text-xs text-[#667085] m-0">Opsional. Anda dapat mengosongkan field ini jika belum mengetahui jumlahnya.</p>
                @error('estimated_quantity')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <!-- Pickup Option -->
            <div class="flex items-start gap-3 p-4 bg-[#F5F8FC] rounded-lg border border-[#E4E7EC]">
                <input type="checkbox" id="pickup_option" name="pickup_option" value="1"
                    {{ old('pickup_option') ? 'checked' : '' }}
                    class="w-4 h-4 mt-0.5 text-[#062072] border-[#E4E7EC] rounded focus:ring-[#062072]">
                <div>
                    <label for="pickup_option" class="text-sm font-medium text-[#101828] cursor-pointer">
                        Layanan Antar-Jemput
                    </label>
                    <p class="text-xs text-[#667085] mt-0.5 m-0">Kami akan menjemput item pesanan Anda di alamat yang terdaftar</p>
                </div>
            </div>

            <!-- Promo Code -->
            <div>
                <label for="promo_code" class="block text-sm font-medium text-[#101828] mb-1.5">
                    Kode Promo
                    <span class="text-[#667085] font-normal text-xs ml-1">(opsional)</span>
                </label>
                <input type="text" id="promo_code" name="promo_code"
                    value="{{ old('promo_code') }}"
                    placeholder="Masukkan kode promo jika ada"
                    class="w-full h-11 px-3.5 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072] uppercase">
            </div>

            <!-- Catatan -->
            <div>
                <label for="notes" class="block text-sm font-medium text-[#101828] mb-1.5">
                    Catatan
                    <span class="text-[#667085] font-normal text-xs ml-1">(opsional)</span>
                </label>
                <textarea id="notes" name="notes" rows="3"
                    placeholder="Kondisi khusus, instruksi tambahan..."
                    class="w-full px-3.5 py-2.5 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072] resize-none">{{ old('notes') }}</textarea>
                @error('notes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <!-- Live Estimation -->
            <div id="estimation-panel" class="p-4 bg-[#DCE8FF]/30 rounded-lg border border-[#062072]/10 {{ $hasSelectedService ? '' : 'hidden' }}">
                <h4 class="text-xs font-bold text-[#062072] uppercase tracking-wider mb-2">Estimasi Pesanan</h4>
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-[#667085]" id="est-unit-label">{{ $isWeightService ? 'Harga per kg:' : 'Harga Layanan:' }}</span>
                        <span class="font-medium text-[#101828]" id="est-unit-price">
                            {{ $currentService ? 'Rp ' . number_format($currentService->price, 0, ',', '.') . ($isWeightService ? ' / kg' : '') : '-' }}
                        </span>
                    </div>
                    <div id="est-qty-row" class="flex justify-between {{ ($isWeightService && old('estimated_quantity')) ? '' : 'hidden' }}">
                        <span class="text-[#667085]">Perkiraan:</span>
                        <span class="font-medium text-[#101828]" id="est-qty">{{ old('estimated_quantity') ? old('estimated_quantity') . ' kantong/item' : '-' }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-[#062072]/10">
                        <span class="font-semibold text-[#101828]" id="est-total-label">Estimasi Total:</span>
                        <span class="font-bold text-[#062072]" id="est-total">
                            @if ($hasSelectedService)
                                @if ($isWeightService)
                                    Menunggu Penimbangan
                                @else
                                    Rp {{ number_format($currentService->price, 0, ',', '.') }}
                                @endif
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    <p id="est-weight-note" class="text-[11px] text-[#667085] mt-1 m-0 {{ $isWeightService ? '' : 'hidden' }}">
                        * Harga awal merupakan harga per kg. Total akhir ditentukan setelah penimbangan oleh petugas saat barang diterima.
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-4 border-t border-[#E4E7EC] flex justify-end gap-3">
                <a href="{{ route('customer.home') }}"
                    class="inline-flex items-center px-4 py-2.5 border border-[#E4E7EC] rounded-lg text-sm font-medium text-[#667085] hover:text-[#101828] hover:bg-[#F5F8FC] transition-colors no-underline">
                    Batal
                </a>
                <button type="submit" id="btn-konfirmasi-order"
                    class="inline-flex items-center px-5 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-sm font-semibold transition-colors cursor-pointer shadow-sm border-0">
                    Lihat Estimasi & Konfirmasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
