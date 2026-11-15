/**
 * Modul Interaksi & Transaksi Modal Pembayaran Kasir SIMAUL
 * Terhubung dengan payment-cart.js, payment-submit.js, dan endpoint /pos/transactions
 */

import {
    formatRupiah, getCartItems, setCartItems, addCartItem, updateCartItemQty,
    removeCartItem, clearCartItems, setDiscount, setPreviousPaid,
    calculateTotals, renderCartItemsUI, searchCatalogServices
} from './payment-cart.js';

import {
    togglePaymentMethodFields, calculateCashChange, handlePaymentSubmit
} from './payment-submit.js';

let transactionMeta = {
    branchId: '', bookingId: '', orderId: '',
    customerName: 'Pelanggan Umum', customerPhone: '',
    type: 'direct', typeLabel: 'Walk-in', branchName: '-'
};

const paymentState = { selectedMethod: 'cash' };

export function openCashierPaymentModal(btn) {
    const modal = document.getElementById('modal-pembayaran-kasir');
    if (!modal) return;

    resetModalForms();

    const id = btn.getAttribute('data-id') || '';
    const type = btn.getAttribute('data-type') || 'direct';
    const serviceId = btn.getAttribute('data-service-id') || '';
    const serviceName = btn.getAttribute('data-service-name') || 'Layanan';
    const totalAmount = parseFloat(btn.getAttribute('data-total-amount') || '0');
    const paidAmount = parseFloat(btn.getAttribute('data-paid-amount') || '0');
    const branchId = btn.getAttribute('data-branch-id') || '';
    const branchName = btn.getAttribute('data-branch-name') || '-';
    const customerName = btn.getAttribute('data-customer-name') || 'Pelanggan Umum';

    transactionMeta = {
        branchId: branchId,
        bookingId: type === 'appointment' ? id : '',
        orderId: type === 'drop_off' ? id : '',
        customerName: customerName,
        customerPhone: '',
        type: type,
        typeLabel: type === 'appointment' ? 'Appointment' : (type === 'drop_off' ? 'Drop-off' : 'Walk-in'),
        branchName: branchName
    };

    setPreviousPaid(paidAmount);
    setDiscount(0);

    if (serviceId) {
        setCartItems([{ service_id: Number(serviceId), name: serviceName, price: totalAmount, qty: 1 }]);
    } else {
        clearCartItems();
    }

    syncMetaToUI();
    refreshModalUI();

    showModal(modal);

    if (id && (type === 'appointment' || type === 'drop_off')) {
        fetchSourceData(type, id);
    }
}

export function openPosDirectPayment(items, meta = {}) {
    const modal = document.getElementById('modal-pembayaran-kasir');
    if (!modal) return;

    resetModalForms();

    transactionMeta = {
        branchId: meta.branchId || document.querySelector('[data-active-branch-id]')?.getAttribute('data-active-branch-id') || '',
        bookingId: meta.bookingId || '',
        orderId: meta.orderId || '',
        customerName: meta.customerName || 'Pelanggan Umum',
        customerPhone: meta.customerPhone || '',
        type: meta.type || 'direct',
        typeLabel: meta.typeLabel || 'Walk-in',
        branchName: meta.branchName || '-'
    };

    setPreviousPaid(meta.paidAmount || 0);
    setDiscount(meta.discount || 0);

    if (items && items.length > 0) {
        setCartItems(items);
    }

    syncMetaToUI();
    refreshModalUI();
    showModal(modal);
}

