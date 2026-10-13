<!-- Modal Edit Akun Internal -->
<div id="modal-edit-user" class="fixed inset-0 z-50 {{ $errors->any() && old('_method') === 'PUT' ? '' : 'hidden' }} overflow-y-auto bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="modal-edit-user-title" data-backdrop-close>
    <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-[#E7ECF3] overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7ECF3] bg-white sticky top-0 z-10">
            <div>
                <h3 id="modal-edit-user-title" class="text-base sm:text-lg font-bold text-[#101828] m-0">Edit Akun Internal</h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Perbarui profil akun, peran, dan penugasan cabang operasional.</p>
            </div>
            <button type="button" data-modal-close="modal-edit-user" class="text-[#667085] hover:text-[#101828] p-1.5 rounded-lg hover:bg-[#F5F8FC] transition-colors cursor-pointer" aria-label="Tutup Modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form id="edit-user-form" method="POST" action="" class="flex flex-col flex-1 overflow-hidden m-0">
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
                    <label for="edit_user_name" class="block text-xs font-semibold text-[#101828] mb-1">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="edit_user_name" name="name" value="{{ old('name') }}" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_user_email" class="block text-xs font-semibold text-[#101828] mb-1">
                            Alamat Email <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" id="edit_user_email" name="email" value="{{ old('email') }}" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="edit_user_phone" class="block text-xs font-semibold text-[#101828] mb-1">Nomor WhatsApp / Telepon</label>
                        <input type="text" id="edit_user_phone" name="phone" value="{{ old('phone') }}" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                </div>

                <div>
                    <label for="edit_user_role" class="block text-xs font-semibold text-[#101828] mb-1">
                        Peran Akun <span class="text-rose-500">*</span>
                    </label>
                    <select id="edit_user_role" name="role_id" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                        <option value="">— Pilih Peran Akun —</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#101828] mb-1.5">
                        Penugasan Cabang <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 bg-[#F5F8FC] rounded-lg border border-[#E7ECF3]">
                        @foreach ($branches as $branch)
                            <label class="flex items-center gap-2 p-2 bg-white rounded-md border border-[#E7ECF3] hover:border-[#062072] cursor-pointer transition-colors text-xs">
                                <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}" id="edit_user_branch_{{ $branch->id }}"
                                    class="edit-user-branch w-3.5 h-3.5 rounded text-[#062072] focus:ring-[#062072]">
                                <span class="truncate font-medium text-[#101828]">{{ $branch->display_name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div>
                        <label for="edit_user_password" class="block text-xs font-semibold text-[#101828] mb-1">
                            Ubah Kata Sandi (Opsional)
                        </label>
                        <input type="password" id="edit_user_password" name="password" placeholder="Kosongkan bila tidak diubah" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                    <div>
                        <label for="edit_user_password_conf" class="block text-xs font-semibold text-[#101828] mb-1">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <input type="password" id="edit_user_password_conf" name="password_confirmation" placeholder="Ulangi kata sandi baru" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-[#F5F8FC] border-t border-[#E7ECF3] flex justify-end gap-2.5">
                <button type="button" data-modal-close="modal-edit-user" class="h-9 px-4 bg-white hover:bg-slate-50 border border-[#D0D5DD] text-[#344054] text-xs font-semibold rounded-lg transition-colors cursor-pointer shadow-2xs">
                    Batal
                </button>
                <button type="submit" class="h-9 px-4 bg-[#062072] hover:bg-[#04184F] text-white text-xs font-semibold rounded-lg transition-colors cursor-pointer shadow-2xs flex items-center gap-1.5">
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
