/**
 * Modul interaksi halaman operasional Shift Kasir dan auto-load query POS.
 */

export function initShiftPageListeners() {
    document.querySelectorAll('.branch-switch-select').forEach(el => {
        el.addEventListener('change', () => el.form?.submit());
    });

    const cashInput = document.getElementById('shift_closing_cash');
    const expectedEl = document.getElementById('shift-page-expected-cash');
    const alertEl = document.getElementById('shift-page-diff-alert');

    if (cashInput && expectedEl && alertEl) {
        const calculateDiff = () => {
            const expected = Number(expectedEl.getAttribute('data-expected') || 0);
            const entered = Number(cashInput.value || 0);
            const diff = entered - expected;

            if (diff === 0) {
                alertEl.className = 'mt-1.5 text-xs font-semibold text-emerald-600';
                alertEl.innerText = 'Kas Seimbang (Sesuai ekspektasi)';
            } else if (diff > 0) {
                alertEl.className = 'mt-1.5 text-xs font-semibold text-blue-600';
                alertEl.innerText = `Selisih Lebih: + Rp ${Number(diff).toLocaleString('id-ID')}`;
            } else {
                alertEl.className = 'mt-1.5 text-xs font-semibold text-rose-600';
                alertEl.innerText = `Selisih Kurang: - Rp ${Number(Math.abs(diff)).toLocaleString('id-ID')}`;
            }
        };

        cashInput.addEventListener('input', calculateDiff);
        calculateDiff();
    }
}

export function initPosQueryLoader() {
    if (typeof window === 'undefined') return;
    if (!window.location.pathname.includes('/pos')) return;

    const urlParams = new URLSearchParams(window.location.search);
    const bookingId = urlParams.get('booking_id');
    const orderId = urlParams.get('order_id');

    if (bookingId && typeof window.selectSource === 'function') {
        window.selectSource('booking');
        const bSelect = document.getElementById('select_booking_id');
        if (bSelect) bSelect.value = bookingId;
        if (typeof window.loadBookingData === 'function') {
            window.loadBookingData(bookingId);
        }
    } else if (orderId && typeof window.selectSource === 'function') {
        window.selectSource('order');
        const oSelect = document.getElementById('select_order_id');
        if (oSelect) oSelect.value = orderId;
        if (typeof window.loadOrderData === 'function') {
            window.loadOrderData(orderId);
        }
    }
}
