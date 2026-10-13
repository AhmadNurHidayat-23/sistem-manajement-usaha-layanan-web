<!-- Modal Tambah Bahan Inventori -->
<div id="modal-create-inventory" class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? '' : 'hidden' }} overflow-y-auto bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="modal-create-inv-title" data-backdrop-close>
    <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-[#E7ECF3] overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7ECF3] bg-white sticky top-0 z-10">
            <div>
                <h3 id="modal-create-inv-title" class="text-base sm:text-lg font-bold text-[#101828] m-0">Tambah Bahan Baru</h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Daftarkan bahan atau barang habis pakai ke inventori cabang.</p>
            </div>
            <button type="button" data-modal-close="modal-create-inventory" class="text-[#667085] hover:text-[#101828] p-1.5 rounded-lg hover:bg-[#F5F8FC] transition-colors cursor-pointer" aria-label="Tutup Modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('inventori.store') }}" class="flex flex-col flex-1 overflow-hidden m-0">
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

                @if ($branches->isNotEmpty())
                    <div>
                        <label for="create_inv_branch" class="block text-xs font-semibold text-[#101828] mb-1">
                            Cabang Outlet <span class="text-rose-500">*</span>
                        </label>
                        <select name="branch_id" id="create_inv_branch" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                            <option value="">Pilih Cabang</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @elseif ($managerBranch)
                    <div>
                        <label class="block text-xs font-semibold text-[#101828] mb-1">Cabang Outlet</label>
                        <input type="text" readonly value="{{ $managerBranch->display_name }}" class="w-full h-10 px-3 bg-slate-50 border border-[#E4E7EC] rounded-lg text-sm text-[#667085] cursor-not-allowed">
                        <input type="hidden" name="branch_id" value="{{ $managerBranch->id }}">
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="create_inv_code" class="block text-xs font-semibold text-[#101828] mb-1">Kode Bahan</label>
                        <input type="text" name="code" id="create_inv_code" value="{{ old('code') }}" placeholder="Contoh: BHN-001" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] uppercase focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                    <div>
                        <label for="create_inv_unit" class="block text-xs font-semibold text-[#101828] mb-1">
                            Satuan Unit <span class="text-rose-500">*</span>
                        </label>
                        <select name="unit" id="create_inv_unit" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                            @foreach (['pcs', 'botol', 'ml', 'liter', 'gram', 'kg', 'box', 'sachet', 'tube', 'lembar'] as $u)
                                <option value="{{ $u }}" {{ old('unit', 'pcs') === $u ? 'selected' : '' }}>{{ $u }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="create_inv_name" class="block text-xs font-semibold text-[#101828] mb-1">
                        Nama Bahan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="create_inv_name" required value="{{ old('name') }}" placeholder="Contoh: Shampoo Herbal Anti Dandruff 500ml" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="create_inv_initial" class="block text-xs font-semibold text-[#101828] mb-1">Stok Awal</label>
                        <input type="number" step="0.01" min="0" name="initial_stock" id="create_inv_initial" value="{{ old('initial_stock', 0) }}" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                    <div>
                        <label for="create_inv_min" class="block text-xs font-semibold text-[#101828] mb-1">
                            Batas Minimum <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" name="minimum_stock" id="create_inv_min" required value="{{ old('minimum_stock', 5) }}" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                    <div>
                        <label for="create_inv_cost" class="block text-xs font-semibold text-[#101828] mb-1">Harga Modal (Rp)</label>
                        <input type="number" step="100" min="0" name="cost_per_unit" id="create_inv_cost" value="{{ old('cost_per_unit', 0) }}" placeholder="0" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                </div>

                <div>
                    <label for="create_inv_desc" class="block text-xs font-semibold text-[#101828] mb-1">Keterangan (Opsional)</label>
                    <textarea name="description" id="create_inv_desc" rows="2" placeholder="Catatan supplier, merek..." class="w-full p-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-[#F5F8FC] border-t border-[#E7ECF3] flex justify-end gap-2.5">
                <button type="button" data-modal-close="modal-create-inventory" class="h-9 px-4 bg-white hover:bg-slate-50 border border-[#D0D5DD] text-[#344054] text-xs font-semibold rounded-lg transition-colors cursor-pointer shadow-2xs">
                    Batal
                </button>
                <button type="submit" class="h-9 px-4 bg-[#062072] hover:bg-[#04184F] text-white text-xs font-semibold rounded-lg transition-colors cursor-pointer shadow-2xs flex items-center gap-1.5">
                    <span>Simpan Bahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
