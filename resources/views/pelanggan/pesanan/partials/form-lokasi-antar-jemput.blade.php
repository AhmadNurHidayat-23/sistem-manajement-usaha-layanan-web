{{-- Formulir Lokasi Antar-Jemput (Muncul saat Layanan Antar-Jemput aktif) --}}
<div id="pickup-location-section" class="space-y-4 p-4 sm:p-5 bg-[#F8FAFC] dark:bg-[#0E172E]/70 rounded-xl border border-[#E4E7EC] dark:border-[#1E2D4A] {{ (old('pickup_option', request('pickup_option'))) ? '' : 'hidden' }}">
    <h4 class="text-sm font-bold text-[#101828] dark:text-white m-0">Lokasi Penjemputan & Pengantaran</h4>
    
    {{-- Alamat Penjemputan --}}
    <div>
        <div class="flex items-center justify-between gap-2 mb-1.5">
            <label for="pickup_address" class="block text-xs font-semibold uppercase tracking-wider text-[#344054] dark:text-[#CBD5E1]">
                Alamat Penjemputan <span class="text-rose-500">*</span>
            </label>
            <span id="pickup-address-status" class="hidden text-xs font-medium"></span>
        </div>
        <textarea id="pickup_address" name="pickup_address" rows="2"
            placeholder="Masukkan alamat lengkap penjemputan (jalan, nomor, RT/RW)..."
            class="w-full px-3.5 py-2.5 bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-lg text-sm text-[#101828] dark:text-[#F8FAFC] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072] resize-none">{{ old('pickup_address', request('pickup_address', auth()->user()->address ?? auth()->user()->orders()->whereNotNull('pickup_address')->latest()->value('pickup_address') ?? '')) }}</textarea>
        @error('pickup_address')<p id="error-pickup-address" class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    {{-- Titik Peta Penjemputan --}}
    <div>
        <div class="flex items-center justify-between gap-2 mb-1.5">
            <span class="block text-xs font-semibold uppercase tracking-wider text-[#344054] dark:text-[#CBD5E1]">
                Titik Lokasi Penjemputan
            </span>
            <button type="button" id="btn-gps-pickup"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md bg-[#062072]/10 dark:bg-[#93C5FD]/10 text-[#062072] dark:text-[#93C5FD] hover:bg-[#062072]/20 transition-colors cursor-pointer border-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Gunakan Lokasi Saya
            </button>
        </div>
        <p class="text-xs text-[#667085] dark:text-[#94A3B8] mb-2 m-0">Geser pin atau klik pada peta untuk menentukan titik jemput yang akurat.</p>
        <div id="pickup-map" class="w-full h-52 sm:h-56 rounded-lg border border-[#E4E7EC] dark:border-[#1E2D4A] overflow-hidden bg-neutral-100 z-0"></div>
        <input type="hidden" id="pickup_latitude" name="pickup_latitude" value="{{ old('pickup_latitude', request('pickup_latitude', '-6.2088')) }}">
        <input type="hidden" id="pickup_longitude" name="pickup_longitude" value="{{ old('pickup_longitude', request('pickup_longitude', '106.8456')) }}">
        <div id="pickup-coords-label" class="mt-1 text-[11px] text-[#667085] dark:text-[#94A3B8]">
            Koordinat: <span id="text-pickup-coords">{{ old('pickup_latitude', request('pickup_latitude', '-6.2088')) }}, {{ old('pickup_longitude', request('pickup_longitude', '106.8456')) }}</span>
        </div>
    </div>

    {{-- Checkbox Lokasi Pengantaran Berbeda --}}
    <div class="pt-2 border-t border-[#E4E7EC] dark:border-[#1E2D4A]">
        <div class="flex items-start gap-2.5">
            <input type="checkbox" id="different_delivery_location" name="different_delivery_location" value="1"
                {{ (old('different_delivery_location', request('different_delivery_location'))) ? 'checked' : '' }}
                class="w-4 h-4 mt-0.5 text-[#062072] border-[#E4E7EC] rounded focus:ring-[#062072] cursor-pointer">
            <label for="different_delivery_location" class="text-xs font-semibold text-[#344054] dark:text-[#CBD5E1] cursor-pointer">
                Lokasi pengantaran berbeda dari penjemputan
            </label>
        </div>
    </div>

    {{-- Container Lokasi Pengantaran (Jika Berbeda) --}}
    <div id="delivery-location-container" class="space-y-4 pt-2 {{ (old('different_delivery_location', request('different_delivery_location'))) ? '' : 'hidden' }}">
        <div>
            <div class="flex items-center justify-between gap-2 mb-1.5">
                <label for="delivery_address" class="block text-xs font-semibold uppercase tracking-wider text-[#344054] dark:text-[#CBD5E1]">
                    Alamat Pengantaran <span class="text-rose-500">*</span>
                </label>
                <span id="delivery-address-status" class="hidden text-xs font-medium"></span>
            </div>
            <textarea id="delivery_address" name="delivery_address" rows="2"
                placeholder="Masukkan alamat lengkap pengantaran..."
                class="w-full px-3.5 py-2.5 bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-lg text-sm text-[#101828] dark:text-[#F8FAFC] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072] resize-none">{{ old('delivery_address', request('delivery_address')) }}</textarea>
            @error('delivery_address')<p id="error-delivery-address" class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="flex items-center justify-between gap-2 mb-1.5">
                <span class="block text-xs font-semibold uppercase tracking-wider text-[#344054] dark:text-[#CBD5E1]">
                    Titik Lokasi Pengantaran
                </span>
                <button type="button" id="btn-gps-delivery"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md bg-[#062072]/10 dark:bg-[#93C5FD]/10 text-[#062072] dark:text-[#93C5FD] hover:bg-[#062072]/20 transition-colors cursor-pointer border-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Gunakan Lokasi Saya
                </button>
            </div>
            <div id="delivery-map" class="w-full h-52 sm:h-56 rounded-lg border border-[#E4E7EC] dark:border-[#1E2D4A] overflow-hidden bg-neutral-100 z-0"></div>
            <input type="hidden" id="delivery_latitude" name="delivery_latitude" value="{{ old('delivery_latitude', request('delivery_latitude', '-6.2088')) }}">
            <input type="hidden" id="delivery_longitude" name="delivery_longitude" value="{{ old('delivery_longitude', request('delivery_longitude', '106.8456')) }}">
            <div id="delivery-coords-label" class="mt-1 text-[11px] text-[#667085] dark:text-[#94A3B8]">
                Koordinat: <span id="text-delivery-coords">{{ old('delivery_latitude', request('delivery_latitude', '-6.2088')) }}, {{ old('delivery_longitude', request('delivery_longitude', '106.8456')) }}</span>
            </div>
        </div>
    </div>

    {{-- Catatan Lokasi --}}
    <div>
        <label for="location_notes" class="block text-xs font-semibold uppercase tracking-wider text-[#344054] dark:text-[#CBD5E1] mb-1.5">
            Catatan Lokasi / Patokan Bangunan <span class="text-[#667085] font-normal lowercase">(opsional)</span>
        </label>
        <input type="text" id="location_notes" name="location_notes"
            value="{{ old('location_notes', request('location_notes')) }}"
            placeholder="Contoh: Pagar hitam, samping masjid, blok B3 No. 12"
            class="w-full h-10 px-3.5 bg-white dark:bg-[#111C35] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-lg text-sm text-[#101828] dark:text-[#F8FAFC] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072]">
    </div>
</div>
