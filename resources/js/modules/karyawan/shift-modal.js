import { openModal } from '../../components/modal.js';

export function openEditShiftModal(data, triggerBtn = null) {
    const form = document.getElementById('form-edit-shift');
    if (!form) return;

    form.action = '/employee-shifts/' + data.id;

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) {
            el.value = val ?? '';
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    const setText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text ?? '-';
    };

    setText('edit_shift_employee_name', data.employee_name || data.employee?.name || '-');
    setText('edit_shift_branch_name', data.branch_name || data.branch?.display_name || data.branch?.name || '-');

    const formattedDate = data.shift_date ? data.shift_date.substring(0, 10) : '';
    const formattedStart = data.start_time ? data.start_time.substring(0, 5) : '08:00';
    const formattedEnd = data.end_time ? data.end_time.substring(0, 5) : '16:00';

    setVal('edit_shift_date', formattedDate);
    setVal('edit_shift_start_time', formattedStart);
    setVal('edit_shift_end_time', formattedEnd);
    setVal('edit_shift_status', data.status || 'scheduled');

    openModal('modal-edit-shift', triggerBtn);
}

export function initShiftModalListeners(root = document) {
    root.querySelectorAll('[data-action="edit-shift"]:not([data-shift-bound])').forEach(btn => {
        btn.setAttribute('data-shift-bound', 'true');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                const payload = JSON.parse(btn.getAttribute('data-payload') || '{}');
                openEditShiftModal(payload, btn);
            } catch (err) {
                console.error('Gagal membuka modal edit shift:', err);
            }
        });
    });
}

if (typeof window !== 'undefined') {
    window.openEditShiftModal = openEditShiftModal;
    window.initShiftModalListeners = initShiftModalListeners;
}

