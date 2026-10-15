<!-- Modal Catat Komisi -->
@if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
    <div id="modal-add-commission" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#E4E7EC] pb-3">
                <h3 class="text-base font-bold text-[#101828] m-0">Catat Komisi Baru</h3>
                <button type="button" onclick="document.getElementById('modal-add-commission').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <x-icons.icon name="close" class="w-5 h-5" />
                </button>
            </div>
            <form method="POST" action="{{ route('employees.commissions.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                <div>
                    <label for="comm_service_id" class="block text-xs font-semibold text-[#101828] mb-1">Layanan <span class="text-rose-500">*</span></label>
                    <select id="comm_service_id" name="service_id" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        @foreach ($services ?? [] as $srv)
                            <option value="{{ $srv->id }}">{{ $srv->name }} (Rp {{ number_format($srv->price, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="commission_type" class="block text-xs font-semibold text-[#101828] mb-1">Skema Komisi <span class="text-rose-500">*</span></label>
                        <select id="commission_type" name="commission_type" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal Tetap (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label for="commission_value" class="block text-xs font-semibold text-[#101828] mb-1">Nilai Skema <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" id="commission_value" name="commission_value" value="10" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    </div>
                </div>

                <div>
                    <label for="comm_status" class="block text-xs font-semibold text-[#101828] mb-1">Status Komisi</label>
                    <select id="comm_status" name="status" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="pending">Pending (Estimasi)</option>
                        <option value="approved">Disetujui (Siap Dicairkan)</option>
                        <option value="paid">Telah Dicairkan (Paid)</option>
                    </select>
                </div>

                <div class="pt-3 border-t border-[#E4E7EC] flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-add-commission').classList.add('hidden')" class="px-3 py-1.5 border border-[#E4E7EC] rounded-lg text-xs font-semibold text-[#667085] hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-3 py-1.5 bg-[#062072] text-white rounded-lg text-xs font-semibold hover:bg-[#0B3A9E]">
                        Catat Komisi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
