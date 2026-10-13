import { openModal } from '../../components/modal.js';

export function openEditServiceModal(service, triggerBtn = null) {
    const editForm = document.getElementById('edit-service-form');
    if (!editForm) return;

    editForm.action = `/catalog/services/${service.id}`;

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) {
            el.value = val ?? '';
            el.dispatchEvent(new Event('change'));
        }
    };

    setVal('edit_service_name', service.name);
    setVal('edit_service_category_id', service.service_category_id);
    setVal('edit_service_branch_id', service.branch_id);
    setVal('edit_service_price', service.price);
    setVal('edit_service_unit', service.unit);
    setVal('edit_service_estimated_duration', service.estimated_duration);
    setVal('edit_service_type', service.service_type);
    setVal('edit_commission_type', service.commission_type);
    setVal('edit_commission_value', service.commission_value);
    setVal('edit_service_desc', service.description);

    const activeCheck = document.getElementById('edit_service_is_active');
    if (activeCheck) {
        activeCheck.checked = Boolean(service.is_active);
    }

    const previewContainer = document.getElementById('edit_service_image_preview');
    const previewImg = document.getElementById('edit_service_image_img');
    if (previewContainer && previewImg) {
        if (service.image_url) {
            previewImg.src = service.image_url;
            previewContainer.classList.remove('hidden');
        } else {
            previewContainer.classList.add('hidden');
        }
    }

    openModal('modal-edit-service', triggerBtn);
}

export function initServiceModalListeners(root = document) {
    root.querySelectorAll('[data-action="edit-service"]:not([data-svc-bound])').forEach((btn) => {
        btn.setAttribute('data-svc-bound', 'true');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                const service = JSON.parse(btn.getAttribute('data-payload') || '{}');
                openEditServiceModal(service, btn);
            } catch (err) {
                console.error('Failed to parse service payload', err);
            }
        });
    });

    ['modal-create-service', 'modal-edit-service'].forEach((modalId) => {
        const modal = document.getElementById(modalId);
        if (!modal || modal.getAttribute('data-submit-bound')) return;
        modal.setAttribute('data-submit-bound', 'true');
        const form = modal.querySelector('form');
        if (!form) return;

        form.addEventListener('submit', () => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            }
        });
    });
}

if (typeof window !== 'undefined') {
    window.openEditServiceModal = openEditServiceModal;
    window.initServiceModalListeners = initServiceModalListeners;
}
