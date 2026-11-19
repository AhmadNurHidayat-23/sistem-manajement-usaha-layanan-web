@php
    $branches = $branches ?? \App\Models\Branch::where('is_active', true)->whereIn('service_mode', ['drop_off', 'both'])->orderBy('id')->get();
    $activeBranchId = old('branch_id', $selectedBranchId ?? request('branch_id'));
    
    $servicesList = $services ?? collect();
    if ($servicesList->isEmpty() && $activeBranchId) {
        $servicesList = \App\Models\Service::where('is_active', true)->where('branch_id', (int)$activeBranchId)->get();
    } elseif ($servicesList->isEmpty()) {
        $servicesList = \App\Models\Service::where('is_active', true)->whereIn('service_type', ['drop_off', 'both'])->get();
    }

    $filteredServices = $activeBranchId 
        ? $servicesList->filter(fn($s) => (int)$s->branch_id === (int)$activeBranchId && $s->is_active)
        : collect();

    $activeServiceId = old('service_id', $selectedServiceId ?? request('service_id'));
    $currentService = $selectedService ?? ($activeServiceId ? $filteredServices->firstWhere('id', (int) $activeServiceId) : null);
    if ($currentService && (int)$currentService->branch_id !== (int)$activeBranchId) {
        $currentService = null;
        $activeServiceId = null;
    }
    $isWeightService = $currentService ? $currentService->isWeightBased() : false;
    $hasSelectedService = !empty($currentService);
    $isStandalone = $isStandalone ?? false;
    $shouldAutoOpen = ($isOpen ?? false) || $errors->any() || session('error') || ($isStandalone && !empty($activeBranchId));
@endphp

