@extends('layouts.pelanggan', ['title' => 'Buat Janji Temu'])

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    {{-- Header --}}
    <div>
        <a href="{{ route('customer.booking.index') }}" class="inline-flex items-center gap-1 text-xs text-[#667085] hover:text-[#062072] no-underline mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Janji Temu
        </a>
        <h2 class="text-xl font-bold text-[#101828] m-0">Buat Janji Temu</h2>
        <p class="text-xs text-[#667085] mt-1 m-0">Pilih cabang, layanan, tanggal, dan waktu untuk membuat janji temu</p>
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
            <div id="time-section" class="hidden space-y-3">
                <div class="flex items-center justify-between">
                    <label for="booking_time" class="block text-sm font-semibold text-[#101828]">Waktu Booking Tersedia</label>
                    <span id="selected-slot-preview" class="hidden text-xs font-semibold text-[#062072] bg-[#DCE8FF]/60 px-2.5 py-1 rounded-full border border-[#062072]/20">
                        Dipilih: <span id="selected-slot-time"></span> WIB
                    </span>
                </div>

                {{-- Keterangan Warna Status (Legend) --}}
                <div class="flex flex-wrap items-center justify-between gap-2.5 p-3 bg-[#F8F9FC] border border-[#E4E7EC] rounded-xl text-xs">
                    <div class="flex items-center gap-1.5 text-[#475467] font-medium">
                        <svg class="w-3.5 h-3.5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Status Jam:</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                        <div class="flex items-center gap-1.5" title="Jam tersedia untuk dipesan">
                            <span class="w-3 h-3 rounded bg-white border border-[#D0D5DD] shadow-xs inline-block"></span>
                            <span class="text-[#344054]">Tersedia</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Jam yang sedang Anda pilih">
                            <span class="w-3 h-3 rounded bg-[#062072] border border-[#062072] inline-block"></span>
                            <span class="text-[#062072] font-semibold">Dipilih</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Kapasitas slot penuh, tidak dapat dipilih">
                            <span class="w-3 h-3 rounded bg-rose-50 border border-rose-300 inline-block"></span>
                            <span class="text-rose-700 font-medium">Penuh</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Waktu telah lewat atau di luar jadwal kerja">
                            <span class="w-3 h-3 rounded bg-slate-100 border border-slate-300 inline-block"></span>
                            <span class="text-slate-500">Lewat / Tutup</span>
                        </div>
                    </div>
                </div>

                <div id="slots-loading" class="hidden text-xs text-[#667085] py-4 text-center">
                    <svg class="animate-spin w-4 h-4 inline mr-1 text-[#062072]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memeriksa ketersediaan jam janji temu...
                </div>

                <div id="slots-container" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 sm:gap-2.5"></div>
                <p id="no-slots" class="hidden text-xs text-[#667085] mt-2 py-3 px-4 bg-slate-50 border border-slate-200 rounded-xl m-0 text-center">
                    Tidak ada slot waktu yang tersedia pada tanggal ini. Silakan pilih tanggal atau petugas lain.
                </p>
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
                class="px-6 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white rounded-xl text-sm font-semibold transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                disabled>
                Buat Janji Temu
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
    const selectedPreview = document.getElementById('selected-slot-preview');
    const selectedTimeSpan = document.getElementById('selected-slot-time');

    let operatingDays = null;
    const dayNames = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

    // Branch change
    branchSelect.addEventListener('change', function() {
        const branchId = this.value;
        const sections = ['service-section', 'employee-section', 'date-section', 'notes-section'];
        sections.forEach(s => document.getElementById(s).classList.toggle('hidden', !branchId));

        if (!branchId) {
            clearSlots();
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
                if (dateInput.value) {
                    validateDateAndLoadSlots();
                }
            });
    });

    // Date change
    dateInput.addEventListener('change', function() {
        validateDateAndLoadSlots();
    });

    function validateDateAndLoadSlots() {
        if (!dateInput.value) {
            clearSlots();
            return;
        }

        if (operatingDays && operatingDays.length > 0) {
            const date = new Date(dateInput.value);
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
    }

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
        
        // Simpan waktu yang sebelumnya dipilih untuk dicocokkan kembali jika masih valid
        const previousSelectedTime = timeInput.value;
        timeInput.value = '';
        if (selectedPreview) selectedPreview.classList.add('hidden');
        updateSubmitState();

        const params = new URLSearchParams({
            branch_id: branchId,
            date: date,
        });
        if (serviceSelect.value) params.append('service_id', serviceSelect.value);
        if (employeeSelect.value) params.append('employee_id', employeeSelect.value);

        fetch(`{{ route('customer.booking.available-slots') }}?${params}`)
            .then(r => r.json())
            .then(res => {
                slotsLoading.classList.add('hidden');
                
                // Normalisasi respons array atau objek { is_open, slots }
                const rawSlots = Array.isArray(res) ? res : (res.slots || []);

                if (rawSlots.length === 0) {
                    noSlots.classList.remove('hidden');
                    return;
                }

                let slotToRestore = null;

                rawSlots.forEach(item => {
                    // Mendukung string jam biasa (fallback) maupun objek status lengkap
                    const isObj = (typeof item === 'object' && item !== null);
                    const time = isObj ? item.time : item;
                    const status = isObj ? item.status : 'available';

                    const btn = document.createElement('button');
                    btn.type = 'button';

                    const unselectedBtnClass = 'slot-btn group relative flex flex-col items-center justify-center py-2.5 px-2 rounded-xl text-xs font-semibold border border-[#D0D5DD] bg-white text-[#101828] hover:border-[#062072] hover:bg-[#F5F8FC] hover:shadow-xs transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#062072]/40 shadow-2xs';
                    const selectedBtnClass = 'slot-btn is-selected group relative flex flex-col items-center justify-center py-2.5 px-2 rounded-xl text-xs font-semibold border border-[#062072] bg-[#062072] text-white hover:bg-[#04184F] hover:border-[#04184F] hover:text-white transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#062072]/40 shadow-sm ring-2 ring-[#062072]/20';

                    if (status === 'full') {
                        // 1. JAM PENUH: Card merah lembut, teks merah, disabled (tidak bisa diklik)
                        btn.disabled = true;
                        btn.tabIndex = -1;
                        btn.className = 'relative flex flex-col items-center justify-center py-2.5 px-2 rounded-xl text-xs font-semibold border border-rose-200 text-rose-700 bg-rose-50 cursor-not-allowed select-none opacity-95 transition-all shadow-2xs';
                        btn.title = `Jam ${time} sudah penuh dipesan`;
                        btn.innerHTML = `
                            <span class="text-sm font-bold tracking-tight text-rose-700">${time}</span>
                            <span class="text-[10px] text-rose-600 font-semibold mt-0.5 flex items-center gap-0.5">
                                <svg class="w-2.5 h-2.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                Penuh
                            </span>
                        `;
                    } else if (status === 'past' || status === 'outside_shift') {
                        // 2. JAM LEWAT / DI LUAR JADWAL: Tampil abu-abu, disabled
                        btn.disabled = true;
                        btn.tabIndex = -1;
                        btn.className = 'relative flex flex-col items-center justify-center py-2.5 px-2 rounded-xl text-xs font-semibold border border-slate-200 text-slate-500 bg-slate-100 cursor-not-allowed select-none transition-all';
                        const labelText = status === 'past' ? 'Lewat' : 'Tutup';
                        btn.title = isObj && item.message ? item.message : `Jam ${time} ${labelText.toLowerCase()}`;
                        btn.innerHTML = `
                            <span class="text-sm font-bold tracking-tight text-slate-500">${time}</span>
                            <span class="text-[10px] text-slate-500 font-medium mt-0.5">${labelText}</span>
                        `;
                    } else {
                        // 3. JAM TERSEDIA: Normal putih, hover navy cerah dengan teks navy, focus ring kontras
                        btn.className = unselectedBtnClass;
                        btn.dataset.time = time;
                        btn.setAttribute('aria-pressed', 'false');
                        btn.title = `Pilih jam ${time} WIB`;
                        btn.innerHTML = `
                            <span class="slot-time text-sm font-bold tracking-tight text-[#101828] group-hover:text-[#062072] transition-colors">${time}</span>
                            <span class="slot-badge text-[10px] text-emerald-700 font-semibold mt-0.5">Tersedia</span>
                        `;

                        btn.addEventListener('click', function() {
                            selectSlot(this, time);
                        });

                        // Cek apakah waktu ini cocok dengan nilai sebelumnya atau old('booking_time')
                        if (previousSelectedTime && (time === previousSelectedTime || time.startsWith(previousSelectedTime))) {
                            slotToRestore = { btn, time };
                        }
                    }

                    slotsContainer.appendChild(btn);
                });

                // Pulihkan pilihan jika masih tersedia
                if (slotToRestore) {
                    selectSlot(slotToRestore.btn, slotToRestore.time);
                }
            })
            .catch(() => {
                slotsLoading.classList.add('hidden');
                noSlots.textContent = 'Gagal memuat jadwal slot waktu. Silakan coba lagi.';
                noSlots.classList.remove('hidden');
            });
    }

    function applyUnselectedSlot(b) {
        b.className = 'slot-btn group relative flex flex-col items-center justify-center py-2.5 px-2 rounded-xl text-xs font-semibold border border-[#D0D5DD] bg-white text-[#101828] hover:border-[#062072] hover:bg-[#F5F8FC] hover:shadow-xs transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#062072]/40 shadow-2xs';
        b.setAttribute('aria-pressed', 'false');
        const st = b.querySelector('.slot-time');
        if (st) st.className = 'slot-time text-sm font-bold tracking-tight text-[#101828] group-hover:text-[#062072] transition-colors';
        const badge = b.querySelector('.slot-badge');
        if (badge) {
            badge.className = 'slot-badge text-[10px] text-emerald-700 font-semibold mt-0.5';
            badge.textContent = 'Tersedia';
        }
    }

    function applySelectedSlot(b) {
        b.className = 'slot-btn is-selected group relative flex flex-col items-center justify-center py-2.5 px-2 rounded-xl text-xs font-semibold border border-[#062072] bg-[#062072] text-white hover:bg-[#04184F] hover:border-[#04184F] hover:text-white transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#062072]/40 shadow-sm ring-2 ring-[#062072]/20';
        b.setAttribute('aria-pressed', 'true');
        const st = b.querySelector('.slot-time');
        if (st) st.className = 'slot-time text-sm font-bold tracking-tight text-white transition-colors';
        const badge = b.querySelector('.slot-badge');
        if (badge) {
            badge.className = 'slot-badge text-[10px] text-white/95 font-medium mt-0.5';
            badge.textContent = 'Dipilih';
        }
    }

    function selectSlot(btn, time) {
        document.querySelectorAll('.slot-btn').forEach(b => {
            applyUnselectedSlot(b);
        });

        // Terapkan styling terpilih (Biru Navy #062072 solid dengan teks putih tajam, hover #04184F)
        applySelectedSlot(btn);

        timeInput.value = time;
        if (selectedPreview && selectedTimeSpan) {
            selectedTimeSpan.textContent = time;
            selectedPreview.classList.remove('hidden');
        }
        updateSubmitState();
    }

    function clearSlots() {
        timeSection.classList.add('hidden');
        slotsContainer.innerHTML = '';
        timeInput.value = '';
        if (selectedPreview) selectedPreview.classList.add('hidden');
        submitBtn.disabled = true;
    }

    function updateSubmitState() {
        submitBtn.disabled = !(branchSelect.value && serviceSelect.value && dateInput.value && timeInput.value);
    }

    // Auto load jika cabang dan tanggal sudah terpilih (misal old input atau parameter URL)
    if (branchSelect.value && dateInput.value) {
        loadSlots();
    }
});
</script>
@endpush
