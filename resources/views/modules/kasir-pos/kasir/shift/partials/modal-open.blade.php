<!-- Modal: Buka Shift Kasir Baru -->
<div id="modal-open-shift" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full border border-[#E4E7EC] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E4E7EC] bg-[#F5F8FC] flex items-center justify-between">
            <h4 class="text-sm font-bold text-[#101828] m-0 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                </svg>
                Buka Shift Kasir Baru
            </h4>
            <button type="button" onclick="closeOpenShiftModal()" class="text-[#667085] hover:text-[#101828]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('pos.shift.open') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label for="modal_shift_branch_id" class="block text-xs font-semibold text-[#101828] mb-1">Cabang Operasional</label>
                <select id="modal_shift_branch_id" name="branch_id" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    @foreach ($accessibleBranches as $b)
                        <option value="{{ $b->id }}" {{ ($activeBranch?->id === $b->id) ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="modal_opening_cash" class="block text-xs font-semibold text-[#101828] mb-1">Modal Awal Uang Kas (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" id="modal_opening_cash" name="opening_cash" value="100000" min="0" step="1000" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm font-bold text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                <span class="text-[11px] text-[#667085] mt-1 block">Uang kembalian yang dimasukkan ke laci kasir saat awal shift.</span>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeOpenShiftModal()" class="px-4 py-2 bg-[#F5F8FC] hover:bg-[#E4E7EC] text-xs font-semibold text-[#667085] rounded-lg transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white rounded-lg shadow-sm transition-colors">
                    Buka Shift Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