function showModal(modal) {
    if (typeof window.openModal === 'function') {
        window.openModal('modal-pembayaran-kasir');
    } else {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    requestAnimationFrame(() => modal.classList.add('is-open'));
}

export function closeCashierPaymentModal() {
    const modal = document.getElementById('modal-pembayaran-kasir');
    if (!modal) return;

    modal.classList.remove('is-open');
    if (typeof window.closeModal === 'function') {
        window.closeModal('modal-pembayaran-kasir');
    } else {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
    resetModalForms();
}

function resetModalForms() {
    document.getElementById('cashier-pay-form')?.reset();
    document.getElementById('cashier-pay-form')?.classList.remove('hidden');
    document.getElementById('pay-success-state')?.classList.add('hidden');
    document.getElementById('pay-error-banner')?.classList.add('hidden');
    document.getElementById('pay-add-service-panel')?.classList.add('hidden');

    const defaultRadio = document.querySelector('input[name="pay_payment_method"][value="cash"]');
    if (defaultRadio) defaultRadio.checked = true;
    paymentState.selectedMethod = 'cash';
    togglePaymentMethodFields('cash', paymentState);
}

function syncMetaToUI() {
    const customerDisplay = document.getElementById('pay-display-customer');
    if (customerDisplay) customerDisplay.textContent = transactionMeta.customerName;

    const phoneDisplay = document.getElementById('pay-display-phone');
    if (phoneDisplay) phoneDisplay.textContent = transactionMeta.customerPhone || '-';

    const typeBadge = document.getElementById('pay-display-type');
    if (typeBadge) typeBadge.textContent = transactionMeta.typeLabel;

    const branchDisplay = document.getElementById('pay-display-branch');
    if (branchDisplay) branchDisplay.textContent = transactionMeta.branchName || '-';

    document.getElementById('pay_branch_id').value = transactionMeta.branchId;
    document.getElementById('pay_booking_id').value = transactionMeta.bookingId;
    document.getElementById('pay_order_id').value = transactionMeta.orderId;
    document.getElementById('pay_customer_name').value = transactionMeta.customerName;
    document.getElementById('pay_customer_phone').value = transactionMeta.customerPhone;
}

export function refreshModalUI() {
    const container = document.getElementById('pay-items-list');
    renderCartItemsUI(container, handleQtyChange, handleRemoveItem);

    const totals = calculateTotals();
    document.getElementById('pay-calc-subtotal').textContent = formatRupiah(totals.subtotal);
    document.getElementById('pay-calc-remaining').textContent = formatRupiah(totals.remaining);

    const discountRow = document.getElementById('pay-row-discount');
    if (totals.discount > 0) {
        discountRow?.classList.remove('hidden');
        document.getElementById('pay-calc-discount').textContent = '- ' + formatRupiah(totals.discount);
    } else {
        discountRow?.classList.add('hidden');
    }

    const paidRow = document.getElementById('pay-row-paid');
    if (totals.paid > 0) {
        paidRow?.classList.remove('hidden');
        document.getElementById('pay-calc-paid').textContent = '- ' + formatRupiah(totals.paid);
    } else {
        paidRow?.classList.add('hidden');
    }

    const cashInput = document.getElementById('pay_cash_received');
    if (cashInput && (Number(cashInput.value) === 0 || Number(cashInput.value) < totals.remaining)) {
        cashInput.value = totals.remaining;
    }
    calculateCashChange(paymentState.selectedMethod);
}

function handleQtyChange(serviceId, delta) {
    updateCartItemQty(serviceId, delta);
    refreshModalUI();
    syncToPosCatalogCart();
}

function handleRemoveItem(serviceId) {
    removeCartItem(serviceId);
    refreshModalUI();
    syncToPosCatalogCart();
}

function syncToPosCatalogCart() {
    if (typeof window.syncPosFromModalCart === 'function') {
        window.syncPosFromModalCart(getCartItems());
    }
}

function fetchSourceData(type, id) {
    const loading = document.getElementById('pay-items-loading');
    loading?.classList.remove('hidden');

    const sourceType = type === 'appointment' ? 'booking' : 'order';
    fetch(`/pos/source-data?type=${sourceType}&id=${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        loading?.classList.add('hidden');
        if (data && data.items && data.items.length > 0) {
            setCartItems(data.items);
            if (data.customer_phone) {
                transactionMeta.customerPhone = data.customer_phone;
                document.getElementById('pay-display-phone').textContent = data.customer_phone;
                document.getElementById('pay_customer_phone').value = data.customer_phone;
            }
            if (data.customer_name) {
                transactionMeta.customerName = data.customer_name;
                document.getElementById('pay-display-customer').textContent = data.customer_name;
                document.getElementById('pay_customer_name').value = data.customer_name;
            }
            refreshModalUI();
        }
    })
    .catch(() => loading?.classList.add('hidden'));
}

function handleAddServiceSearch(e) {
    const query = e.target.value;
    const resultsContainer = document.getElementById('pay-service-search-results');
    if (!resultsContainer) return;

    searchCatalogServices(query, transactionMeta.branchId).then(services => {
        resultsContainer.innerHTML = '';
        if (services.length === 0) {
            resultsContainer.innerHTML = '<div class="p-3 text-center text-[#667085]">Tidak ada layanan ditemukan.</div>';
            return;
        }

        services.forEach(srv => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'w-full text-left p-2.5 hover:bg-[#F5F8FC] flex items-center justify-between gap-2 transition-colors cursor-pointer border-b border-[#E4E7EC] last:border-b-0';
            item.innerHTML = `
                <div>
                    <span class="font-bold text-[#101828] block">${srv.name}</span>
                    <span class="text-[10px] text-[#667085] uppercase">${srv.category_name}</span>
                </div>
                <div class="text-right">
                    <span class="font-extrabold text-[#062072] block">${formatRupiah(srv.price)}</span>
                    <span class="text-[10px] font-bold text-emerald-600">+ Tambah</span>
                </div>
            `;
            item.addEventListener('click', () => {
                addCartItem(srv.id, srv.name, srv.price, 1);
                refreshModalUI();
                syncToPosCatalogCart();
                document.getElementById('pay-add-service-panel')?.classList.add('hidden');
            });
            resultsContainer.appendChild(item);
        });
    });
}

export function initPaymentModal() {
    const modal = document.getElementById('modal-pembayaran-kasir');
    if (!modal) return;

    document.getElementById('btn-close-pay-modal')?.addEventListener('click', closeCashierPaymentModal);
    document.getElementById('btn-cancel-pay-modal')?.addEventListener('click', closeCashierPaymentModal);
    document.getElementById('btn-success-close-modal')?.addEventListener('click', closeCashierPaymentModal);

    modal.addEventListener('click', e => {
        if (e.target === modal) closeCashierPaymentModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeCashierPaymentModal();
        }
    });

    document.querySelectorAll('input[name="pay_payment_method"]').forEach(radio => {
        radio.addEventListener('change', e => togglePaymentMethodFields(e.target.value, paymentState));
    });

    document.getElementById('pay_cash_received')?.addEventListener('input', () => calculateCashChange(paymentState.selectedMethod));

    document.querySelectorAll('[data-cash-action]').forEach(btn => {
        btn.addEventListener('click', () => {
            const act = btn.getAttribute('data-cash-action');
            const cashInput = document.getElementById('pay_cash_received');
            if (!cashInput) return;
            if (act === 'exact') {
                cashInput.value = calculateTotals().remaining;
            } else if (act === 'add') {
                const addVal = parseFloat(btn.getAttribute('data-cash-val') || 0);
                cashInput.value = (parseFloat(cashInput.value) || 0) + addVal;
            }
            calculateCashChange(paymentState.selectedMethod);
        });
    });

    document.getElementById('btn-toggle-add-service')?.addEventListener('click', () => {
        const panel = document.getElementById('pay-add-service-panel');
        if (!panel) return;
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) {
            const input = document.getElementById('pay-service-search-input');
            input?.focus();
            searchCatalogServices('', transactionMeta.branchId).then(() => {
                input?.dispatchEvent(new Event('input'));
            });
        }
    });

    document.getElementById('btn-close-add-service')?.addEventListener('click', () => {
        document.getElementById('pay-add-service-panel')?.classList.add('hidden');
    });

    document.getElementById('pay-service-search-input')?.addEventListener('input', handleAddServiceSearch);
    document.getElementById('cashier-pay-form')?.addEventListener('submit', (e) => {
        handlePaymentSubmit(e, transactionMeta, paymentState.selectedMethod, syncToPosCatalogCart);
    });

    document.querySelectorAll('[data-action="open-payment-modal"]').forEach(btn => {
        btn.addEventListener('click', () => openCashierPaymentModal(btn));
    });
}

window.openCashierPaymentModal = openCashierPaymentModal;
window.closeCashierPaymentModal = closeCashierPaymentModal;
window.openPosDirectPayment = openPosDirectPayment;
window.initPaymentModal = initPaymentModal;
