import { openModal } from '../../components/modal.js';

export function openEditInventoryModal(data, triggerBtn = null) {
    const form = document.getElementById('edit-inventory-form');
    if (!form) return;

    form.action = '/inventori/' + data.id;

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) {
            el.value = val ?? '';
            el.dispatchEvent(new Event('change'));
        }
    };

    setVal('edit_inv_code', data.code);
    setVal('edit_inv_name', data.name);
    setVal('edit_inv_unit', data.unit || 'pcs');
    setVal('edit_inv_min', data.minimum_stock);
    setVal('edit_inv_cost', data.cost_per_unit || 0);
    setVal('edit_inv_desc', data.description || '');

    openModal('modal-edit-inventory', triggerBtn);
}

export function initInventoryModalListeners(root = document) {
    root.querySelectorAll('[data-action="edit-inventory"]:not([data-inv-bound])').forEach(btn => {
        btn.setAttribute('data-inv-bound', 'true');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                const payload = JSON.parse(btn.getAttribute('data-payload') || '{}');
                openEditInventoryModal(payload, btn);
            } catch (err) {
                console.error('Gagal membuka modal edit bahan:', err);
            }
        });
    });
}

if (typeof window !== 'undefined') {
    window.openEditInventoryModal = openEditInventoryModal;
    window.initInventoryModalListeners = initInventoryModalListeners;
}
