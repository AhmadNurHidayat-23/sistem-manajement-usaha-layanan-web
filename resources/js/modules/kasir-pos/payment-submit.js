/**
 * Modul Transaksi & Submit Pembayaran Kasir
 * Menangani pengiriman data pembayaran ke server, perhitungan kembalian, dan update state transaksi.
 */

import { formatRupiah, getCartItems, clearCartItems, calculateTotals } from './payment-cart.js';

export function togglePaymentMethodFields(method, state) {
    state.selectedMethod = method;
    const cashBox = document.getElementById('pay-cash-container');
    const nonCashBox = document.getElementById('pay-non-cash-container');

    if (method === 'cash') {
        cashBox?.classList.remove('hidden');
        nonCashBox?.classList.add('hidden');
        calculateCashChange(state.selectedMethod);
    } else {
        cashBox?.classList.add('hidden');
        nonCashBox?.classList.remove('hidden');
        const submitBtn = document.getElementById('btn-cashier-pay-submit');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    }
}

export function calculateCashChange(selectedMethod) {
    if (selectedMethod !== 'cash') return;

    const totals = calculateTotals();
    const cashInput = document.getElementById('pay_cash_received');
    const changeDisplay = document.getElementById('pay-change-display');
    const warning = document.getElementById('pay-cash-warning');
    const btnSubmit = document.getElementById('btn-cashier-pay-submit');

    const received = parseFloat(cashInput?.value || 0);
    const required = totals.remaining;
    const change = received - required;

    if (change >= 0) {
        if (changeDisplay) {
            changeDisplay.textContent = formatRupiah(change);
            changeDisplay.className = 'font-extrabold text-emerald-700 text-sm';
        }
        warning?.classList.add('hidden');
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    } else {
        if (changeDisplay) {
            changeDisplay.textContent = '- ' + formatRupiah(Math.abs(change));
            changeDisplay.className = 'font-extrabold text-rose-600 text-sm';
        }
        warning?.classList.remove('hidden');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.classList.add('opacity-60', 'cursor-not-allowed');
        }
    }
}

export function removeDashboardTableRow(type, id) {
    const rowEl = document.getElementById(`payment-row-${type}-${id}`);
    if (rowEl) {
        rowEl.classList.add('bg-emerald-50', 'transition-all', 'duration-500');
        setTimeout(() => {
            rowEl.remove();
            const tbody = document.getElementById('payment-tbody');
            if (tbody && tbody.querySelectorAll('.payment-row').length === 0) {
                document.getElementById('payment-empty-state')?.classList.remove('hidden');
            }
        }, 500);
    }
}

export function handlePaymentSubmit(e, meta, selectedMethod, syncToPosCatalogCart) {
    e.preventDefault();

    const items = getCartItems();
    const errorBanner = document.getElementById('pay-error-banner');
    const errorText = document.getElementById('pay-error-text');
    const btnSubmit = document.getElementById('btn-cashier-pay-submit');
    const totals = calculateTotals();

    errorBanner?.classList.add('hidden');

    if (items.length === 0) {
        if (errorText) errorText.textContent = 'Minimal pilih 1 layanan sebelum melanjutkan transaksi.';
        errorBanner?.classList.remove('hidden');
        return;
    }

    const cashReceived = parseFloat(document.getElementById('pay_cash_received')?.value || 0);
    if (selectedMethod === 'cash' && cashReceived < totals.remaining) {
        if (errorText) errorText.textContent = 'Nominal uang yang diterima kurang dari total sisa tagihan.';
        errorBanner?.classList.remove('hidden');
        return;
    }

    const payload = {
        branch_id: document.getElementById('pay_branch_id').value,
        booking_id: document.getElementById('pay_booking_id').value || null,
        order_id: document.getElementById('pay_order_id').value || null,
        customer_name: document.getElementById('pay_customer_name').value || 'Pelanggan Umum',
        customer_phone: document.getElementById('pay_customer_phone').value || null,
        payment_method: selectedMethod,
        cash_received: selectedMethod === 'cash' ? cashReceived : null,
        reference_number: document.getElementById('pay_reference_number')?.value || null,
        items: items.map(it => ({ service_id: it.service_id, qty: it.qty }))
    };

    btnSubmit.disabled = true;
    btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
    document.getElementById('cashier-pay-spinner')?.classList.remove('hidden');
    document.getElementById('cashier-pay-icon')?.classList.add('hidden');
    document.getElementById('cashier-pay-text').textContent = 'Memproses Pembayaran...';

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
        document.querySelector('input[name="_token"]')?.value;

    fetch('/pos/transactions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(payload)
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || (data.errors ? Object.values(data.errors).flat().join('<br>') : 'Gagal memproses transaksi.'));
        }
        return data;
    })
    .then(result => {
        if (result.success) {
            if (meta.type && meta.bookingId) {
                removeDashboardTableRow('appointment', meta.bookingId);
            } else if (meta.type && meta.orderId) {
                removeDashboardTableRow('drop_off', meta.orderId);
            }

            if (result.is_gateway && result.redirect_url) {
                window.location.href = result.redirect_url;
                return;
            }
            if (result.is_gateway && result.payment_url) {
                window.location.href = result.payment_url;
                return;
            }

            document.getElementById('cashier-pay-form')?.classList.add('hidden');
            const successBox = document.getElementById('pay-success-state');
            successBox?.classList.remove('hidden');

            document.getElementById('pay-success-code').textContent = result.transaction.code;
            document.getElementById('pay-success-total').textContent = formatRupiah(result.transaction.total);
            document.getElementById('pay-success-method').textContent = selectedMethod;
            document.getElementById('pay-success-receipt-btn').href = result.transaction.receipt_url;

            clearCartItems();
            if (typeof syncToPosCatalogCart === 'function') {
                syncToPosCatalogCart();
            }
        }
    })
    .catch(err => {
        btnSubmit.disabled = false;
        btnSubmit.classList.remove('opacity-75', 'cursor-not-allowed');
        document.getElementById('cashier-pay-spinner')?.classList.add('hidden');
        document.getElementById('cashier-pay-icon')?.classList.remove('hidden');
        document.getElementById('cashier-pay-text').textContent = 'Proses Pembayaran';

        if (errorText) errorText.innerHTML = err.message || 'Terjadi kesalahan pada transaksi.';
        errorBanner?.classList.remove('hidden');
    });
}

