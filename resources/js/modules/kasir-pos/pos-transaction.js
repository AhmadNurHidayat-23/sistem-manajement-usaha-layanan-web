/**
 * Modul transaksi kasir POS: pengelolaan keranjang, pencarian katalog,
 * voucher diskon, dan kalkulasi pembayaran.
 */

let cart = [];
let activeSource = 'direct';

export function formatRupiah(num) {
    return 'Rp ' + Number(num).toLocaleString('id-ID');
}

export function selectSource(source) {
    activeSource = source;
    const badge = document.getElementById('source-badge');
    const bookingContainer = document.getElementById('booking-selector-container');
    const orderContainer = document.getElementById('order-selector-container');
    const tabDirect = document.getElementById('tab-direct');
    const tabBooking = document.getElementById('tab-booking');
    const tabOrder = document.getElementById('tab-order');

    [tabDirect, tabBooking, tabOrder].forEach(el => {
        if (el) {
            el.className = 'py-2 px-3 rounded-md text-center text-[#667085] hover:text-[#101828] transition-all flex items-center justify-center gap-1.5';
        }
    });

    if (source === 'direct') {
        if (tabDirect) tabDirect.className = 'py-2 px-3 rounded-md text-center font-bold bg-white text-[#062072] shadow-sm transition-all flex items-center justify-center gap-1.5';
        if (badge) badge.innerText = 'Transaksi Langsung';
        bookingContainer?.classList.add('hidden');
        orderContainer?.classList.add('hidden');
        const bInput = document.getElementById('booking_id');
        const oInput = document.getElementById('order_id');
        if (bInput) bInput.value = '';
        if (oInput) oInput.value = '';
    } else if (source === 'booking') {
        if (tabBooking) tabBooking.className = 'py-2 px-3 rounded-md text-center font-bold bg-white text-[#062072] shadow-sm transition-all flex items-center justify-center gap-1.5';
        if (badge) badge.innerText = 'Dari Booking Appointment';
        bookingContainer?.classList.remove('hidden');
        orderContainer?.classList.add('hidden');
        const oInput = document.getElementById('order_id');
        if (oInput) oInput.value = '';
    } else if (source === 'order') {
        if (tabOrder) tabOrder.className = 'py-2 px-3 rounded-md text-center font-bold bg-white text-[#062072] shadow-sm transition-all flex items-center justify-center gap-1.5';
        if (badge) badge.innerText = 'Dari Drop-off Pesanan';
        orderContainer?.classList.remove('hidden');
        bookingContainer?.classList.add('hidden');
        const bInput = document.getElementById('booking_id');
        if (bInput) bInput.value = '';
    }
}

export function loadBookingData(bookingId) {
    if (!bookingId) return;
    fetch(`/pos/source-data?type=booking&id=${bookingId}`)
        .then(res => res.json())
        .then(data => {
            const bInput = document.getElementById('booking_id');
            const oInput = document.getElementById('order_id');
            const cName = document.getElementById('customer_name');
            const cPhone = document.getElementById('customer_phone');

            if (bInput) bInput.value = data.id;
            if (oInput) oInput.value = '';
            if (cName) cName.value = data.customer_name || '';
            if (cPhone) cPhone.value = data.customer_phone || '';

            cart = [];
            if (data.items && data.items.length > 0) {
                data.items.forEach(item => {
                    cart.push({
                        service_id: item.service_id,
                        name: item.name,
                        price: Number(item.price),
                        qty: Number(item.qty) || 1
                    });
                });
            }
            renderCart();
        })
        .catch(err => console.error(err));
}

export function loadOrderData(orderId) {
    if (!orderId) return;
    fetch(`/pos/source-data?type=order&id=${orderId}`)
        .then(res => res.json())
        .then(data => {
            const oInput = document.getElementById('order_id');
            const bInput = document.getElementById('booking_id');
            const cName = document.getElementById('customer_name');
            const cPhone = document.getElementById('customer_phone');

            if (oInput) oInput.value = data.id;
            if (bInput) bInput.value = '';
            if (cName) cName.value = data.customer_name || '';
            if (cPhone) cPhone.value = data.customer_phone || '';

            cart = [];
            if (data.items && data.items.length > 0) {
                data.items.forEach(item => {
                    cart.push({
                        service_id: item.service_id,
                        name: item.name,
                        price: Number(item.price),
                        qty: Number(item.qty) || 1
                    });
                });
            }
            renderCart();
        })
        .catch(err => console.error(err));
}

