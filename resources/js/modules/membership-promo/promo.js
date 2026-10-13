import { openModal } from '../../components/modal.js';

/**
 * Salin kode promo / voucher ke clipboard dengan visual feedback dan fallback.
 */
export function copyPromoCode(code, btn) {
    function showFeedback() {
        const textEl = btn.querySelector('.btn-text');
        const origText = textEl ? textEl.innerText : btn.innerText;

        if (textEl) {
            textEl.innerText = 'Tersalin';
        } else {
            btn.innerText = 'Tersalin';
        }

        btn.classList.add('bg-[#062072]', 'text-white', 'border-[#062072]');
        btn.classList.remove('bg-white', 'text-[#062072]');

        setTimeout(() => {
            if (textEl) {
                textEl.innerText = origText;
            } else {
                btn.innerText = origText;
            }
            btn.classList.remove('bg-[#062072]', 'text-white', 'border-[#062072]');
            btn.classList.add('bg-white', 'text-[#062072]');
        }, 2000);
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code).then(showFeedback).catch(() => {
            fallbackCopyText(code);
            showFeedback();
        });
    } else {
        fallbackCopyText(code);
        showFeedback();
    }
}

function fallbackCopyText(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        document.execCommand('copy');
    } catch (e) {}
    document.body.removeChild(textArea);
}

export function openEditPromoModal(data, triggerBtn = null) {
    const form = document.getElementById('edit-promo-form');
    if (!form) return;

    form.action = '/promotions/' + data.id;

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) {
            el.value = val ?? '';
            el.dispatchEvent(new Event('change'));
        }
    };

    setVal('edit_code', data.code);
    setVal('edit_name', data.name);
    setVal('edit_branch_id', data.branch_id);
    setVal('edit_discount_type', data.discount_type || 'percentage');
    setVal('edit_discount_value', data.discount_value ?? 0);
    setVal('edit_quota', data.quota);
    setVal('edit_start_date', data.start_date);
    setVal('edit_end_date', data.end_date);

    const statusEl = document.getElementById('edit_status');
    if (statusEl && data.status) {
        statusEl.value = data.status;
        statusEl.dispatchEvent(new Event('change'));
    }

    openModal('modal-edit-promo', triggerBtn);
}

export function openApprovePromoModal(url, code, name, branch, triggerBtn = null) {
    const form = document.getElementById('form-action-approve');
    if (!form) return;
    form.action = url;

    const codeEl = document.getElementById('approve-promo-code');
    const nameEl = document.getElementById('approve-promo-name');
    const branchEl = document.getElementById('approve-promo-branch');

    if (codeEl) codeEl.textContent = code;
    if (nameEl) nameEl.textContent = name;
    if (branchEl) branchEl.textContent = branch;

    openModal('modal-action-approve', triggerBtn);
}

export function openRejectPromoModal(url, code, name, branch, triggerBtn = null) {
    const form = document.getElementById('form-action-reject');
    if (!form) return;
    form.action = url;

    const codeEl = document.getElementById('reject-promo-code');
    const nameEl = document.getElementById('reject-promo-name');
    const branchEl = document.getElementById('reject-promo-branch');

    if (codeEl) codeEl.textContent = code;
    if (nameEl) nameEl.textContent = name;
    if (branchEl) branchEl.textContent = branch;

    openModal('modal-action-reject', triggerBtn);
}

