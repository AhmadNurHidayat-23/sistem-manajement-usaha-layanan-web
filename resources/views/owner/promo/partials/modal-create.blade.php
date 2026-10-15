<!-- Modal Tambah Promo / Voucher Baru -->
<div id="modal-create-promo" class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? '' : 'hidden' }} overflow-y-auto bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="modal-create-promo-title" data-backdrop-close>
    <div class="relative w-full max-w-3xl bg-white rounded-2xl shadow-2xl border border-[#E7ECF3] overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7ECF3] bg-white sticky top-0 z-10">
            <div>
                <h3 id="modal-create-promo-title" class="text-base sm:text-lg font-bold text-[#101828] m-0">Tambah Promo / Voucher Baru</h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Buat kupon diskon baru untuk transaksi kasir dan booking pelanggan.</p>
            </div>
            <button type="button" data-modal-close="modal-create-promo" class="text-[#667085] hover:text-[#101828] p-1.5 rounded-lg hover:bg-[#F5F8FC] transition-colors cursor-pointer" aria-label="Tutup Modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Form -->
        <form method="POST" action="{{ route('promotions.store') }}" class="flex flex-col flex-1 overflow-hidden">
            @csrf

            <div class="p-6 space-y-4 overflow-y-auto flex-1">
                @if ($errors->any() && !old('_method'))
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-700">
                        <p class="font-bold mb-1">Periksa kembali isian formulir:</p>
                        <ul class="list-disc list-inside space-y-0.5 m-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Kode Promo -->
                    <div>
                        <label for="create_code" class="block text-xs font-semibold text-[#101828] mb-1">
                            Kode Promo / Voucher <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="create_code" name="code" value="{{ old('code') }}" required placeholder="Contoh: HEMAT20" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] font-semibold uppercase placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                        <p class="text-[11px] text-[#667085] mt-1 m-0">Kode unik saat checkout.</p>
                        @error('code')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Promo -->
                    <div>
                        <label for="create_name" class="block text-xs font-semibold text-[#101828] mb-1">
                            Nama / Judul Promo <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="create_name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Diskon Pelanggan Baru" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                        <p class="text-[11px] text-[#667085] mt-1 m-0">Deskripsi singkat penawaran diskon.</p>
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Cabang Berlaku -->
                <div>
                    <label for="create_branch_id" class="block text-xs font-semibold text-[#101828] mb-1">
                        Cakupan Cabang
                    </label>
                    <select id="create_branch_id" name="branch_id" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                        @if (auth()->user()?->role?->name === 'owner')
                            <option value="">Semua Cabang (Global)</option>
                        @endif
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->display_name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-[#667085] mt-1 m-0">Pilih cabang spesifik atau biarkan berlaku seluruh cabang.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Tipe Diskon -->
                    <div>
                        <label for="create_discount_type" class="block text-xs font-semibold text-[#101828] mb-1">
                            Jenis Diskon <span class="text-rose-500">*</span>
                        </label>
                        <select id="create_discount_type" name="discount_type" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                            <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                            <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                        </select>
                    </div>

                    <!-- Nilai Diskon -->
                    <div>
                        <label for="create_discount_value" class="block text-xs font-semibold text-[#101828] mb-1">
                            Nilai Diskon <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" id="create_discount_value" name="discount_value" value="{{ old('discount_value', 10) }}" step="0.01" min="0" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                        <p class="text-[11px] text-[#667085] mt-1 m-0">Persen: 1-100, Nominal: jumlah rupiah.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Kuota -->
                    <div>
                        <label for="create_quota" class="block text-xs font-semibold text-[#101828] mb-1">
                            Kuota Penggunaan
                        </label>
                        <input type="number" id="create_quota" name="quota" value="{{ old('quota') }}" min="1" placeholder="Unlimited" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>

                    <!-- Tanggal Mulai -->
                    <div>
                        <label for="create_start_date" class="block text-xs font-semibold text-[#101828] mb-1">
                            Tanggal Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="create_start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label for="create_end_date" class="block text-xs font-semibold text-[#101828] mb-1">
                            Tanggal Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="create_end_date" name="end_date" value="{{ old('end_date', date('Y-m-d', strtotime('+30 days'))) }}" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                </div>

                <!-- Status Promo -->
                @if (auth()->user()?->role?->name === 'owner')
                    <div>
                        <label for="create_status" class="block text-xs font-semibold text-[#101828] mb-1">
                            Status Promo <span class="text-rose-500">*</span>
                        </label>
                        <select id="create_status" name="status" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif (Dapat langsung digunakan)</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif (Draft / Ditunda)</option>
                        </select>
                    </div>
                @else
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                        <p class="font-semibold m-0">Pengajuan Promo Cabang:</p>
                        <p class="m-0 mt-0.5 text-amber-700">Promo yang diajukan oleh Manajer berstatus <strong>Menunggu Persetujuan (Pending)</strong> hingga disetujui Owner.</p>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 bg-[#F8F9FC] border-t border-[#E7ECF3] flex items-center justify-end gap-2.5">
                <button type="button" data-modal-close="modal-create-promo" class="h-9 px-4 rounded-lg text-xs font-semibold text-[#062072] bg-[#EAF2FF] hover:bg-[#DCE8FF] transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="h-9 px-4 rounded-lg text-xs font-semibold text-white bg-[#062072] hover:bg-[#04184F] transition-colors cursor-pointer shadow-2xs">
                    {{ auth()->user()?->role?->name === 'owner' ? 'Simpan Promo' : 'Ajukan Promo' }}
                </button>
            </div>
        </form>
    </div>
</div>
