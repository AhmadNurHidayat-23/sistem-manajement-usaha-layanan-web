<x-modals.modal id="modal-create-service" title="Tambah Layanan Baru" subtitle="Isi informasi dan spesifikasi layanan secara lengkap" maxWidth="max-w-3xl">
    <form method="POST" action="{{ route('catalog.services.store') }}" enctype="multipart/form-data" class="space-y-4 max-h-[75vh] overflow-y-auto pr-1">
        @csrf

        <x-forms.input name="name" label="Nama Layanan" placeholder="Contoh: Service Berkala Paket A" required />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="create_service_category_id" class="block text-xs font-semibold text-[#101828] mb-1">
                    Kategori Layanan <span class="text-rose-500">*</span>
                </label>
                <select id="create_service_category_id" name="service_category_id" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('service_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('service_category_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="create_service_branch_id" class="block text-xs font-semibold text-[#101828] mb-1">Cabang (Kosongkan jika semua cabang)</label>
                <select id="create_service_branch_id" name="branch_id" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">Semua Cabang (Global)</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->display_name }}</option>
                    @endforeach
                </select>
                @error('branch_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-forms.input type="number" name="price" label="Harga (Rp)" placeholder="150000" required min="0" step="1000" />
            <x-forms.input type="text" name="unit" label="Satuan Unit" placeholder="misal: sesi, kg, kendaraan, item" />
            <x-forms.input type="number" name="estimated_duration" label="Estimasi Durasi (Menit)" placeholder="45" min="1" />
        </div>

        <div>
            <label for="create_service_type" class="block text-xs font-semibold text-[#101828] mb-1">
                Tipe Layanan <span class="text-rose-500">*</span>
            </label>
            <select id="create_service_type" name="service_type" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                <option value="single" {{ old('service_type') === 'single' ? 'selected' : '' }}>Layanan Tunggal</option>
                <option value="package" {{ old('service_type') === 'package' ? 'selected' : '' }}>Paket / Bundling</option>
                <option value="express" {{ old('service_type') === 'express' ? 'selected' : '' }}>Layanan Express</option>
            </select>
            @error('service_type')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Skema Komisi Karyawan -->
        <div class="p-3.5 bg-[#F5F8FC] border border-[#E4E7EC] rounded-xl space-y-3">
            <div>
                <h4 class="text-xs font-bold text-[#101828] uppercase tracking-wider m-0">Skema Komisi Karyawan</h4>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Pengaturan komisi untuk karyawan yang mengerjakan layanan ini.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="create_commission_type" class="block text-xs font-semibold text-[#101828] mb-1">Tipe Komisi</label>
                    <select id="create_commission_type" name="commission_type" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="">-- Belum Diatur --</option>
                        <option value="percentage" {{ old('commission_type') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                        <option value="fixed" {{ old('commission_type') === 'fixed' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                    </select>
                </div>
                <div>
                    <x-forms.input type="number" step="0.01" min="0" name="commission_value" label="Nilai Komisi" placeholder="Contoh: 10 untuk 10%, atau 25000" />
                </div>
            </div>
        </div>

        <div>
            <label for="create_service_desc" class="block text-xs font-semibold text-[#101828] mb-1">Deskripsi Layanan</label>
            <textarea id="create_service_desc" name="description" rows="2" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="create_service_image" class="block text-xs font-semibold text-[#101828] mb-1">Upload Foto Layanan</label>
            <input type="file" id="create_service_image" name="image" accept="image/*" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none">
            <p class="text-xs text-[#667085] mt-1 m-0">Format: JPG, PNG, WEBP. Maksimal 2MB.</p>
        </div>

        <div class="flex items-center gap-2 pt-1">
            <input type="checkbox" id="create_service_is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-[#062072] border-[#E4E7EC] rounded">
            <label for="create_service_is_active" class="text-xs font-medium text-[#101828]">Status Layanan Aktif</label>
        </div>

        <div class="pt-4 border-t border-[#E4E7EC] flex justify-end gap-3">
            <x-buttons.btn variant="outline" type="button" data-modal-close="modal-create-service">
                Batal
            </x-buttons.btn>
            <x-buttons.btn variant="primary" type="submit">
                Simpan Layanan
            </x-buttons.btn>
        </div>
    </form>
</x-modals.modal>