export function openTogglePromoModal(url, code, name, isActive, triggerBtn = null) {
    const form = document.getElementById('form-action-toggle');
    if (!form) return;
    form.action = url;

    const titleEl = document.getElementById('toggle-promo-title');
    const descEl = document.getElementById('toggle-promo-desc');
    const labelEl = document.getElementById('toggle-promo-label');
    const submitBtn = document.getElementById('toggle-promo-submit');
    const iconActive = document.getElementById('toggle-promo-icon-active');
    const iconInactive = document.getElementById('toggle-promo-icon-inactive');

    if (labelEl) labelEl.textContent = `${code} — ${name}`;

    if (isActive) {
        if (titleEl) titleEl.textContent = 'Nonaktifkan Promo';
        if (descEl) descEl.textContent = 'Promo tidak akan dapat digunakan kasir atau booking pelanggan.';
        if (submitBtn) {
            submitBtn.textContent = 'Ya, Nonaktifkan';
            submitBtn.className = 'h-9 px-4 rounded-lg text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 transition-colors cursor-pointer shadow-2xs';
        }
        if (iconActive) iconActive.classList.remove('hidden');
        if (iconInactive) iconInactive.classList.add('hidden');
    } else {
        if (titleEl) titleEl.textContent = 'Aktifkan Promo';
        if (descEl) descEl.textContent = 'Promo akan dapat digunakan kembali saat checkout transaksi.';
        if (submitBtn) {
            submitBtn.textContent = 'Ya, Aktifkan';
            submitBtn.className = 'h-9 px-4 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors cursor-pointer shadow-2xs';
        }
        if (iconActive) iconActive.classList.add('hidden');
        if (iconInactive) iconInactive.classList.remove('hidden');
    }

    openModal('modal-action-toggle', triggerBtn);
}

// Delegasi event click untuk tabel promo (Zero inline script & zero onclick)
if (typeof document !== 'undefined') {
    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.btn-edit-promo');
        if (editBtn) {
            e.preventDefault();
            try {
                const data = JSON.parse(editBtn.getAttribute('data-promo'));
                openEditPromoModal(data, editBtn);
            } catch (err) {
                console.error('Format data promo invalid', err);
            }
            return;
        }

        const approveBtn = e.target.closest('.btn-approve-promo');
        if (approveBtn) {
            e.preventDefault();
            openApprovePromoModal(
                approveBtn.getAttribute('data-url'),
                approveBtn.getAttribute('data-code'),
                approveBtn.getAttribute('data-name'),
                approveBtn.getAttribute('data-branch'),
                approveBtn
            );
            return;
        }

        const rejectBtn = e.target.closest('.btn-reject-promo');
        if (rejectBtn) {
            e.preventDefault();
            openRejectPromoModal(
                rejectBtn.getAttribute('data-url'),
                rejectBtn.getAttribute('data-code'),
                rejectBtn.getAttribute('data-name'),
                rejectBtn.getAttribute('data-branch'),
                rejectBtn
            );
            return;
        }

        const toggleBtn = e.target.closest('.btn-toggle-promo');
        if (toggleBtn) {
            e.preventDefault();
            openTogglePromoModal(
                toggleBtn.getAttribute('data-url'),
                toggleBtn.getAttribute('data-code'),
                toggleBtn.getAttribute('data-name'),
                toggleBtn.getAttribute('data-is-active') === '1',
                toggleBtn
            );
            return;
        }

        const copyBtn = e.target.closest('[data-copy-promo]');
        if (copyBtn) {
            e.preventDefault();
            const code = copyBtn.getAttribute('data-copy-promo');
            if (code) copyPromoCode(code, copyBtn);
        }
    });

    // Guard pencegahan double-submit untuk seluruh form promo
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!form.matches('#modal-create-promo form, #edit-promo-form, #form-action-approve, #form-action-reject, #form-action-toggle')) {
            return;
        }
        if (form.getAttribute('data-submitting') === 'true') {
            e.preventDefault();
            return;
        }
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            form.setAttribute('data-submitting', 'true');
            submitBtn.disabled = true;
            submitBtn.dataset.originalText = submitBtn.innerText;
            submitBtn.innerText = 'Menyimpan...';
        }
    });

    // Reset loading state jika browser validation (HTML5) menemukan field tidak valid
    document.addEventListener('invalid', (e) => {
        const form = e.target.closest('form');
        if (form && form.getAttribute('data-submitting') === 'true') {
            form.removeAttribute('data-submitting');
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && submitBtn.dataset.originalText) {
                submitBtn.disabled = false;
                submitBtn.innerText = submitBtn.dataset.originalText;
            }
        }
    }, true);
}

// Global exposure untuk backward-compatibility
if (typeof window !== 'undefined') {
    window.copyPromoCode = copyPromoCode;
    window.openEditPromoModal = openEditPromoModal;
    window.openApprovePromoModal = openApprovePromoModal;
    window.openRejectPromoModal = openRejectPromoModal;
    window.openTogglePromoModal = openTogglePromoModal;
}
