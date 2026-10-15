<x-modals.modal id="modal-edit-service" title="Edit Layanan" subtitle="Perbarui spesifikasi dan tarif layanan" maxWidth="max-w-3xl">
    <form id="edit-service-form" method="POST" action="" enctype="multipart/form-data" class="space-y-4 max-h-[75vh] overflow-y-auto pr-1">
        @csrf
        @method('PUT')

        <div>
            <label for="edit_service_name" class="block text-xs font-semibold text-[#101828] mb-1">
                Nama Layanan <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="edit_service_name" name="name" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="edit_service_category_id" class="block text-xs font-semibold text-[#101828] mb-1">
                    Kategori Layanan <span class="text-rose-500">*</span>
                </label>
                <select id="edit_service_category_id" name="service_category_id" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="edit_service_branch_id" class="block text-xs font-semibold text-[#101828] mb-1">Cabang</label>
                <select id="edit_service_branch_id" name="branch_id" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">Semua Cabang (Global)</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->display_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="edit_service_price" class="block text-xs font-semibold text-[#101828] mb-1">
                    Harga (Rp) <span class="text-rose-500">*</span>
                </label>
                <input type="number" id="edit_service_price" name="price" required min="0" step="1000" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
            </div>
            <div>
                <label for="edit_service_unit" class="block text-xs font-semibold text-[#101828] mb-1">Satuan Unit</label>
                <input type="text" id="edit_service_unit" name="unit" placeholder="sesi, kg, item" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
            </div>
            <div>
                <label for="edit_service_estimated_duration" class="block text-xs font-semibold text-[#101828] mb-1">Estimasi Durasi (Mnt)</label>
                <input type="number" id="edit_service_estimated_duration" name="estimated_duration" min="1" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
            </div>
        </div>

        <div>
            <label for="edit_service_type" class="block text-xs font-semibold text-[#101828] mb-1">
                Tipe Layanan <span class="text-rose-500">*</span>
            </label>
            <select id="edit_service_type" name="service_type" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                <option value="single">Layanan Tunggal</option>
                <option value="package">Paket / Bundling</option>
                <option value="express">Layanan Express</option>
            </select>
        </div>

        <!-- Skema Komisi Karyawan -->
        <div class="p-3.5 bg-[#F5F8FC] border border-[#E4E7EC] rounded-xl space-y-3">
            <div>
                <h4 class="text-xs font-bold text-[#101828] uppercase tracking-wider m-0">Skema Komisi Karyawan</h4>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Pengaturan komisi untuk karyawan yang mengerjakan layanan ini.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="edit_commission_type" class="block text-xs font-semibold text-[#101828] mb-1">Tipe Komisi</label>
                    <select id="edit_commission_type" name="commission_type" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="">-- Belum Diatur --</option>
                        <option value="percentage">Persentase (%)</option>
                        <option value="fixed">Nominal Tetap (Rp)</option>
                    </select>
                </div>
                <div>
                    <label for="edit_commission_value" class="block text-xs font-semibold text-[#101828] mb-1">Nilai Komisi</label>
                    <input type="number" step="0.01" min="0" id="edit_commission_value" name="commission_value" placeholder="Contoh: 10 untuk 10%, atau 25000" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                </div>
            </div>
        </div>

        <div>
            <label for="edit_service_desc" class="block text-xs font-semibold text-[#101828] mb-1">Deskripsi Layanan</label>
            <textarea id="edit_service_desc" name="description" rows="2" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]"></textarea>
        </div>

        <div>
            <label for="edit_service_image" class="block text-xs font-semibold text-[#101828] mb-1">Ganti Foto Layanan (Opsional)</label>
            <div id="edit_service_image_preview" class="hidden mb-2">
                <img id="edit_service_image_img" src="" alt="Foto Layanan" class="w-20 h-20 object-cover rounded-lg border border-[#E4E7EC]">
            </div>
            <input type="file" id="edit_service_image" name="image" accept="image/*" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none">
        </div>

        <div class="flex items-center gap-2 pt-1">
            <input type="checkbox" id="edit_service_is_active" name="is_active" value="1" class="w-4 h-4 text-[#062072] border-[#E4E7EC] rounded">
            <label for="edit_service_is_active" class="text-xs font-medium text-[#101828]">Status Layanan Aktif</label>
        </div>

        <div class="pt-4 border-t border-[#E4E7EC] flex justify-end gap-3">
            <x-buttons.btn variant="outline" type="button" data-modal-close="modal-edit-service">
                Batal
            </x-buttons.btn>
            <x-buttons.btn variant="primary" type="submit">
                Simpan Perubahan
            </x-buttons.btn>
        </div>
    </form>
</x-modals.modal>
