<!-- Modal Catat Komisi Manual -->
@if (! ($isSelfView ?? false))
    <x-modals.modal id="modal-create-commission" title="Catat Komisi Manual" subtitle="Pencatatan komisi pengerjaan layanan untuk karyawan" maxWidth="max-w-md">
        <form id="form-create-commission" method="POST" action="{{ route('employees.commissions.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="create_comm_emp" class="block text-xs font-semibold text-[#101828] mb-1">
                    Pilih Karyawan <span class="text-rose-500">*</span>
                </label>
                <select id="create_comm_emp" name="employee_id" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-hidden focus:ring-2 focus:ring-[#062072]">
                    <option value="">Pilih Karyawan Penerima Komisi</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->branch->display_name }})
                        </option>
                    @endforeach
                </select>
                @error('employee_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="create_comm_service" class="block text-xs font-semibold text-[#101828] mb-1">
                    Pilih Layanan <span class="text-rose-500">*</span>
                </label>
                <select id="create_comm_service" name="service_id" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-hidden focus:ring-2 focus:ring-[#062072]">
                    <option value="">Pilih Layanan</option>
                    @foreach ($services as $srv)
                        <option value="{{ $srv->id }}" {{ old('service_id') == $srv->id ? 'selected' : '' }}>
                            {{ $srv->name }} — Rp {{ number_format($srv->price, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
                @error('service_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="create_comm_type" class="block text-xs font-semibold text-[#101828] mb-1">
                        Bentuk Skema <span class="text-rose-500">*</span>
                    </label>
                    <select id="create_comm_type" name="commission_type" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-hidden focus:ring-2 focus:ring-[#062072]">
                        <option value="percentage" {{ old('commission_type') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                        <option value="fixed" {{ old('commission_type') === 'fixed' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                    </select>
                    @error('commission_type')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="create_comm_val" class="block text-xs font-semibold text-[#101828] mb-1">
                        Nilai Skema <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" min="0" id="create_comm_val" name="commission_value" value="{{ old('commission_value', '10') }}" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-hidden focus:ring-2 focus:ring-[#062072]">
                    @error('commission_value')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="create_comm_status" class="block text-xs font-semibold text-[#101828] mb-1">Status Awal Komisi</label>
                <select id="create_comm_status" name="status" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-hidden focus:ring-2 focus:ring-[#062072]">
                    <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Menunggu (Estimasi)</option>
                    <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Disetujui (Siap Dicairkan)</option>
                    <option value="paid" {{ old('status') === 'paid' ? 'selected' : '' }}>Telah Dicairkan</option>
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-3 border-t border-[#E4E7EC] flex items-center justify-end gap-2">
                <button type="button" data-modal-close="modal-create-commission" class="px-3.5 py-2 border border-[#D0D5DD] rounded-lg text-xs font-semibold text-[#344054] hover:bg-slate-50 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btn-submit-commission" class="px-4 py-2 bg-[#062072] text-white rounded-lg text-xs font-semibold hover:bg-[#04184F] transition-colors cursor-pointer inline-flex items-center gap-1.5 shadow-2xs">
                    <span id="btn-submit-text">Simpan Komisi</span>
                </button>
            </div>
        </form>
    </x-modals.modal>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('form-create-commission');
            const submitBtn = document.getElementById('btn-submit-commission');
            const submitText = document.getElementById('btn-submit-text');

            if (form && submitBtn) {
                form.addEventListener('submit', function (e) {
                    if (submitBtn.disabled) {
                        e.preventDefault();
                        return;
                    }
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    if (submitText) {
                        submitText.textContent = 'Menyimpan...';
                    }
                });
            }
        });
    </script>
@endif

