<!-- Modal Tambah Karyawan Baru -->
<div id="modal-create-employee" class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? '' : 'hidden' }} overflow-y-auto bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="modal-create-employee-title" data-backdrop-close>
    <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-[#E7ECF3] overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7ECF3] bg-white sticky top-0 z-10">
            <div>
                <h3 id="modal-create-employee-title" class="text-base sm:text-lg font-bold text-[#101828] m-0">Tambah Karyawan Baru</h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Masukkan profil karyawan, cabang penempatan, dan spesialisasi keahlian.</p>
            </div>
            <button type="button" data-modal-close="modal-create-employee" class="text-[#667085] hover:text-[#101828] p-1.5 rounded-lg hover:bg-[#F5F8FC] transition-colors cursor-pointer" aria-label="Tutup Modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('employees.store') }}" class="flex flex-col flex-1 overflow-hidden m-0">
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

                <div>
                    <label for="create_emp_name" class="block text-xs font-semibold text-[#101828] mb-1">
                        Nama Lengkap Karyawan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="create_emp_name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="create_emp_phone" class="block text-xs font-semibold text-[#101828] mb-1">
                        Nomor WhatsApp / Telepon <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="create_emp_phone" name="phone" value="{{ old('phone') }}" required placeholder="081234567890" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    @error('phone')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="create_emp_branch" class="block text-xs font-semibold text-[#101828] mb-1">
                            Cabang Penempatan <span class="text-rose-500">*</span>
                        </label>
                        <select id="create_emp_branch" name="branch_id" required class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                            <option value="">Pilih Cabang</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="create_emp_status" class="block text-xs font-semibold text-[#101828] mb-1">
                            Status Karyawan <span class="text-rose-500">*</span>
                        </label>
                        <select id="create_emp_status" name="status" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="create_emp_user" class="block text-xs font-semibold text-[#101828] mb-1">
                        Hubungkan Akun Login Sistem (Opsional)
                    </label>
                    <select id="create_emp_user" name="user_id" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                        <option value="">-- Tanpa Akun Login (Hanya Data Profil) --</option>
                        @if(isset($availableUsers))
                            @foreach ($availableUsers as $u)
                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div>
                    <label for="create_emp_skills" class="block text-xs font-semibold text-[#101828] mb-1">
                        Keahlian & Spesialisasi (Pisahkan dengan koma)
                    </label>
                    <input type="text" id="create_emp_skills" name="skills" value="{{ old('skills') }}" placeholder="Contoh: Potong Rambut, Styling, Cuci Kiloan" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-[#F5F8FC] border-t border-[#E7ECF3] flex justify-end gap-2.5">
                <button type="button" data-modal-close="modal-create-employee" class="h-9 px-4 bg-white hover:bg-slate-50 border border-[#D0D5DD] text-[#344054] text-xs font-semibold rounded-lg transition-colors cursor-pointer shadow-2xs">
                    Batal
                </button>
                <button type="submit" class="h-9 px-4 bg-[#062072] hover:bg-[#04184F] text-white text-xs font-semibold rounded-lg transition-colors cursor-pointer shadow-2xs flex items-center gap-1.5">
                    <span>Simpan Karyawan</span>
                </button>
            </div>
        </form>
    </div>
</div>
