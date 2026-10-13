@extends('layouts.pelanggan', ['title' => 'Buat Appointment'])

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    {{-- Header --}}
    <div>
        <a href="{{ route('customer.booking.index') }}" class="inline-flex items-center gap-1 text-xs text-[#667085] hover:text-[#062072] no-underline mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Appointment
        </a>
        <h2 class="text-xl font-bold text-[#101828] m-0">Buat Janji Temu</h2>
        <p class="text-xs text-[#667085] mt-1 m-0">Pilih cabang, layanan, tanggal, dan waktu untuk membuat appointment</p>
    </div>

    {{-- Flash --}}
    @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm">
        <ul class="list-disc list-inside m-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('customer.booking.store') }}" method="POST" id="bookingForm"
        class="bg-white border border-[#E4E7EC] rounded-2xl shadow-sm">
        @csrf

        <div class="p-6 space-y-6">
            {{-- Cabang --}}
            <div>
                <label for="branch_id" class="block text-sm font-semibold text-[#101828] mb-2">Pilih Cabang</label>
                <select name="branch_id" id="branch_id"
                    class="w-full px-4 py-3 rounded-xl border border-[#E4E7EC] text-sm text-[#101828] focus:ring-2 focus:ring-[#062072]/20 focus:border-[#062072] outline-none transition-all bg-white">
                    <option value="">— Pilih cabang —</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (old('branch_id', $selectedBranchId) == $branch->id) ? 'selected' : '' }}>
                            {{ $branch->display_name }} ({{ $branch->operating_days_label }} &bull; {{ $branch->operating_hours_label }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Layanan --}}
            <div id="service-section" class="{{ empty($selectedBranchId) ? 'hidden' : '' }}">
                <label for="service_id" class="block text-sm font-semibold text-[#101828] mb-2">Pilih Layanan</label>
                <select name="service_id" id="service_id"
                    class="w-full px-4 py-3 rounded-xl border border-[#E4E7EC] text-sm text-[#101828] focus:ring-2 focus:ring-[#062072]/20 focus:border-[#062072] outline-none transition-all bg-white">
                    <option value="">— Pilih layanan —</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ old('service_id', $selectedServiceId) == $service->id ? 'selected' : '' }}
                            data-duration="{{ $service->estimated_duration ?? 0 }}"
                            data-price="{{ $service->price }}">
                            {{ $service->name }} — Rp {{ number_format($service->price, 0, ',', '.') }} — {{ $service->formatted_duration }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Petugas (opsional) --}}
            <div id="employee-section" class="{{ empty($selectedBranchId) ? 'hidden' : '' }}">
                <label for="employee_id" class="block text-sm font-semibold text-[#101828] mb-2">Petugas <span class="text-xs text-[#667085] font-normal">(opsional)</span></label>
                <select name="employee_id" id="employee_id"
                    class="w-full px-4 py-3 rounded-xl border border-[#E4E7EC] text-sm text-[#101828] focus:ring-2 focus:ring-[#062072]/20 focus:border-[#062072] outline-none transition-all bg-white">
                    <option value="">— Siapa saja tersedia —</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tanggal --}}
            <div id="date-section" class="{{ empty($selectedBranchId) ? 'hidden' : '' }}">
                <label for="booking_date" class="block text-sm font-semibold text-[#101828] mb-2">Tanggal</label>
                <input type="date" name="booking_date" id="booking_date"
                    value="{{ old('booking_date') }}"
                    min="{{ date('Y-m-d') }}"
                    class="w-full px-4 py-3 rounded-xl border border-[#E4E7EC] text-sm text-[#101828] focus:ring-2 focus:ring-[#062072]/20 focus:border-[#062072] outline-none transition-all bg-white">
                <p id="date-warning" class="hidden text-xs text-rose-600 mt-1 m-0"></p>
            </div>

            {{-- Jam --}}
            <div id="time-section" class="hidden">
                <label for="booking_time" class="block text-sm font-semibold text-[#101828] mb-2">Waktu Tersedia</label>
                <div id="slots-loading" class="hidden text-xs text-[#667085]">
                    <svg class="animate-spin w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat slot waktu...
                </div>
                <div id="slots-container" class="grid grid-cols-4 sm:grid-cols-6 gap-2"></div>
                <p id="no-slots" class="hidden text-xs text-[#667085] mt-1 m-0">Tidak ada slot waktu tersedia pada tanggal ini.</p>
                <input type="hidden" name="booking_time" id="booking_time_input" value="{{ old('booking_time') }}">
            </div>

            {{-- Catatan --}}
            <div id="notes-section" class="{{ empty($selectedBranchId) ? 'hidden' : '' }}">
                <label for="notes" class="block text-sm font-semibold text-[#101828] mb-2">Catatan <span class="text-xs text-[#667085] font-normal">(opsional)</span></label>
                <textarea name="notes" id="notes" rows="3" maxlength="1000"
                    class="w-full px-4 py-3 rounded-xl border border-[#E4E7EC] text-sm text-[#101828] focus:ring-2 focus:ring-[#062072]/20 focus:border-[#062072] outline-none transition-all bg-white resize-none"
                    placeholder="Catatan tambahan untuk petugas...">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Submit --}}
        <div class="px-6 py-4 bg-[#F5F8FC] border-t border-[#E4E7EC] rounded-b-2xl flex items-center justify-end gap-3">
            <a href="{{ route('customer.booking.index') }}"
                class="px-4 py-2.5 text-sm font-medium text-[#667085] hover:text-[#101828] transition-colors no-underline">
                Batal
            </a>
            <button type="submit" id="submitBtn"
                class="px-6 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white rounded-xl text-sm font-semibold transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                Buat Appointment
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const branchSelect = document.getElementById('branch_id');
    const serviceSelect = document.getElementById('service_id');
    const employeeSelect = document.getElementById('employee_id');
    const dateInput = document.getElementById('booking_date');
    const slotsContainer = document.getElementById('slots-container');
    const slotsLoading = document.getElementById('slots-loading');
    const noSlots = document.getElementById('no-slots');
    const timeSection = document.getElementById('time-section');
    const timeInput = document.getElementById('booking_time_input');
    const submitBtn = document.getElementById('submitBtn');
    const dateWarning = document.getElementById('date-warning');

    let operatingDays = null;
    const dayNames = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

    // Branch change
    branchSelect.addEventListener('change', function() {
        const branchId = this.value;
        const sections = ['service-section', 'employee-section', 'date-section', 'notes-section'];
        sections.forEach(s => document.getElementById(s).classList.toggle('hidden', !branchId));

        if (!branchId) {
            timeSection.classList.add('hidden');
            submitBtn.disabled = true;
            return;
        }

        // Fetch branch data
        fetch(`{{ route('customer.booking.branch-data') }}?branch_id=${branchId}`)
            .then(r => r.json())
            .then(data => {
                operatingDays = data.operating_days;

                // Update services
                serviceSelect.innerHTML = '<option value="">— Pilih layanan —</option>';
                data.services.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.dataset.duration = s.duration;
                    opt.dataset.price = s.price;
                    opt.textContent = `${s.name} — Rp ${new Intl.NumberFormat('id-ID').format(s.price)} — ${s.duration_formatted}`;
                    serviceSelect.appendChild(opt);
                });

                // Update employees
                employeeSelect.innerHTML = '<option value="">— Siapa saja tersedia —</option>';
                data.employees.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.id;
                    opt.textContent = e.name;
                    employeeSelect.appendChild(opt);
                });

                clearSlots();
            });
    });

    // Date change
    dateInput.addEventListener('change', function() {
        if (operatingDays && operatingDays.length > 0) {
            const date = new Date(this.value);
            const dayName = dayNames[date.getDay()];
            if (!operatingDays.map(d => d.toLowerCase()).includes(dayName)) {
                dateWarning.textContent = 'Cabang tidak beroperasi pada hari ini. Silakan pilih hari lain.';
                dateWarning.classList.remove('hidden');
                clearSlots();
                return;
            }
        }
        dateWarning.classList.add('hidden');
        loadSlots();
    });

    // Service/employee change: reload slots
    serviceSelect.addEventListener('change', loadSlots);
    employeeSelect.addEventListener('change', loadSlots);

    function loadSlots() {
        const branchId = branchSelect.value;
        const date = dateInput.value;
        if (!branchId || !date) return;

        timeSection.classList.remove('hidden');
        slotsLoading.classList.remove('hidden');
        noSlots.classList.add('hidden');
        slotsContainer.innerHTML = '';
        timeInput.value = '';
        submitBtn.disabled = true;

        const params = new URLSearchParams({
            branch_id: branchId,
            date: date,
        });
        if (serviceSelect.value) params.append('service_id', serviceSelect.value);
        if (employeeSelect.value) params.append('employee_id', employeeSelect.value);

        fetch(`{{ route('customer.booking.available-slots') }}?${params}`)
            .then(r => r.json())
            .then(slots => {
                slotsLoading.classList.add('hidden');
                if (slots.length === 0) {
                    noSlots.classList.remove('hidden');
                    return;
                }

                slots.forEach(slot => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'slot-btn px-3 py-2 rounded-lg text-xs font-medium border border-[#E4E7EC] text-[#101828] hover:border-[#062072] hover:bg-[#DCE8FF]/30 transition-all cursor-pointer bg-white';
                    btn.textContent = slot;
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.slot-btn').forEach(b => {
                            b.classList.remove('bg-[#062072]', 'text-white', 'border-[#062072]');
                            b.classList.add('bg-white', 'text-[#101828]', 'border-[#E4E7EC]');
                        });
                        this.classList.add('bg-[#062072]', 'text-white', 'border-[#062072]');
                        this.classList.remove('bg-white', 'text-[#101828]', 'border-[#E4E7EC]');
                        timeInput.value = slot;
                        updateSubmitState();
                    });
                    slotsContainer.appendChild(btn);
                });
            });
    }

    function clearSlots() {
        timeSection.classList.add('hidden');
        slotsContainer.innerHTML = '';
        timeInput.value = '';
        submitBtn.disabled = true;
    }

    function updateSubmitState() {
        submitBtn.disabled = !(branchSelect.value && serviceSelect.value && dateInput.value && timeInput.value);
    }
});
</script>
@endpush
