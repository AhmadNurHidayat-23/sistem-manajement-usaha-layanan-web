<!-- Modal Tambah Shift -->
@if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
    <div id="modal-add-shift" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#E4E7EC] pb-3">
                <h3 class="text-base font-bold text-[#101828] m-0">Tambah Jadwal Shift Karyawan</h3>
                <button type="button" onclick="document.getElementById('modal-add-shift').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <x-icons.icon name="close" class="w-5 h-5" />
                </button>
            </div>
            <form method="POST" action="{{ route('employees.shifts.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                <div>
                    <label for="shift_branch_id" class="block text-xs font-semibold text-[#101828] mb-1">Cabang Shift <span class="text-rose-500">*</span></label>
                    <select id="shift_branch_id" name="branch_id" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        @foreach ($accessibleBranches as $b)
                            <option value="{{ $b->id }}" {{ $employee->branch_id === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="shift_date" class="block text-xs font-semibold text-[#101828] mb-1">Tanggal Shift <span class="text-rose-500">*</span></label>
                    <input type="date" id="shift_date" name="shift_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-forms.time-picker id="shift_start_time" name="start_time" label="Jam Mulai" value="08:00" :required="true" />
                    </div>
                    <div>
                        <x-forms.time-picker id="shift_end_time" name="end_time" label="Jam Selesai" value="16:00" :required="true" />
                    </div>
                </div>
                <p class="text-[11px] text-[#667085] -mt-2 m-0">Ketentuan: Durasi satu shift kerja maksimal 8 jam.</p>

                <div>
                    <label for="shift_status" class="block text-xs font-semibold text-[#101828] mb-1">Status Kehadiran Awal</label>
                    <select id="shift_status" name="status" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="scheduled">Terjadwal</option>
                        <option value="present">Hadir</option>
                        <option value="late">Terlambat</option>
                        <option value="absent">Absen</option>
                        <option value="off">Libur</option>
                    </select>
                </div>

                <div class="pt-3 border-t border-[#E4E7EC] flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-add-shift').classList.add('hidden')" class="px-3 py-1.5 border border-[#E4E7EC] rounded-lg text-xs font-semibold text-[#667085] hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-3 py-1.5 bg-[#062072] text-white rounded-lg text-xs font-semibold hover:bg-[#0B3A9E]">
                        Simpan Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Konfirmasi Hapus Shift -->
    <div id="modal-delete-shift"
         class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity"
         role="dialog"
         aria-modal="true"
         aria-labelledby="modal-delete-shift-title"
         data-backdrop-close>
        <div class="relative w-full max-w-md bg-white dark:bg-[#111C35] rounded-2xl shadow-2xl border border-[#E7ECF3] dark:border-[#1E2D4A] p-6 space-y-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 id="modal-delete-shift-title" class="text-base sm:text-lg font-bold text-[#101828] dark:text-[#F8FAFC] m-0">
                        Hapus Jadwal Shift?
                    </h3>
                    <p class="text-xs sm:text-sm text-[#667085] dark:text-[#94A3B8] mt-1.5 m-0 leading-relaxed">
                        Apakah Anda yakin ingin menghapus jadwal shift ini? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
            </div>

            <!-- Detail Shift yang Akan Dihapus -->
            <div id="delete-shift-info" class="p-3 bg-[#F8FAFC] dark:bg-[#17233F] border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-xl text-xs space-y-1">
                <div class="flex justify-between items-center text-[#101828] dark:text-[#F8FAFC]">
                    <span class="text-[#667085] dark:text-[#94A3B8]">Karyawan:</span>
                    <span id="delete_shift_employee_name" class="font-bold">-</span>
                </div>
                <div class="flex justify-between items-center text-[#101828] dark:text-[#F8FAFC]">
                    <span class="text-[#667085] dark:text-[#94A3B8]">Waktu:</span>
                    <span id="delete_shift_time" class="font-semibold text-[#062072] dark:text-[#93C5FD]">-</span>
                </div>
                <div class="flex justify-between items-center text-[#101828] dark:text-[#F8FAFC]">
                    <span class="text-[#667085] dark:text-[#94A3B8]">Cabang:</span>
                    <span id="delete_shift_branch" class="font-medium">-</span>
                </div>
            </div>

            <form id="form-delete-shift" method="POST" action="" class="m-0">
                @csrf
                @method('DELETE')

                <div class="pt-2 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5">
                    <button type="button"
                            data-modal-close="modal-delete-shift"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl border border-[#D0D5DD] dark:border-[#334155] bg-white dark:bg-[#1E293B] text-xs font-semibold text-[#344054] dark:text-[#CBD5E1] hover:bg-[#F8F9FC] dark:hover:bg-[#334155] transition-colors cursor-pointer text-center">
                        Batal
                    </button>
                    <button type="submit"
                            id="btn-confirm-delete-shift"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-[0.98] text-white text-xs font-semibold transition-all cursor-pointer shadow-xs flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="btn-delete-shift-text">Hapus Shift</span>
                        <svg id="btn-delete-shift-spinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
