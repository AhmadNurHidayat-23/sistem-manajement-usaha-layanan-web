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
                        <label for="shift_start_time" class="block text-xs font-semibold text-[#101828] mb-1">Jam Mulai <span class="text-rose-500">*</span></label>
                        <input type="time" id="shift_start_time" name="start_time" value="08:00" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    </div>
                    <div>
                        <label for="shift_end_time" class="block text-xs font-semibold text-[#101828] mb-1">Jam Selesai <span class="text-rose-500">*</span></label>
                        <input type="time" id="shift_end_time" name="end_time" value="17:00" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    </div>
                </div>

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
@endif
