/**
 * Customer Booking Create Module
 * Mengelola interaksi pemilihan cabang, layanan, petugas, tanggal, dan slot waktu booking.
 */

const dayNames = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

export function initCustomerBookingCreate() {
    const form = document.getElementById('bookingForm');
    if (!form) return;

    const branchDataUrl = form.dataset.branchDataUrl;
    const availableSlotsUrl = form.dataset.availableSlotsUrl;
    let operatingDays = form.dataset.operatingDays ? JSON.parse(form.dataset.operatingDays) : null;

    const branchSelect = document.getElementById('branch_id');
    const serviceSelect = document.getElementById('service_id');
    const employeeSelect = document.getElementById('employee_id');
    const dateInput = document.getElementById('booking_date');
    const slotsContainer = document.getElementById('slots-container');
    const slotsLoading = document.getElementById('slots-loading');
    const noSlots = document.getElementById('no-slots');
    const noDatePrompt = document.getElementById('no-date-prompt');
    const timeSection = document.getElementById('time-section');
    const timeInput = document.getElementById('booking_time_input');
    const submitBtn = document.getElementById('submitBtn');
    const dateWarning = document.getElementById('date-warning');
    const selectedPreview = document.getElementById('selected-slot-preview');
    const selectedTimeSpan = document.getElementById('selected-slot-time');
    const employeeOffWarning = document.getElementById('employee-off-warning');
    const employeeOffMessage = document.getElementById('employee-off-message');

    if (!branchSelect || !dateInput || !slotsContainer) return;

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
        document.querySelectorAll('.slot-btn').forEach(b => applyUnselectedSlot(b));
        applySelectedSlot(btn);
        timeInput.value = time;
        if (selectedPreview && selectedTimeSpan) {
            selectedTimeSpan.textContent = time;
            selectedPreview.classList.remove('hidden');
        }
        updateSubmitState();
    }

    function clearSlots() {
        if (noDatePrompt) noDatePrompt.classList.toggle('hidden', Boolean(dateInput.value));
        if (noSlots) noSlots.classList.add('hidden');
        if (slotsLoading) slotsLoading.classList.add('hidden');
        slotsContainer.innerHTML = '';
        timeInput.value = '';
        if (selectedPreview) selectedPreview.classList.add('hidden');
        if (employeeOffWarning) employeeOffWarning.classList.add('hidden');
        updateSubmitState();
    }

    function updateSubmitState() {
        if (submitBtn) {
            submitBtn.disabled = !(branchSelect.value && serviceSelect?.value && dateInput.value && timeInput.value);
        }
    }

    function renderSlotItem(item, previousSelectedTime) {
        const isObj = (typeof item === 'object' && item !== null);
        const time = isObj ? item.time : item;
        const status = isObj ? item.status : 'available';
        const reason = isObj ? (item.reason || '') : '';
        const message = isObj ? (item.message || '') : '';
        const isAvailable = isObj ? (item.is_available ?? (status === 'available')) : (status === 'available');

        const btn = document.createElement('button');
        btn.type = 'button';

        if (!isAvailable) {
            btn.disabled = true;
            btn.tabIndex = -1;
            if (reason === 'capacity_full' || (status === 'full' && reason !== 'employee_conflict')) {
                btn.className = 'relative flex flex-col items-center justify-center py-2.5 px-2 rounded-xl text-xs font-semibold border border-rose-200 text-rose-700 bg-rose-50 cursor-not-allowed select-none opacity-95 transition-all shadow-2xs';
                btn.title = `Jam ${time} penuh dipesan`;
                btn.innerHTML = `<span class="text-sm font-bold tracking-tight text-rose-700">${time}</span><span class="text-[10px] text-rose-600 font-semibold mt-0.5 flex items-center gap-0.5"><svg class="w-2.5 h-2.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>Penuh</span>`;
            } else if (reason === 'employee_no_shift') {
                btn.className = 'relative flex flex-col items-center justify-center py-2.5 px-2 rounded-xl text-xs font-semibold border border-amber-200 text-amber-900 bg-amber-50/70 cursor-not-allowed select-none transition-all shadow-2xs';
                btn.title = message || `Petugas libur pada jam ${time}`;
                btn.innerHTML = `<span class="text-sm font-bold tracking-tight text-amber-900">${time}</span><span class="text-[10px] text-amber-700 font-semibold mt-0.5 text-center leading-tight">Petugas Libur</span>`;
            } else if (reason === 'employee_conflict' || reason === 'outside_employee_shift' || reason === 'employee_wrong_branch') {
                btn.className = 'relative flex flex-col items-center justify-center py-2.5 px-2 rounded-xl text-xs font-semibold border border-amber-200 text-amber-900 bg-amber-50/70 cursor-not-allowed select-none transition-all shadow-2xs';
                btn.title = message || `Petugas tidak tersedia pada jam ${time}`;
                btn.innerHTML = `<span class="text-sm font-bold tracking-tight text-amber-900">${time}</span><span class="text-[9px] text-amber-700 font-semibold mt-0.5 text-center leading-tight">Petugas Tidak Tersedia</span>`;
            } else {
                btn.className = 'relative flex flex-col items-center justify-center py-2.5 px-2 rounded-xl text-xs font-semibold border border-slate-200 text-slate-500 bg-slate-100 cursor-not-allowed select-none transition-all';
                btn.title = message || `Jam ${time} tidak dapat dipilih`;
                btn.innerHTML = `<span class="text-sm font-bold tracking-tight text-slate-400">${time}</span><span class="text-[10px] text-slate-500 font-medium mt-0.5">${reason === 'past' ? 'Lewat' : 'Tutup'}</span>`;
            }
            return { btn, isSelectable: false, time };
        }

        btn.className = 'slot-btn group relative flex flex-col items-center justify-center py-2.5 px-2 rounded-xl text-xs font-semibold border border-[#D0D5DD] bg-white text-[#101828] hover:border-[#062072] hover:bg-[#F5F8FC] hover:shadow-xs transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#062072]/40 shadow-2xs';
        btn.dataset.time = time;
        btn.setAttribute('aria-pressed', 'false');
        btn.title = `Pilih jam ${time} WIB`;
        btn.innerHTML = `<span class="slot-time text-sm font-bold tracking-tight text-[#101828] group-hover:text-[#062072] transition-colors">${time}</span><span class="slot-badge text-[10px] text-emerald-700 font-semibold mt-0.5">Tersedia</span>`;

        btn.addEventListener('click', () => selectSlot(btn, time));
        const matchesRestore = previousSelectedTime && (time === previousSelectedTime || time.startsWith(previousSelectedTime));
        return { btn, isSelectable: true, time, matchesRestore };
    }

    function loadSlots() {
        const branchId = branchSelect.value;
        const date = dateInput.value;
        if (!branchId || !date) {
            clearSlots();
            return;
        }

        if (timeSection) timeSection.classList.remove('hidden');
        if (noDatePrompt) noDatePrompt.classList.add('hidden');
        if (slotsLoading) slotsLoading.classList.remove('hidden');
        if (noSlots) noSlots.classList.add('hidden');
        if (employeeOffWarning) employeeOffWarning.classList.add('hidden');
        slotsContainer.innerHTML = '';

        const previousSelectedTime = timeInput.value;
        timeInput.value = '';
        if (selectedPreview) selectedPreview.classList.add('hidden');
        updateSubmitState();

        const params = new URLSearchParams({ branch_id: branchId, date });
        if (serviceSelect?.value) params.append('service_id', serviceSelect.value);
        if (employeeSelect?.value) params.append('employee_id', employeeSelect.value);

        fetch(`${availableSlotsUrl}?${params}`)
            .then(r => r.json())
            .then(res => {
                if (slotsLoading) slotsLoading.classList.add('hidden');
                if (res.is_open === false) {
                    if (dateWarning) {
                        dateWarning.textContent = res.message || 'Cabang tidak beroperasi pada hari tersebut.';
                        dateWarning.classList.remove('hidden');
                    }
                    if (noSlots) {
                        noSlots.textContent = 'Cabang tutup pada tanggal ini.';
                        noSlots.classList.remove('hidden');
                    }
                    return;
                }
                if (dateWarning) dateWarning.classList.add('hidden');

                if (res.employee_off_duty && employeeSelect?.value && employeeOffWarning) {
                    if (employeeOffMessage) {
                        employeeOffMessage.textContent = res.employee_message || 'Petugas yang dipilih tidak memiliki jadwal kerja pada tanggal ini.';
                    }
                    employeeOffWarning.classList.remove('hidden');
                }

                const rawSlots = Array.isArray(res) ? res : (res.slots || []);
                if (rawSlots.length === 0) {
                    if (noSlots) {
                        noSlots.textContent = 'Tidak ada slot waktu yang tersedia pada tanggal ini. Silakan pilih tanggal atau petugas lain.';
                        noSlots.classList.remove('hidden');
                    }
                    return;
                }

                let slotToRestore = null;
                rawSlots.forEach(item => {
                    const result = renderSlotItem(item, previousSelectedTime);
                    slotsContainer.appendChild(result.btn);
                    if (result.isSelectable && result.matchesRestore && !slotToRestore) {
                        slotToRestore = result;
                    }
                });

                if (slotToRestore) {
                    selectSlot(slotToRestore.btn, slotToRestore.time);
                }
            })
            .catch(() => {
                if (slotsLoading) slotsLoading.classList.add('hidden');
                if (noSlots) {
                    noSlots.textContent = 'Gagal memuat jadwal slot waktu. Silakan coba lagi.';
                    noSlots.classList.remove('hidden');
                }
            });
    }

    function validateDateAndLoadSlots() {
        if (!dateInput.value) {
            clearSlots();
            return;
        }

        if (operatingDays && operatingDays.length > 0) {
            const parts = dateInput.value.split('-').map(Number);
            if (parts.length === 3 && !isNaN(parts[0]) && !isNaN(parts[1]) && !isNaN(parts[2])) {
                const date = new Date(parts[0], parts[1] - 1, parts[2]);
                const dayName = dayNames[date.getDay()];
                if (!operatingDays.map(d => String(d).toLowerCase()).includes(dayName)) {
                    if (dateWarning) {
                        dateWarning.textContent = 'Cabang tidak beroperasi pada hari ini. Silakan pilih hari lain.';
                        dateWarning.classList.remove('hidden');
                    }
                    if (noDatePrompt) noDatePrompt.classList.add('hidden');
                    if (noSlots) {
                        noSlots.textContent = 'Cabang tutup pada tanggal ini. Silakan pilih hari lain.';
                        noSlots.classList.remove('hidden');
                    }
                    slotsContainer.innerHTML = '';
                    timeInput.value = '';
                    if (selectedPreview) selectedPreview.classList.add('hidden');
                    updateSubmitState();
                    return;
                }
            }
        }
        if (dateWarning) dateWarning.classList.add('hidden');
        loadSlots();
    }

    // Branch selection change
    branchSelect.addEventListener('change', function() {
        const branchId = this.value;
        ['service-section', 'employee-section', 'date-section', 'time-section', 'notes-section'].forEach(s => {
            const el = document.getElementById(s);
            if (el) el.classList.toggle('hidden', !branchId);
        });

        if (serviceSelect) serviceSelect.value = '';
        if (employeeSelect) employeeSelect.value = '';
        clearSlots();

        if (!branchId) {
            operatingDays = null;
            return;
        }

        fetch(`${branchDataUrl}?branch_id=${branchId}`)
            .then(r => r.json())
            .then(data => {
                operatingDays = data.operating_days;
                if (serviceSelect) {
                    serviceSelect.innerHTML = '<option value="">— Pilih layanan —</option>';
                    (data.services || []).forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.dataset.duration = s.duration;
                        opt.dataset.price = s.price;
                        opt.textContent = `${s.name} — Rp ${new Intl.NumberFormat('id-ID').format(s.price)} — ${s.duration_formatted}`;
                        serviceSelect.appendChild(opt);
                    });
                    serviceSelect.dispatchEvent(new Event('render-custom-select'));
                }

                if (employeeSelect) {
                    employeeSelect.innerHTML = '<option value="" disabled selected hidden data-placeholder="true">Pilih Petugas</option>';
                    (data.employees || []).forEach(e => {
                        const opt = document.createElement('option');
                        opt.value = e.id;
                        opt.textContent = e.name;
                        employeeSelect.appendChild(opt);
                    });
                    employeeSelect.dispatchEvent(new Event('render-custom-select'));
                }

                clearSlots();
                if (dateInput.value) {
                    validateDateAndLoadSlots();
                }
            });
    });

    if (serviceSelect) {
        serviceSelect.addEventListener('change', () => {
            if (branchSelect.value && dateInput.value) loadSlots();
        });
    }

    if (employeeSelect) {
        employeeSelect.addEventListener('change', () => {
            if (branchSelect.value && dateInput.value) loadSlots();
        });
    }

    dateInput.addEventListener('change', validateDateAndLoadSlots);
    dateInput.addEventListener('input', function() {
        if (this.value && this.value.length === 10) validateDateAndLoadSlots();
    });

    if (branchSelect.value && dateInput.value) {
        validateDateAndLoadSlots();
    }
}

