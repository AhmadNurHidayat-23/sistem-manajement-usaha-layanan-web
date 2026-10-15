<!-- Modal Tambah Shift Baru -->
@if (! ($isSelfView ?? false))
    <x-modals.modal id="modal-create-shift" title="Tambah Jadwal Shift" subtitle="Atur penugasan shift kerja harian staf" maxWidth="max-w-md">
        <form method="POST" action="{{ route('employees.shifts.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="shift_emp_id" class="block text-xs font-semibold text-[#101828] mb-1">Pilih Karyawan <span class="text-rose-500">*</span></label>
                <select id="shift_emp_id" name="employee_id" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">Pilih Karyawan</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" data-branch="{{ $emp->branch_id }}">{{ $emp->name }} ({{ $emp->branch->display_name }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="modal_branch_id" class="block text-xs font-semibold text-[#101828] mb-1">Cabang Shift <span class="text-rose-500">*</span></label>
                <select id="modal_branch_id" name="branch_id" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->display_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="modal_shift_date" class="block text-xs font-semibold text-[#101828] mb-1">Tanggal Shift <span class="text-rose-500">*</span></label>
                <input type="date" id="modal_shift_date" name="shift_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="modal_start_time" class="block text-xs font-semibold text-[#101828] mb-1">Jam Mulai <span class="text-rose-500">*</span></label>
                    <input type="time" id="modal_start_time" name="start_time" value="08:00" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                </div>
                <div>
                    <label for="modal_end_time" class="block text-xs font-semibold text-[#101828] mb-1">Jam Selesai <span class="text-rose-500">*</span></label>
                    <input type="time" id="modal_end_time" name="end_time" value="16:00" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                </div>
            </div>
            <p class="text-[11px] text-[#667085] -mt-2 m-0">Ketentuan: Durasi satu shift kerja maksimal 8 jam.</p>

            <div>
                <label for="modal_status" class="block text-xs font-semibold text-[#101828] mb-1">Status Kehadiran</label>
                <select id="modal_status" name="status" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="scheduled">Terjadwal</option>
                    <option value="present">Hadir</option>
                    <option value="late">Terlambat</option>
                    <option value="absent">Absen</option>
                    <option value="off">Libur</option>
                </select>
            </div>

            <div class="pt-3 border-t border-[#E4E7EC] flex items-center justify-end gap-2">
                <button type="button" data-modal-close="modal-create-shift" class="px-3 py-1.5 border border-[#E4E7EC] rounded-lg text-xs font-semibold text-[#667085] hover:bg-slate-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-3 py-1.5 bg-[#062072] text-white rounded-lg text-xs font-semibold hover:bg-[#0B3A9E] cursor-pointer">
                    Jadwalkan Shift
                </button>
            </div>
        </form>
    </x-modals.modal>
@endif
