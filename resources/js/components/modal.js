/**
 * SIMAUL Modal Component System
 * High performance, accessible, loop-free, with focus trap & trigger restoration.
 */

const triggerMap = new Map();
const closeTimerMap = new Map();
let isKeydownBound = false;
let isBackdropClickBound = false;

export function openModal(id, trigger = null) {
    const el = typeof id === 'string' ? document.getElementById(id) : id;
    if (!el) return;

    if (closeTimerMap.has(el)) {
        clearTimeout(closeTimerMap.get(el));
        closeTimerMap.delete(el);
    }

    // Simpan elemen pemicu untuk dikembalikan fokusnya saat tutup
    const sourceTrigger = trigger || (document.activeElement && document.activeElement !== document.body ? document.activeElement : null);
    if (sourceTrigger) {
        triggerMap.set(el, sourceTrigger);
    }

    el.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        el.classList.add('is-open');
    } else {
        requestAnimationFrame(() => {
            el.classList.add('is-open');
        });
    }

    // Inisialisasi custom select & date picker jika belum terpasang di modal ini
    if (typeof window.initCustomSelects === 'function') {
        window.initCustomSelects(el);
    }
    if (typeof window.initCustomDatePickers === 'function') {
        window.initCustomDatePickers(el);
    }

    // Auto fokus elemen input pertama yang dapat difokuskan
    requestAnimationFrame(() => {
        const firstFocusable = el.querySelector(
            'input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]):not([data-modal-close])'
        );
        if (firstFocusable) {
            firstFocusable.focus();
        }
    });
}

export function closeModal(id) {
    const el = typeof id === 'string' ? document.getElementById(id) : id;
    if (!el || el.classList.contains('hidden')) return;

    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    el.classList.remove('is-open');

    const finalizeClose = () => {
        if (!el.classList.contains('is-open')) {
            el.classList.add('hidden');
        }
        closeTimerMap.delete(el);

        // Kembalikan overflow body jika tidak ada modal lain yang terbuka
        const remainingOpen = document.querySelectorAll('[id^="modal-"]:not(.hidden), [role="dialog"]:not(.hidden)');
        if (remainingOpen.length === 0) {
            document.body.classList.remove('overflow-hidden');
        }

        // Kembalikan fokus ke elemen pemicu
        const trigger = triggerMap.get(el);
        if (trigger && typeof trigger.focus === 'function' && document.body.contains(trigger)) {
            trigger.focus();
        }
        triggerMap.delete(el);
    };

    if (prefersReducedMotion) {
        finalizeClose();
    } else {
        const timer = setTimeout(finalizeClose, 180);
        closeTimerMap.set(el, timer);
    }
}

function handleGlobalKeydown(e) {
    const openModals = Array.from(document.querySelectorAll('[id^="modal-"]:not(.hidden), [role="dialog"]:not(.hidden)'));
    if (openModals.length === 0) return;

    const topModal = openModals[openModals.length - 1];

    // Escape menutup modal teratas
    if (e.key === 'Escape') {
        e.preventDefault();
        closeModal(topModal);
        return;
    }

    // Focus Trap pada tombol Tab
    if (e.key === 'Tab') {
        const focusables = Array.from(topModal.querySelectorAll(
            'a[href], button:not([disabled]), input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )).filter(el => el.offsetParent !== null || el.offsetWidth > 0 || el.offsetHeight > 0);

        if (focusables.length === 0) {
            e.preventDefault();
            return;
        }

        const first = focusables[0];
        const last = focusables[focusables.length - 1];

        if (e.shiftKey) {
            if (document.activeElement === first || !topModal.contains(document.activeElement)) {
                e.preventDefault();
                last.focus();
            }
        } else {
            if (document.activeElement === last || !topModal.contains(document.activeElement)) {
                e.preventDefault();
                first.focus();
            }
        }
    }
}

export function initModalListeners(root = document) {
    // Listener buka modal dengan guard idempotency
    root.querySelectorAll('[data-modal-open]:not([data-modal-bound])').forEach(btn => {
        btn.setAttribute('data-modal-bound', 'true');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = btn.getAttribute('data-modal-open');
            openModal(targetId, btn);
        });
    });

    // Listener tutup modal dengan guard idempotency
    root.querySelectorAll('[data-modal-close]:not([data-modal-bound])').forEach(btn => {
        btn.setAttribute('data-modal-bound', 'true');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = btn.getAttribute('data-modal-close');
            if (targetId) {
                closeModal(targetId);
            } else {
                const modal = btn.closest('[id^="modal-"], [role="dialog"]');
                if (modal) closeModal(modal);
            }
        });
    });

    // Daftarkan event global hanya satu kali
    if (!isKeydownBound && typeof document !== 'undefined') {
        document.addEventListener('keydown', handleGlobalKeydown);
        isKeydownBound = true;
    }

    if (!isBackdropClickBound && typeof document !== 'undefined') {
        document.addEventListener('click', (e) => {
            if (e.target && e.target.hasAttribute && e.target.hasAttribute('data-backdrop-close') && (e.target.matches('[id^="modal-"]') || e.target.matches('[role="dialog"]'))) {
                closeModal(e.target);
            }
        });
        isBackdropClickBound = true;
    }
}

if (typeof window !== 'undefined') {
    window.openModal = openModal;
    window.closeModal = closeModal;
    window.initModalListeners = initModalListeners;
}