export function filterServices() {
    const searchEl = document.getElementById('service-search');
    const query = (searchEl?.value || '').toLowerCase();
    const cards = document.querySelectorAll('.service-card');
    cards.forEach(card => {
        const name = (card.getAttribute('data-name') || '').toLowerCase();
        card.style.display = name.includes(query) ? 'flex' : 'none';
    });
}

export function filterCategory(catId) {
    document.querySelectorAll('.cat-btn').forEach(btn => {
        if (btn.getAttribute('data-cat') === String(catId)) {
            btn.className = 'cat-btn px-2.5 py-1 rounded-md font-semibold bg-[#062072] text-white shrink-0';
        } else {
            btn.className = 'cat-btn px-2.5 py-1 rounded-md font-medium text-[#667085] hover:bg-[#F5F8FC] hover:text-[#101828] border border-transparent shrink-0';
        }
    });

    const cards = document.querySelectorAll('.service-card');
    cards.forEach(card => {
        const cCat = card.getAttribute('data-cat');
        if (catId === 'all' || cCat === String(catId)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

export function addItem(id, name, price) {
    const existing = cart.find(i => i.service_id === id);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({
            service_id: id,
            name: name,
            price: Number(price),
            qty: 1
        });
    }
    renderCart();
}

export function updateQty(id, delta) {
    const item = cart.find(i => i.service_id === id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) {
        cart = cart.filter(i => i.service_id !== id);
    }
    renderCart();
}

export function removeItem(id) {
    cart = cart.filter(i => i.service_id !== id);
    renderCart();
}

export function clearCart() {
    cart = [];
    const promoCode = document.getElementById('applied_promo_code');
    const discount = document.getElementById('applied_discount');
    const promoStatus = document.getElementById('promo-status');
    const promoInput = document.getElementById('promo_code_input');

    if (promoCode) promoCode.value = '';
    if (discount) discount.value = '0';
    if (promoStatus) promoStatus.className = 'text-[11px] hidden font-semibold';
    if (promoInput) promoInput.value = '';
    renderCart();
}

export function getSubtotal() {
    return cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
}

export function renderCart() {
    const container = document.getElementById('cart-container');
    if (!container) return;
    container.innerHTML = '';

    if (cart.length === 0) {
        container.innerHTML = `
            <div id="empty-cart-msg" class="py-8 text-center text-xs text-[#667085]">
                Keranjang kosong. Pilih layanan di katalog sebelah kiri.
            </div>
        `;
    } else {
        cart.forEach((item, index) => {
            const sub = item.price * item.qty;
            const row = document.createElement('div');
            row.className = 'pt-2 flex items-center justify-between gap-2 text-xs';
            row.innerHTML = `
                <div class="flex-1 pr-2">
                    <div class="font-bold text-[#101828] line-clamp-1">${item.name}</div>
                    <div class="text-[11px] text-[#667085]">${formatRupiah(item.price)}</div>
                    <input type="hidden" name="items[${index}][service_id]" value="${item.service_id}">
                    <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                    <input type="hidden" name="items[${index}][price]" value="${item.price}">
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="updateQty(${item.service_id}, -1)" class="w-6 h-6 rounded bg-[#F5F8FC] border border-[#E4E7EC] hover:bg-[#E4E7EC] flex items-center justify-center font-bold text-[#101828]">-</button>
                    <span class="w-5 text-center font-bold text-[#101828]">${item.qty}</span>
                    <button type="button" onclick="updateQty(${item.service_id}, 1)" class="w-6 h-6 rounded bg-[#F5F8FC] border border-[#E4E7EC] hover:bg-[#E4E7EC] flex items-center justify-center font-bold text-[#101828]">+</button>
                </div>
                <div class="w-20 text-right font-bold text-[#062072]">
                    ${formatRupiah(sub)}
                </div>
                <button type="button" onclick="removeItem(${item.service_id})" class="text-[#667085] hover:text-rose-600 transition-colors p-1" title="Hapus">
                    &times;
                </button>
            `;
            container.appendChild(row);
        });
    }

    const subtotal = getSubtotal();
    const discount = Number(document.getElementById('applied_discount')?.value) || 0;
    const total = Math.max(0, subtotal - discount);

    const subEl = document.getElementById('summary-subtotal');
    const discEl = document.getElementById('summary-discount');
    const totEl = document.getElementById('summary-total');

    if (subEl) subEl.innerText = formatRupiah(subtotal);
    if (discEl) discEl.innerText = '- ' + formatRupiah(discount);
    if (totEl) totEl.innerText = formatRupiah(total);

    calculateChange();
}

export function applyPromoCode() {
    const code = document.getElementById('promo_code_input')?.value.trim();
    const subtotal = getSubtotal();
    const branchEl = document.querySelector('[data-active-branch-id]');
    const branchId = branchEl ? branchEl.getAttribute('data-active-branch-id') : '';
    const statusEl = document.getElementById('promo-status');

    if (!code) {
        if (statusEl) {
            statusEl.className = 'text-[11px] block font-semibold text-rose-600';
            statusEl.innerText = 'Silakan ketikkan kode promo terlebih dahulu.';
        }
        return;
    }

    if (subtotal <= 0) {
        if (statusEl) {
            statusEl.className = 'text-[11px] block font-semibold text-rose-600';
            statusEl.innerText = 'Keranjang masih kosong. Tambah item dahulu.';
        }
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const validateUrl = branchEl?.getAttribute('data-validate-promo-url') || '/promotions/validate';

    fetch(validateUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({
            code: code,
            amount: subtotal,
            branch_id: branchId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.valid) {
            const discount = Number(data.discount_amount) || 0;
            const appliedCode = document.getElementById('applied_promo_code');
            const appliedDisc = document.getElementById('applied_discount');
            if (appliedCode) appliedCode.value = code;
            if (appliedDisc) appliedDisc.value = discount;

            if (statusEl) {
                statusEl.className = 'text-[11px] block font-semibold text-emerald-600';
                statusEl.innerText = `Promo ${code} aktif! Hemat ${formatRupiah(discount)}`;
            }
        } else {
            const appliedCode = document.getElementById('applied_promo_code');
            const appliedDisc = document.getElementById('applied_discount');
            if (appliedCode) appliedCode.value = '';
            if (appliedDisc) appliedDisc.value = 0;

            if (statusEl) {
                statusEl.className = 'text-[11px] block font-semibold text-rose-600';
                statusEl.innerText = data.message || 'Kode promo tidak valid atau syarat belanja belum tercapai.';
            }
        }
        renderCart();
    })
    .catch(err => {
        console.error(err);
        if (statusEl) {
            statusEl.className = 'text-[11px] block font-semibold text-rose-600';
            statusEl.innerText = 'Gagal memvalidasi kode promo.';
        }
    });
}

export function togglePaymentFields(method) {
    const cashDiv = document.getElementById('cash-fields');
    const nonCashDiv = document.getElementById('non-cash-fields');

    if (method === 'cash') {
        cashDiv?.classList.remove('hidden');
        nonCashDiv?.classList.add('hidden');
    } else {
        cashDiv?.classList.add('hidden');
        nonCashDiv?.classList.remove('hidden');
    }
}

export function setExactCash() {
    const subtotal = getSubtotal();
    const discount = Number(document.getElementById('applied_discount')?.value) || 0;
    const total = Math.max(0, subtotal - discount);
    const cashInput = document.getElementById('cash_received');
    if (cashInput) {
        cashInput.value = total;
    }
    calculateChange();
}

export function addCash(amount) {
    const cashInput = document.getElementById('cash_received');
    if (!cashInput) return;
    const current = Number(cashInput.value) || 0;
    cashInput.value = current + amount;
    calculateChange();
}

export function calculateChange() {
    const subtotal = getSubtotal();
    const discount = Number(document.getElementById('applied_discount')?.value) || 0;
    const total = Math.max(0, subtotal - discount);
    const cashReceived = Number(document.getElementById('cash_received')?.value) || 0;
    const change = cashReceived - total;

    const changeDisplay = document.getElementById('change-display');
    if (!changeDisplay) return;

    if (change >= 0) {
        changeDisplay.className = 'font-bold text-emerald-700 text-sm';
        changeDisplay.innerText = formatRupiah(change);
    } else {
        changeDisplay.className = 'font-bold text-rose-600 text-sm';
        changeDisplay.innerText = 'Kurang ' + formatRupiah(Math.abs(change));
    }
}

// Bind secara global untuk inline event handler Blade
if (typeof window !== 'undefined') {
    window.selectSource = selectSource;
    window.loadBookingData = loadBookingData;
    window.loadOrderData = loadOrderData;
    window.filterServices = filterServices;
    window.filterCategory = filterCategory;
    window.addItem = addItem;
    window.updateQty = updateQty;
    window.removeItem = removeItem;
    window.clearCart = clearCart;
    window.applyPromoCode = applyPromoCode;
    window.togglePaymentFields = togglePaymentFields;
    window.setExactCash = setExactCash;
    window.addCash = addCash;
    window.calculateChange = calculateChange;
    window.formatRupiah = formatRupiah;
}