<!-- Modal Buat Pesanan -->
<div id="modal-buat-pesanan"
     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 {{ $shouldAutoOpen ? 'is-open' : 'hidden' }}"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-buat-pesanan-title"
     data-backdrop-close
     data-auto-open="{{ $shouldAutoOpen ? '1' : '0' }}"
     data-standalone="{{ $isStandalone ? '1' : '0' }}">

    <div class="relative w-full max-w-3xl bg-white dark:bg-[#111C35] rounded-2xl shadow-2xl border border-[#E7ECF3] dark:border-[#1E2D4A] max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header Modal (Sticky) -->
        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-[#E7ECF3] dark:border-[#1E2D4A] bg-white dark:bg-[#111C35] shrink-0 sticky top-0 z-20">
            <div>
                <h3 id="modal-buat-pesanan-title" class="text-base sm:text-lg font-bold text-[#101828] dark:text-[#F8FAFC] m-0">
                    Buat Pesanan
                </h3>
                <p class="text-xs text-[#667085] dark:text-[#94A3B8] mt-0.5 m-0">
                    Lengkapi detail pesanan layanan Anda
                </p>
            </div>
            @if ($isStandalone)
                <a href="{{ route('customer.dashboard') . '#pilih-layanan' }}"
                   class="text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white p-2 rounded-xl hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] transition-colors no-underline cursor-pointer border border-transparent hover:border-[#E4E7EC] dark:hover:border-[#1E2D4A]"
                   aria-label="Tutup Modal"
                   title="Tutup (Kembali ke Beranda)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </a>
            @else
                <button type="button"
                        data-modal-close="modal-buat-pesanan"
                        class="text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white p-2 rounded-xl hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] transition-colors cursor-pointer border border-transparent hover:border-[#E4E7EC] dark:hover:border-[#1E2D4A]"
                        aria-label="Tutup Modal"
                        title="Tutup (Esc)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif
        </div>

        <!-- Alert Error (jika ada) -->
        <div id="order-form-error-alert" class="{{ (session('error') || $errors->any()) ? '' : 'hidden' }} mx-5 sm:mx-6 mt-4 p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 text-rose-700 dark:text-rose-400 rounded-lg text-xs flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span id="order-form-error-message">
                @if (session('error'))
                    {{ session('error') }}
                @elseif ($errors->any())
                    {{ $errors->first() }}
                @endif
            </span>
        </div>

        <!-- Body Form Modal (Scrollable) -->
        <div class="overflow-y-auto p-5 sm:p-6 flex-1 overscroll-contain">
            <form method="POST" action="{{ route('customer.pesanan.confirm') }}" class="space-y-5" id="form-order-customer">
                @csrf

                <!-- Cabang -->
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-[#101828] dark:text-[#F8FAFC] mb-1.5">
                        Cabang <span class="text-rose-500">*</span>
                    </label>
                    <select id="branch_id" name="branch_id" required
                        class="w-full h-11 px-3.5 bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-lg text-sm text-[#101828] dark:text-[#F8FAFC] focus:outline-none focus:ring-2 focus:ring-[#062072]">
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
                    <label for="service_id" class="block text-sm font-medium text-[#101828] dark:text-[#F8FAFC] mb-1.5">
                        Layanan <span class="text-rose-500">*</span>
                    </label>
                    <select id="service_id" name="service_id" required
                        class="w-full h-11 px-3.5 bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-lg text-sm text-[#101828] dark:text-[#F8FAFC] focus:outline-none focus:ring-2 focus:ring-[#062072]">
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
                <div id="weight-info-box" class="p-4 bg-amber-50 dark:bg-amber-950/40 rounded-lg border border-amber-200 dark:border-amber-900/50 {{ $isWeightService ? '' : 'hidden' }}">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-amber-900 dark:text-amber-300 m-0">Berat Cucian</h4>
                            <p class="text-xs text-amber-800 dark:text-amber-200 mt-1 m-0">
                                Berat akan ditimbang oleh petugas saat barang diterima. Harga akhir akan disesuaikan dengan berat aktual.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Field Opsional Perkiraan Jumlah Barang/Kantong (Hanya untuk Layanan Berbasis Berat) -->
                <div id="quantity-field-container" class="{{ $isWeightService ? '' : 'hidden' }}">
                    <label for="estimated_quantity" class="block text-sm font-medium text-[#101828] dark:text-[#F8FAFC] mb-1.5">
                        Perkiraan Jumlah Barang/Kantong
                        <span class="text-[#667085] dark:text-[#94A3B8] font-normal text-xs ml-1">(opsional)</span>
                    </label>
                    <input type="number" id="estimated_quantity" name="estimated_quantity"
                        value="{{ old('estimated_quantity', request('estimated_quantity')) }}" min="1" step="1"
                        placeholder="Contoh: 1 atau 2 kantong (opsional)"
                        class="w-full h-11 px-3.5 bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-lg text-sm text-[#101828] dark:text-[#F8FAFC] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <p class="mt-1 text-xs text-[#667085] dark:text-[#94A3B8] m-0">Opsional. Anda dapat mengosongkan field ini jika belum mengetahui jumlahnya.</p>
                    @error('estimated_quantity')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <!-- Pickup Option -->
                <div class="flex items-start gap-3 p-4 bg-[#F5F8FC] dark:bg-[#0E172E] rounded-lg border border-[#E4E7EC] dark:border-[#1E2D4A]">
                    <input type="checkbox" id="pickup_option" name="pickup_option" value="1"
                        {{ (old('pickup_option', request('pickup_option'))) ? 'checked' : '' }}
                        class="w-4 h-4 mt-0.5 text-[#062072] border-[#E4E7EC] dark:border-[#1E2D4A] rounded focus:ring-[#062072] cursor-pointer">
                    <div>
                        <label for="pickup_option" class="text-sm font-medium text-[#101828] dark:text-white cursor-pointer">
                            Layanan Antar-Jemput
                        </label>
                        <p class="text-xs text-[#667085] dark:text-[#94A3B8] mt-0.5 m-0">Kami akan menjemput item pesanan Anda di alamat yang ditentukan</p>
                    </div>
                </div>

                @include('pelanggan.pesanan.partials.form-lokasi-antar-jemput')

                <!-- Promo Code -->
                <div>
                    <label for="promo_code" class="block text-sm font-medium text-[#101828] dark:text-[#F8FAFC] mb-1.5">
                        Kode Promo
                        <span class="text-[#667085] dark:text-[#94A3B8] font-normal text-xs ml-1">(opsional)</span>
                    </label>
                    <input type="text" id="promo_code" name="promo_code"
                        value="{{ old('promo_code', request('promo_code')) }}"
                        placeholder="Masukkan kode promo jika ada"
                        class="w-full h-11 px-3.5 bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-lg text-sm text-[#101828] dark:text-[#F8FAFC] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072] uppercase">
                </div>

                <!-- Catatan -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-[#101828] dark:text-[#F8FAFC] mb-1.5">
                        Catatan
                        <span class="text-[#667085] dark:text-[#94A3B8] font-normal text-xs ml-1">(opsional)</span>
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                        placeholder="Kondisi khusus, instruksi tambahan..."
                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-lg text-sm text-[#101828] dark:text-[#F8FAFC] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072] resize-none">{{ old('notes', request('notes')) }}</textarea>
                    @error('notes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <!-- Live Estimation -->
                <div id="estimation-panel" class="p-4 bg-[#DCE8FF]/30 dark:bg-[#062072]/20 rounded-lg border border-[#062072]/10 dark:border-[#93C5FD]/20 {{ $hasSelectedService ? '' : 'hidden' }}">
                    <h4 class="text-xs font-bold text-[#062072] dark:text-[#93C5FD] uppercase tracking-wider mb-2">Estimasi Pesanan</h4>
                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-[#667085] dark:text-[#94A3B8]" id="est-unit-label">{{ $isWeightService ? 'Harga per kg:' : 'Harga Layanan:' }}</span>
                            <span class="font-medium text-[#101828] dark:text-white" id="est-unit-price">
                                {{ $currentService ? 'Rp ' . number_format($currentService->price, 0, ',', '.') . ($isWeightService ? ' / kg' : '') : '-' }}
                            </span>
                        </div>
                        <div id="est-qty-row" class="flex justify-between {{ ($isWeightService && old('estimated_quantity')) ? '' : 'hidden' }}">
                            <span class="text-[#667085] dark:text-[#94A3B8]">Perkiraan:</span>
                            <span class="font-medium text-[#101828] dark:text-white" id="est-qty">{{ old('estimated_quantity') ? old('estimated_quantity') . ' kantong/item' : '-' }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-[#062072]/10 dark:border-[#93C5FD]/20">
                            <span class="font-semibold text-[#101828] dark:text-white" id="est-total-label">Estimasi Total:</span>
                            <span class="font-bold text-[#062072] dark:text-[#93C5FD]" id="est-total">
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
                        <p id="est-weight-note" class="text-[11px] text-[#667085] dark:text-[#94A3B8] mt-1 m-0 {{ $isWeightService ? '' : 'hidden' }}">
                            * Harga awal merupakan harga per kg. Total akhir ditentukan setelah penimbangan oleh petugas saat barang diterima.
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-[#E4E7EC] dark:border-[#1E2D4A] flex justify-end gap-3">
                    @if ($isStandalone)
                        <a href="{{ route('customer.dashboard') . '#pilih-layanan' }}"
                            class="inline-flex items-center px-4 py-2.5 border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-lg text-sm font-medium text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] transition-colors no-underline">
                            Batal
                        </a>
                    @else
                        <button type="button"
                            data-modal-close="modal-buat-pesanan"
                            class="inline-flex items-center px-4 py-2.5 border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-lg text-sm font-medium text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] transition-colors cursor-pointer bg-transparent">
                            Batal
                        </button>
                    @endif
                    <button type="submit" id="btn-konfirmasi-order"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-sm font-semibold transition-colors cursor-pointer shadow-sm border-0 disabled:opacity-60 disabled:cursor-not-allowed">
                        <span>Lanjut ke Konfirmasi</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
