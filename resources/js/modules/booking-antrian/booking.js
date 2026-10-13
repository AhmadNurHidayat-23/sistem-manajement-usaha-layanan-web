/**
 * Logika pemilihan slot waktu dan penugasan karyawan pada form booking customer.
 */
export function initBookingForm(formEl = document.querySelector('form[data-booking-form="true"]')) {
    const branchSelect = document.getElementById('branch_id');
    const dateInput = document.getElementById('booking_date');
    const timeSelect = document.getElementById('booking_time');
    const empSection = document.getElementById('employee-section');
    const empSelect = document.getElementById('employee_id');

    if (!branchSelect || !dateInput || !timeSelect) return;

    const oldTime = formEl?.dataset?.oldTime || '';
    const oldEmpId = formEl?.dataset?.oldEmpId || '';
    const currentTime = formEl?.dataset?.currentTime || '';
    const bookingId = formEl?.dataset?.bookingId || '';

    function loadSlots() {
        const branchId = branchSelect.value;
        const date = dateInput.value;
        if (!branchId || !date) return;

        timeSelect.innerHTML = '<option value="">Memuat slot…</option>';

        let url = `/bookings/available-slots?branch_id=${branchId}&date=${date}`;
        if (bookingId) {
            url += `&exclude=${bookingId}`;
        }

        fetch(url)
            .then(r => r.json())
            .then(data => {
                timeSelect.innerHTML = '<option value="">— Pilih jam —</option>';
                const slots = data.slots || [];

                // Jika sedang edit dan jam saat ini belum ada dalam list, tambahkan sebagai opsi
                if (currentTime && !slots.includes(currentTime.substring(0, 5))) {
                    const opt = document.createElement('option');
                    opt.value = currentTime.substring(0, 5);
                    opt.textContent = `${currentTime.substring(0, 5)} WIB (saat ini)`;
                    opt.selected = true;
                    timeSelect.appendChild(opt);
                }

                if (slots.length === 0 && !currentTime) {
                    timeSelect.innerHTML = '<option value="">Tidak ada slot tersedia</option>';
                    return;
                }

                slots.forEach(slot => {
                    const opt = document.createElement('option');
                    opt.value = slot;
                    opt.textContent = `${slot} WIB`;
                    const matchTime = oldTime || currentTime;
                    if (matchTime && (slot === matchTime || slot === matchTime.substring(0, 5))) {
                        opt.selected = true;
                    }
                    timeSelect.appendChild(opt);
                });
            })
            .catch(() => {
                timeSelect.innerHTML = '<option value="">Gagal memuat slot</option>';
            });
    }

    function loadEmployees() {
        if (!empSection || !empSelect) return;
        const branchId = branchSelect.value;
        if (!branchId) {
            empSection.classList.add('hidden');
            return;
        }

        fetch(`/bookings/branch-employees?branch_id=${branchId}`)
            .then(r => r.json())
            .then(data => {
                empSelect.innerHTML = '<option value="">— Tidak ada preferensi —</option>';
                const employees = data.employees || [];
                if (employees.length > 0) {
                    employees.forEach(emp => {
                        const opt = document.createElement('option');
                        opt.value = emp.id;
                        opt.textContent = emp.name;
                        if (oldEmpId && String(emp.id) === String(oldEmpId)) {
                            opt.selected = true;
                        }
                        empSelect.appendChild(opt);
                    });
                    empSection.classList.remove('hidden');
                } else {
                    empSection.classList.add('hidden');
                }
            })
            .catch(() => {
                empSection.classList.add('hidden');
            });
    }

    branchSelect.addEventListener('change', () => {
        loadSlots();
        loadEmployees();
    });

    dateInput.addEventListener('change', loadSlots);

    if (branchSelect.value) {
        loadSlots();
        loadEmployees();
    }
}

if (typeof window !== 'undefined') {
    window.initBookingForm = initBookingForm;
}
