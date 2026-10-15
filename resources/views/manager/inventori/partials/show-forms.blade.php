<!-- Form catat stok masuk dan keluar -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Form stok masuk -->
    <x-cards.card class="p-5 border-emerald-200">
        <div class="flex items-center gap-2 pb-3 mb-3 border-b border-[#E4E7EC]">
            <div class="p-2 rounded-lg bg-emerald-100 text-emerald-700">
                <x-icons.icon name="arrow-down" class="w-4 h-4" />
            </div>
            <div>
                <h4 class="text-sm font-bold text-[#101828] m-0">Catat Stok Masuk (Restock)</h4>
                <p class="text-[11px] text-[#667085] m-0">Penambahan persediaan bahan dari supplier / pembelian.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('inventori.stock-in', $item) }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="in_quantity" class="block text-xs font-semibold text-[#344054] mb-1">
                        Jumlah Masuk ({{ $item->unit }}) <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="quantity"
                        id="in_quantity"
                        required
                        placeholder="0.00"
                        class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600"
                    >
                </div>

                <div>
                    <label for="in_reference" class="block text-xs font-semibold text-[#344054] mb-1">
                        Kategori Referensi
                    </label>
                    <select
                        name="reference_type"
                        id="in_reference"
                        class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600"
                    >
                        <option value="restock">Pembelian / Restock</option>
                        <option value="transfer_in">Transfer Masuk dari Cabang Lain</option>
                        <option value="return_customer">Retur / Pengembalian</option>
                        <option value="correction">Koreksi Penambahan</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="in_notes" class="block text-xs font-semibold text-[#344054] mb-1">
                    Catatan / No. Faktur
                </label>
                <input
                    type="text"
                    name="notes"
                    id="in_notes"
                    placeholder="Contoh: Faktur Toko Sejahtera #PO-1049"
                    class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600"
                >
            </div>

            <div class="text-right pt-2">
                <button
                    type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-4 py-2 rounded-lg transition-colors shadow-xs"
                >
                    Simpan Stok Masuk
                </button>
            </div>
        </form>
    </x-cards.card>

    <!-- Form stok keluar -->
    <x-cards.card class="p-5 border-rose-200">
        <div class="flex items-center gap-2 pb-3 mb-3 border-b border-[#E4E7EC]">
            <div class="p-2 rounded-lg bg-rose-100 text-rose-700">
                <x-icons.icon name="arrow-up" class="w-4 h-4" />
            </div>
            <div>
                <h4 class="text-sm font-bold text-[#101828] m-0">Catat Stok Keluar (Pemakaian)</h4>
                <p class="text-[11px] text-[#667085] m-0">Pengurangan persediaan untuk layanan servis, rusak, atau afkir.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('inventori.stock-out', $item) }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="out_quantity" class="block text-xs font-semibold text-[#344054] mb-1">
                        Jumlah Keluar ({{ $item->unit }}) <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        max="{{ $item->current_stock }}"
                        name="quantity"
                        id="out_quantity"
                        required
                        placeholder="0.00"
                        class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-rose-600 focus:ring-1 focus:ring-rose-600"
                    >
                </div>

                <div>
                    <label for="out_reference" class="block text-xs font-semibold text-[#344054] mb-1">
                        Kategori Referensi
                    </label>
                    <select
                        name="reference_type"
                        id="out_reference"
                        class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-rose-600 focus:ring-1 focus:ring-rose-600"
                    >
                        <option value="service_usage">Pemakaian Layanan Servis</option>
                        <option value="damaged">Rusak / Bocor / Tumpah</option>
                        <option value="expired">Kadaluarsa / Rusak</option>
                        <option value="transfer_out">Transfer Keluar ke Cabang Lain</option>
                        <option value="correction">Koreksi Pengurangan</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="out_notes" class="block text-xs font-semibold text-[#344054] mb-1">
                    Catatan Penggunaan
                </label>
                <input
                    type="text"
                    name="notes"
                    id="out_notes"
                    placeholder="Contoh: Digunakan teknisi untuk paket perawatan premium"
                    class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-rose-600 focus:ring-1 focus:ring-rose-600"
                >
            </div>

            <div class="text-right pt-2">
                <button
                    type="submit"
                    class="bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs px-4 py-2 rounded-lg transition-colors shadow-xs"
                >
                    Simpan Stok Keluar
                </button>
            </div>
        </form>
    </x-cards.card>
</div>
