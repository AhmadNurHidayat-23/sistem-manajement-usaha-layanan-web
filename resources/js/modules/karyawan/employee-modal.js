import { openModal } from '../../components/modal.js';

export function openEditEmployeeModal(data, triggerBtn = null) {
    const form = document.getElementById('edit-employee-form');
    if (!form) return;

    form.action = '/employees/' + data.id;

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) {
            el.value = val ?? '';
            el.dispatchEvent(new Event('change'));
        }
    };

    setVal('edit_emp_name', data.name);
    setVal('edit_emp_phone', data.phone);
    setVal('edit_emp_branch', data.branch_id);
    setVal('edit_emp_status', data.status || 'active');
    setVal('edit_emp_user', data.user_id || '');
    setVal('edit_emp_skills', data.skills || '');

    openModal('modal-edit-employee', triggerBtn);
}

export function initEmployeeModalListeners(root = document) {
    root.querySelectorAll('[data-action="edit-employee"]:not([data-emp-bound])').forEach(btn => {
        btn.setAttribute('data-emp-bound', 'true');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                const payload = JSON.parse(btn.getAttribute('data-payload') || '{}');
                openEditEmployeeModal(payload, btn);
            } catch (err) {
                console.error('Gagal membuka modal edit karyawan:', err);
            }
        });
    });
}

if (typeof window !== 'undefined') {
    window.openEditEmployeeModal = openEditEmployeeModal;
    window.initEmployeeModalListeners = initEmployeeModalListeners;
}
