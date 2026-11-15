{{-- Sub-komponen Modal Pembayaran: Identitas Pelanggan, Daftar Item & Fitur Tambah Layanan --}}
<div class="space-y-4">
    {{-- Identitas Ringkas Pelanggan & Transaksi --}}
    <div class="bg-[#F8FAFC] border border-[#E4E7EC] rounded-xl p-3.5 space-y-2.5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            <div>
                <span class="text-[#667085] block text-[11px] font-medium">Pelanggan</span>
                <span id="pay-display-customer" class="font-bold text-[#101828] text-sm block truncate">Pelanggan Umum</span>
                <span id="pay-display-phone" class="text-[11px] text-[#667085] block truncate"></span>
            </div>
            <div>
                <span class="text-[#667085] block text-[11px] font-medium">Tipe Transaksi & Cabang</span>
                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                    <span id="pay-display-type-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#062072] border border-blue-200">
                        Walk-in
                    </span>
                    <span id="pay-display-branch" class="text-xs font-semibold text-[#344054] truncate">-</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Item Layanan Terpilih --}}
    <div class="border border-[#E4E7EC] rounded-xl p-3.5 bg-white space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-[#E4E7EC]">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#101828]">Layanan Dipilih</span>
                <span id="pay-items-count-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#DCE8FF] text-[#062072]">
                    0 item
                </span>
            </div>
            <button type="button" id="btn-toggle-add-service" class="inline-flex items-center gap-1 text-xs font-bold text-[#062072] hover:text-[#0B3A9E] hover:underline cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>+ Tambah Layanan</span>
            </button>
        </div>

        {{-- Panel Tambah Layanan (Dropdown / Live Search dalam Modal) --}}
        <div id="pay-add-service-panel" class="hidden p-3 bg-[#F5F8FC] border border-blue-200 rounded-xl space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-[#062072] flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Pilih Layanan dari Katalog
                </span>
                <button type="button" id="btn-close-add-service" class="text-[11px] text-[#667085] hover:text-rose-600 font-semibold cursor-pointer">
                    Tutup
                </button>
            </div>
            <div class="relative">
                <input type="text" id="pay-service-search-input" placeholder="Ketik nama layanan untuk mencari..." class="w-full px-3 py-1.5 bg-white border border-[#D0D5DD] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
            </div>
            <div id="pay-service-search-results" class="max-h-40 overflow-y-auto divide-y divide-[#E4E7EC] bg-white rounded-lg border border-[#E4E7EC] text-xs">
                {{-- Diisi secara dinamis oleh JavaScript --}}
            </div>
        </div>

        {{-- Loading Indicator saat fetch source data --}}
        <div id="pay-items-loading" class="hidden py-4 text-center text-xs text-[#667085]">
            <svg class="animate-spin w-4 h-4 text-[#062072] inline-block mr-1" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memuat rincian item layanan...
        </div>

        {{-- List Item Keranjang --}}
        <div id="pay-items-list" class="space-y-2 max-h-48 overflow-y-auto divide-y divide-[#E4E7EC]/70 pr-1 text-xs">
            {{-- Diisi secara dinamis oleh JS --}}
        </div>

        {{-- Empty item placeholder --}}
        <div id="pay-items-empty" class="hidden py-6 text-center text-xs text-[#667085]">
            Belum ada layanan yang dipilih. Klik tombol <strong>+ Tambah Layanan</strong> di atas untuk memilih layanan.
        </div>
    </div>
</div>

