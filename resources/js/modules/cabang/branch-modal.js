import { openModal } from '../../components/modal.js';

export function openEditBranchModal(data, triggerBtn = null) {
    const form = document.getElementById('edit-branch-form');
    if (!form) return;

    form.action = '/branches/' + data.id;

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) {
            el.value = val ?? '';
            el.dispatchEvent(new Event('change'));
        }
    };

    setVal('edit_branch_name', data.name);
    setVal('edit_branch_address', data.address);
    setVal('edit_branch_phone', data.phone);
    setVal('edit_branch_capacity', data.max_slot_capacity);
    setVal('edit_branch_opening', data.opening_time ? data.opening_time.substring(0, 5) : '');
    setVal('edit_branch_closing', data.closing_time ? data.closing_time.substring(0, 5) : '');
    setVal('edit_branch_mode', data.service_mode || 'both');

    const activeEl = document.getElementById('edit_branch_active');
    if (activeEl) {
        activeEl.checked = Boolean(data.is_active);
    }

    const operatingDays = Array.isArray(data.operating_days) ? data.operating_days : [];
    document.querySelectorAll('.edit-branch-day').forEach(cb => {
        cb.checked = operatingDays.includes(cb.value);
    });

    openModal('modal-edit-branch', triggerBtn);
}

export function initBranchModalListeners(root = document) {
    root.querySelectorAll('[data-action="edit-branch"]:not([data-branch-bound])').forEach(btn => {
        btn.setAttribute('data-branch-bound', 'true');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                const payload = JSON.parse(btn.getAttribute('data-payload') || '{}');
                openEditBranchModal(payload, btn);
            } catch (err) {
                console.error('Gagal membuka modal edit cabang:', err);
            }
        });
    });
}

if (typeof window !== 'undefined') {
    window.openEditBranchModal = openEditBranchModal;
    window.initBranchModalListeners = initBranchModalListeners;
}
