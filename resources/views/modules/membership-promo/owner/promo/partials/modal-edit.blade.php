<!-- Modal Edit Promo / Voucher -->
<div id="modal-edit-promo" class="fixed inset-0 z-50 {{ $errors->any() && old('_method') === 'PUT' ? '' : 'hidden' }} overflow-y-auto bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="modal-edit-promo-title" data-backdrop-close>
    <div class="relative w-full max-w-3xl bg-white rounded-2xl shadow-2xl border border-[#E7ECF3] overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7ECF3] bg-white sticky top-0 z-10">
            <div>
                <h3 id="modal-edit-promo-title" class="text-base sm:text-lg font-bold text-[#101828] m-0">Edit Promo / Voucher</h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Perbarui rincian potongan diskon, periode berlaku, atau kuota kupon promo.</p>
            </div>
            <button type="button" data-modal-close="modal-edit-promo" class="text-[#667085] hover:text-[#101828] p-1.5 rounded-lg hover:bg-[#F5F8FC] transition-colors cursor-pointer" aria-label="Tutup Modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Form -->
        <form id="edit-promo-form" method="POST" action="" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-4 overflow-y-auto flex-1">
                @if ($errors->any() && old('_method') === 'PUT')
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
                        <label for="edit_code" class="block text-xs font-semibold text-[#101828] mb-1">
                            Kode Promo / Voucher <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="edit_code" name="code" value="{{ old('code') }}" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] font-semibold uppercase focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                        <p class="text-[11px] text-[#667085] mt-1 m-0">Kode unik promo.</p>
                        @error('code')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Promo -->
                    <div>
                        <label for="edit_name" class="block text-xs font-semibold text-[#101828] mb-1">
                            Nama / Judul Promo <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="edit_name" name="name" value="{{ old('name') }}" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                        <p class="text-[11px] text-[#667085] mt-1 m-0">Deskripsi singkat penawaran diskon.</p>
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Cabang Berlaku -->
                <div>
                    <label for="edit_branch_id" class="block text-xs font-semibold text-[#101828] mb-1">
                        Cakupan Cabang
                    </label>
                    <select id="edit_branch_id" name="branch_id" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                        @if (auth()->user()?->role?->name === 'owner')
                            <option value="">Semua Cabang (Global)</option>
                        @endif
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->display_name }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-[#667085] mt-1 m-0">Pilih cabang spesifik atau biarkan berlaku seluruh cabang.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Tipe Diskon -->
                    <div>
                        <label for="edit_discount_type" class="block text-xs font-semibold text-[#101828] mb-1">
                            Jenis Diskon <span class="text-rose-500">*</span>
                        </label>
                        <select id="edit_discount_type" name="discount_type" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal Tetap (Rp)</option>
                        </select>
                    </div>

                    <!-- Nilai Diskon -->
                    <div>
                        <label for="edit_discount_value" class="block text-xs font-semibold text-[#101828] mb-1">
                            Nilai Diskon <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" id="edit_discount_value" name="discount_value" step="0.01" min="0" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                        <p class="text-[11px] text-[#667085] mt-1 m-0">Jika persentase: 1-100. Jika nominal: angka rupiah.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Kuota -->
                    <div>
                        <label for="edit_quota" class="block text-xs font-semibold text-[#101828] mb-1">
                            Kuota Penggunaan
                        </label>
                        <input type="number" id="edit_quota" name="quota" min="1" placeholder="Unlimited" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>

                    <!-- Tanggal Mulai -->
                    <div>
                        <label for="edit_start_date" class="block text-xs font-semibold text-[#101828] mb-1">
                            Tanggal Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="edit_start_date" name="start_date" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label for="edit_end_date" class="block text-xs font-semibold text-[#101828] mb-1">
                            Tanggal Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="edit_end_date" name="end_date" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                </div>

                <!-- Status Promo (Owner Only) -->
                @if (auth()->user()?->role?->name === 'owner')
                    <div>
                        <label for="edit_status" class="block text-xs font-semibold text-[#101828] mb-1">
                            Status Promo <span class="text-rose-500">*</span>
                        </label>
                        <select id="edit_status" name="status" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                            <option value="active">Aktif (Dapat langsung digunakan)</option>
                            <option value="inactive">Nonaktif (Draft / Ditunda)</option>
                            <option value="approved">Disetujui</option>
                            <option value="pending">Menunggu Persetujuan</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 bg-[#F8F9FC] border-t border-[#E7ECF3] flex items-center justify-end gap-2.5">
                <button type="button" data-modal-close="modal-edit-promo" class="h-9 px-4 rounded-lg text-xs font-semibold text-[#062072] bg-[#EAF2FF] hover:bg-[#DCE8FF] transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="h-9 px-4 rounded-lg text-xs font-semibold text-white bg-[#062072] hover:bg-[#04184F] transition-colors cursor-pointer shadow-2xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

