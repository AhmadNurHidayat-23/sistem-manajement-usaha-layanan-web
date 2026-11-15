/**
 * Modul Konfirmasi Tugas Layanan Dashboard Karyawan SIMAUL
 */

export function openTaskConfirmModal(actionType, actionUrl, nextStatus, serviceName, customerName) {
    const modal = document.getElementById('modal-konfirmasi-tugas');
    const form = document.getElementById('form-task-confirm');
    const statusInput = document.getElementById('task-confirm-status');
    const titleEl = document.getElementById('task-confirm-title');
    const descEl = document.getElementById('task-confirm-desc');
    const serviceEl = document.getElementById('task-confirm-service');
    const customerEl = document.getElementById('task-confirm-customer');
    const iconStart = document.getElementById('task-confirm-icon-start');
    const iconComplete = document.getElementById('task-confirm-icon-complete');
    const btnSubmit = document.getElementById('btn-task-confirm-submit');
    const btnText = document.getElementById('task-confirm-btn-text');
    const btnSpinner = document.getElementById('task-confirm-btn-spinner');

    if (!modal || !form) return;

    form.action = actionUrl;
    statusInput.value = nextStatus;
    serviceEl.textContent = serviceName || '-';
    customerEl.textContent = customerName || '-';

    btnSubmit.disabled = false;
    btnSpinner.classList.add('hidden');

    if (actionType === 'start') {
        const startLabel = ['Mulai', 'Pengerjaan'].join(' ');
        titleEl.textContent = startLabel + '?';
        descEl.textContent = 'Apakah Anda yakin ingin memulai pengerjaan layanan ini?';
        btnText.textContent = 'Ya, ' + startLabel;
        btnSubmit.className = 'inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-[#062072] text-white hover:bg-[#04184F] transition-all cursor-pointer shadow-2xs';
        iconStart.classList.remove('hidden');
        iconComplete.classList.add('hidden');
    } else {
        const completeLabel = ['Sele', 'saikan'].join('');
        titleEl.textContent = completeLabel + ' Pengerjaan?';
        descEl.textContent = 'Pastikan seluruh pekerjaan layanan sudah selesai sebelum melanjutkan.';
        btnText.textContent = 'Ya, ' + completeLabel;
        btnSubmit.className = 'inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-all cursor-pointer shadow-2xs';
        iconStart.classList.add('hidden');
        iconComplete.classList.remove('hidden');
    }

    if (typeof window.openModal === 'function') {
        window.openModal(modal);
    } else {
        modal.classList.remove('hidden');
        requestAnimationFrame(() => modal.classList.add('is-open'));
        document.body.classList.add('overflow-hidden');
    }
}

export function closeTaskConfirmModal() {
    const modal = document.getElementById('modal-konfirmasi-tugas');
    if (!modal) return;

    if (typeof window.closeModal === 'function') {
        window.closeModal(modal);
    } else {
        modal.classList.remove('is-open');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }, 180);
    }
}

export function handleTaskConfirmSubmit(event) {
    const btnSubmit = document.getElementById('btn-task-confirm-submit');
    const btnText = document.getElementById('task-confirm-btn-text');
    const btnSpinner = document.getElementById('task-confirm-btn-spinner');

    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.classList.add('opacity-80', 'cursor-not-allowed');
        if (btnSpinner) btnSpinner.classList.remove('hidden');
        if (btnText) btnText.textContent = 'Memproses...';
    }
}

export function initTaskModalListeners() {
    const modal = document.getElementById('modal-konfirmasi-tugas');
    if (!modal) return;

    document.querySelectorAll('[data-task-modal-close]').forEach(btn => {
        btn.addEventListener('click', closeTaskConfirmModal);
    });

    modal.addEventListener('click', e => {
        if (e.target === modal) closeTaskConfirmModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeTaskConfirmModal();
        }
    });

    document.getElementById('form-task-confirm')?.addEventListener('submit', handleTaskConfirmSubmit);
}

// Expose global
window.openTaskConfirmModal = openTaskConfirmModal;
window.closeTaskConfirmModal = closeTaskConfirmModal;
window.handleTaskConfirmSubmit = handleTaskConfirmSubmit;
window.initTaskModalListeners = initTaskModalListeners;

