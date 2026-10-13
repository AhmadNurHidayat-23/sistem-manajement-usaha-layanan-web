/**
 * Pengelolaan modal shift kasir dan audit selisih kas.
 */
export function openOpenShiftModal() {
    document.getElementById('modal-open-shift')?.classList.remove('hidden');
}

export function closeOpenShiftModal() {
    document.getElementById('modal-open-shift')?.classList.add('hidden');
}

export function openCloseShiftModal() {
    document.getElementById('modal-close-shift')?.classList.remove('hidden');
}

export function closeCloseShiftModal() {
    document.getElementById('modal-close-shift')?.classList.add('hidden');
}

export function openRegisterMemberModal() {
    document.getElementById('modal-register-member')?.classList.remove('hidden');
}

export function closeRegisterMemberModal() {
    document.getElementById('modal-register-member')?.classList.add('hidden');
}

export function auditCashDiff(enteredVal) {
    const expected = Number(document.getElementById('expected-cash-val')?.getAttribute('data-expected') || 0);
    const entered = Number(enteredVal || 0);
    const diff = entered - expected;
    const alertEl = document.getElementById('diff-alert');

    if (!alertEl) return;

    if (diff === 0) {
        alertEl.className = 'mt-1.5 text-[11px] font-semibold text-emerald-600';
        alertEl.innerText = 'Kas Seimbang (Sesuai ekspektasi)';
    } else if (diff > 0) {
        alertEl.className = 'mt-1.5 text-[11px] font-semibold text-blue-600';
        alertEl.innerText = `Selisih Lebih: + Rp ${Number(diff).toLocaleString('id-ID')}`;
    } else {
        alertEl.className = 'mt-1.5 text-[11px] font-semibold text-rose-600';
        alertEl.innerText = `Selisih Kurang: - Rp ${Number(Math.abs(diff)).toLocaleString('id-ID')}`;
    }
}

// Bind secara global untuk inline event handler Blade
if (typeof window !== 'undefined') {
    window.openOpenShiftModal = openOpenShiftModal;
    window.closeOpenShiftModal = closeOpenShiftModal;
    window.openCloseShiftModal = openCloseShiftModal;
    window.closeCloseShiftModal = closeCloseShiftModal;
    window.openRegisterMemberModal = openRegisterMemberModal;
    window.closeRegisterMemberModal = closeRegisterMemberModal;
    window.auditCashDiff = auditCashDiff;
}
