import { openModal } from '../../components/modal.js';

export function openEditUserModal(data, triggerBtn = null) {
    const form = document.getElementById('edit-user-form');
    if (!form) return;

    form.action = '/owner/internal-users/' + data.id;

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) {
            el.value = val ?? '';
            el.dispatchEvent(new Event('change'));
        }
    };

    setVal('edit_user_name', data.name);
    setVal('edit_user_email', data.email);
    setVal('edit_user_phone', data.phone);
    setVal('edit_user_role', data.role_id);

    const pwdEl = document.getElementById('edit_user_password');
    const pwdConfEl = document.getElementById('edit_user_password_conf');
    if (pwdEl) pwdEl.value = '';
    if (pwdConfEl) pwdConfEl.value = '';

    const branchIds = Array.isArray(data.branch_ids) ? data.branch_ids.map(Number) : [];
    document.querySelectorAll('.edit-user-branch').forEach(cb => {
        cb.checked = branchIds.includes(Number(cb.value));
    });

    openModal('modal-edit-user', triggerBtn);
}

export function initUserModalListeners(root = document) {
    root.querySelectorAll('[data-action="edit-user"]:not([data-user-bound])').forEach(btn => {
        btn.setAttribute('data-user-bound', 'true');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                const payload = JSON.parse(btn.getAttribute('data-payload') || '{}');
                openEditUserModal(payload, btn);
            } catch (err) {
                console.error('Gagal membuka modal edit akun:', err);
            }
        });
    });
}

if (typeof window !== 'undefined') {
    window.openEditUserModal = openEditUserModal;
    window.initUserModalListeners = initUserModalListeners;
}
