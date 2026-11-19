import { openModal, closeModal } from '../../components/modal.js';

export function openDeleteShiftModal(data, triggerBtn = null) {
    const modal = document.getElementById('modal-delete-shift');
    const form = document.getElementById('form-delete-shift');
    if (!modal || !form) return;

    form.action = data.actionUrl || ('/employee-shifts/' + data.id);

    const setText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text ?? '-';
    };

    setText('delete_shift_employee_name', data.employeeName || data.employee_name || '-');
    setText('delete_shift_time', (data.shiftDate ? (data.shiftDate + ', ') : '') + (data.shiftTime || data.shift_time || '-'));
    setText('delete_shift_branch', data.branchName || data.branch_name || '-');

    // Reset button & spinner state
    const btn = document.getElementById('btn-confirm-delete-shift');
    const text = document.getElementById('btn-delete-shift-text');
    const spinner = document.getElementById('btn-delete-shift-spinner');
    if (btn) btn.disabled = false;
    if (text) text.textContent = 'Hapus Shift';
    if (spinner) spinner.classList.add('hidden');

    openModal('modal-delete-shift', triggerBtn);
}

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

    root.querySelectorAll('[data-action="delete-shift"]:not([data-shift-delete-bound])').forEach(btn => {
        btn.setAttribute('data-shift-delete-bound', 'true');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                const payload = btn.getAttribute('data-payload')
                    ? JSON.parse(btn.getAttribute('data-payload'))
                    : {
                        id: btn.getAttribute('data-id'),
                        actionUrl: btn.getAttribute('data-action-url'),
                        employeeName: btn.getAttribute('data-employee-name'),
                        shiftDate: btn.getAttribute('data-shift-date'),
                        shiftTime: btn.getAttribute('data-shift-time'),
                        branchName: btn.getAttribute('data-branch-name'),
                    };
                openDeleteShiftModal(payload, btn);
            } catch (err) {
                console.error('Gagal membuka modal hapus shift:', err);
            }
        });
    });

    const deleteForm = root.querySelector('#form-delete-shift');
    if (deleteForm && !deleteForm.dataset.shiftDeleteSubmitBound) {
        deleteForm.dataset.shiftDeleteSubmitBound = 'true';
        deleteForm.addEventListener('submit', (e) => {
            const btn = document.getElementById('btn-confirm-delete-shift');
            const text = document.getElementById('btn-delete-shift-text');
            const spinner = document.getElementById('btn-delete-shift-spinner');
            if (btn && btn.disabled) {
                e.preventDefault();
                return;
            }
            if (btn) btn.disabled = true;
            if (text) text.textContent = 'Menghapus...';
            if (spinner) spinner.classList.remove('hidden');
        });
    }

    const shiftEmpSelect = root.querySelector('#shift_emp_id');
    const modalBranchSelect = root.querySelector('#modal_branch_id');
    if (shiftEmpSelect && modalBranchSelect && !shiftEmpSelect.dataset.shiftBranchSyncBound) {
        shiftEmpSelect.dataset.shiftBranchSyncBound = 'true';
        shiftEmpSelect.addEventListener('change', () => {
            const selectedOpt = shiftEmpSelect.options[shiftEmpSelect.selectedIndex];
            const branchId = selectedOpt?.getAttribute('data-branch');
            if (branchId) {
                modalBranchSelect.value = branchId;
                modalBranchSelect.dispatchEvent(new Event('change', { bubbles: true }));
                modalBranchSelect.dispatchEvent(new Event('render-custom-select'));
            }
        });
    }
}

if (typeof window !== 'undefined') {
    window.openEditShiftModal = openEditShiftModal;
    window.openDeleteShiftModal = openDeleteShiftModal;
    window.initShiftModalListeners = initShiftModalListeners;
}

