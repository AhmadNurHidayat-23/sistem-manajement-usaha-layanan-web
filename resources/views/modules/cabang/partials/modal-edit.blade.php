<!-- Modal Edit Cabang -->
<div id="modal-edit-branch" class="fixed inset-0 z-50 {{ $errors->any() && old('_method') === 'PUT' ? '' : 'hidden' }} overflow-y-auto bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="modal-edit-branch-title" data-backdrop-close>
    <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-[#E7ECF3] overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7ECF3] bg-white sticky top-0 z-10">
            <div>
                <h3 id="modal-edit-branch-title" class="text-base sm:text-lg font-bold text-[#101828] m-0">Edit Informasi Cabang</h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Perbarui data lokasi, jam operasional, dan mode layanan cabang.</p>
            </div>
            <button type="button" data-modal-close="modal-edit-branch" class="text-[#667085] hover:text-[#101828] p-1.5 rounded-lg hover:bg-[#F5F8FC] transition-colors cursor-pointer" aria-label="Tutup Modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form id="edit-branch-form" method="POST" action="" class="flex flex-col flex-1 overflow-hidden m-0">
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

                <div>
                    <label for="edit_branch_name" class="block text-xs font-semibold text-[#101828] mb-1">
                        Nama Cabang <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="edit_branch_name" name="name" value="{{ old('name') }}" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="edit_branch_address" class="block text-xs font-semibold text-[#101828] mb-1">
                        Alamat Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="edit_branch_address" name="address" rows="3" required class="w-full p-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_branch_phone" class="block text-xs font-semibold text-[#101828] mb-1">Nomor Telepon</label>
                        <input type="text" id="edit_branch_phone" name="phone" value="{{ old('phone') }}" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                    <div>
                        <label for="edit_branch_capacity" class="block text-xs font-semibold text-[#101828] mb-1">Kapasitas Slot <span class="text-rose-500">*</span></label>
                        <input type="number" id="edit_branch_capacity" name="max_slot_capacity" value="{{ old('max_slot_capacity') }}" required min="1" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="edit_branch_opening" class="block text-xs font-semibold text-[#101828] mb-1">Jam Buka</label>
                        <input type="time" id="edit_branch_opening" name="opening_time" value="{{ old('opening_time') }}" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                    <div>
                        <label for="edit_branch_closing" class="block text-xs font-semibold text-[#101828] mb-1">Jam Tutup</label>
                        <input type="time" id="edit_branch_closing" name="closing_time" value="{{ old('closing_time') }}" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                    <div>
                        <label for="edit_branch_mode" class="block text-xs font-semibold text-[#101828] mb-1">Mode Layanan <span class="text-rose-500">*</span></label>
                        <select id="edit_branch_mode" name="service_mode" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                            <option value="both">Keduanya (Appointment & Drop-off)</option>
                            <option value="appointment">Appointment Only</option>
                            <option value="drop_off">Drop-off Only</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#101828] mb-1.5">Hari Operasional</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach(\App\Models\Branch::DAYS_OF_WEEK as $label => $val)
                            <label class="inline-flex items-center gap-2 p-2 border border-[#E4E7EC] rounded-lg text-xs cursor-pointer hover:bg-[#F5F8FC]">
                                <input type="checkbox" name="operating_days[]" value="{{ $val }}" id="edit_day_{{ $val }}"
                                    class="edit-branch-day w-3.5 h-3.5 text-[#062072] rounded">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="edit_branch_active" name="is_active" value="1" class="w-4 h-4 text-[#062072] border-[#E4E7EC] rounded focus:ring-[#062072]">
                    <label for="edit_branch_active" class="text-xs font-medium text-[#101828]">Status Cabang Aktif</label>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-[#F5F8FC] border-t border-[#E7ECF3] flex justify-end gap-2.5">
                <button type="button" data-modal-close="modal-edit-branch" class="h-9 px-4 bg-white hover:bg-slate-50 border border-[#D0D5DD] text-[#344054] text-xs font-semibold rounded-lg transition-colors cursor-pointer shadow-2xs">
                    Batal
                </button>
                <button type="submit" class="h-9 px-4 bg-[#062072] hover:bg-[#04184F] text-white text-xs font-semibold rounded-lg transition-colors cursor-pointer shadow-2xs flex items-center gap-1.5">
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
