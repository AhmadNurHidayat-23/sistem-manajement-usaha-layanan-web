@if (in_array($order->status->value, ['ready', 'completed']))
<div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5 sm:p-6" id="delivery-method-card">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-lg bg-blue-50 border border-blue-200 text-[#062072] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-[#101828] m-0">Pilihan Penyerahan Barang</h3>
                <p class="text-xs text-[#667085] m-0 mt-0.5">Pesanan telah selesai dikerjakan. Tentukan cara Anda menerima barang.</p>
            </div>
        </div>

        @if ($order->delivery_method)
            <button type="button"
                id="btn-toggle-delivery-form"
                class="text-xs font-semibold text-[#062072] hover:text-[#0B3A9E] hover:underline cursor-pointer bg-transparent border-0 p-0">
                Ubah Pilihan
            </button>
        @endif
    </div>

    <!-- Tampilan Status Pilihan yang Sudah Disimpan -->
    @if ($order->delivery_method)
        <div id="delivery-current-selection" class="space-y-4">
            @if ($order->delivery_method === 'store_pickup')
                <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-[#062072] flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-[#062072] uppercase tracking-wider block">Ambil Sendiri di Toko</span>
                            <p class="text-xs text-[#344054] m-0 mt-0.5">
                                Silakan datang ke cabang <strong>{{ $order->branch?->name ?? 'SIMAUL' }}</strong> untuk mengambil pesanan Anda.
                            </p>
                            <p class="text-[11px] text-[#667085] m-0 mt-1">
                                Pembayaran dapat dilakukan tunai/QRIS di kasir cabang saat pengambilan atau bayar online sekarang.
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0">
                        @if ($order->isPaid())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Lunas (Siap Diambil)
                            </span>
                        @else
                            <a href="{{ route('customer.pesanan.pay-online', $order) }}"
                                class="inline-flex items-center justify-center px-3.5 py-2 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors no-underline">
                                Bayar Online Sekarang
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-indigo-900 uppercase tracking-wider block">Diantar ke Alamat</span>
                            <p class="text-xs text-[#344054] m-0 mt-0.5">
                                Pesanan akan diantarkan kurir ke alamat Anda: <em>{{ $order->pickup_address ?? 'Alamat terdaftar' }}</em>.
                            </p>
                            @if (! $order->isPaid())
                                <p class="text-[11px] font-medium text-amber-700 m-0 mt-1">
                                    Peringatan: Pesanan antar ke alamat WAJIB dibayar online sebelum pengantaran (tidak melayani COD kurir).
                                </p>
                            @else
                                <p class="text-[11px] font-medium text-emerald-700 m-0 mt-1">
                                    Pembayaran telah diterima lunas. Kurir segera menjadwalkan pengantaran.
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="shrink-0">
                        @if ($order->isPaid())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Lunas (Siap Diantar)
                            </span>
                        @else
                            <a href="{{ route('customer.pesanan.pay-online', $order) }}"
                                class="inline-flex items-center justify-center px-3.5 py-2 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors no-underline">
                                Bayar Online Sekarang
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Form Pemilihan / Pengubahan Metode Penyerahan -->
    <form id="form-delivery-method"
          action="{{ route('customer.pesanan.delivery-method', $order) }}"
          method="POST"
          class="{{ $order->delivery_method ? 'hidden' : '' }} mt-4 space-y-4">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <!-- Pilihan 1: Ambil di Toko -->
            <label class="delivery-option-label relative flex flex-col p-4 rounded-xl border-2 transition-all cursor-pointer {{ ($order->delivery_method === 'store_pickup' || ! $order->delivery_method) ? 'border-[#062072] bg-blue-50/40' : 'border-[#E4E7EC] hover:border-[#D0D5DD]' }}">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <input type="radio" name="delivery_method" value="store_pickup" {{ ($order->delivery_method === 'store_pickup' || ! $order->delivery_method) ? 'checked' : '' }} class="h-4 w-4 text-[#062072] focus:ring-[#062072]">
                        <span class="text-xs font-bold text-[#101828]">Ambil di Toko</span>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-[#062072]">
                        Bebas Ongkir
                    </span>
                </div>
                <p class="text-xs text-[#475467] m-0 mb-2">
                    Ambil langsung di kasir toko. Bisa bayar tunai/QRIS di kasir saat mengambil atau bayar online lebih awal.
                </p>
                <div class="mt-auto text-[11px] text-[#667085] flex items-center gap-1 font-medium">
                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Bisa bayar di kasir / bayar online
                </div>
            </label>

            <!-- Pilihan 2: Diantar ke Alamat -->
            <label class="delivery-option-label relative flex flex-col p-4 rounded-xl border-2 transition-all cursor-pointer {{ ($order->delivery_method === 'address_delivery') ? 'border-[#062072] bg-blue-50/40' : 'border-[#E4E7EC] hover:border-[#D0D5DD]' }}">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <input type="radio" name="delivery_method" value="address_delivery" {{ ($order->delivery_method === 'address_delivery') ? 'checked' : '' }} class="h-4 w-4 text-[#062072] focus:ring-[#062072]">
                        <span class="text-xs font-bold text-[#101828]">Diantar ke Alamat</span>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-700">
                        Kurir SIMAUL
                    </span>
                </div>
                <p class="text-xs text-[#475467] m-0 mb-2">
                    Kurir mengantarkan barang langsung ke alamat Anda. Wajib bayar online sebelum barang diantar (tidak melayani COD).
                </p>
                <div class="mt-auto text-[11px] text-amber-700 flex items-center gap-1 font-medium">
                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Wajib lunas online sebelum diantar
                </div>
            </label>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-2">
            @if ($order->delivery_method)
                <button type="button" id="btn-cancel-delivery-edit" class="px-4 py-2 text-xs font-semibold text-[#344054] bg-white border border-[#D0D5DD] rounded-lg hover:bg-[#F9FAFB] transition-colors cursor-pointer">
                    Batal
                </button>
            @endif
            <button type="submit" id="btn-save-delivery" class="inline-flex items-center justify-center px-4 py-2 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors cursor-pointer">
                Simpan Pilihan Penyerahan
            </button>
        </div>
    </form>
</div>
@endif

