/**
 * Modul Manajemen Keranjang Layanan Kasir & POS SIMAUL
 * Bertanggung jawab atas pengelolaan item, kuantitas, kalkulasi, dan pencarian layanan.
 */

let cartItems = [];
let availableCatalog = [];
let discountValue = 0;
let previousPaidValue = 0;

export function formatRupiah(num) {
    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
}

export function setAvailableCatalog(services) {
    if (Array.isArray(services)) {
        availableCatalog = services;
    }
}

export function getAvailableCatalog() {
    return availableCatalog;
}

export function getCartItems() {
    return cartItems;
}

export function setCartItems(items) {
    cartItems = Array.isArray(items) ? items.map(it => ({
        service_id: Number(it.service_id || it.id),
        name: String(it.name || 'Layanan'),
        price: Number(it.price || 0),
        qty: Math.max(0.01, Number(it.qty || 1)),
        is_fixed: Boolean(it.is_fixed)
    })) : [];
}

export function addCartItem(serviceId, name, price, qty = 1, isFixed = false) {
    const id = Number(serviceId);
    const existing = cartItems.find(i => i.service_id === id);
    if (existing) {
        existing.qty += qty;
    } else {
        cartItems.push({
            service_id: id,
            name: name,
            price: Number(price || 0),
            qty: Math.max(1, Number(qty || 1)),
            is_fixed: isFixed
        });
    }
}

export function updateCartItemQty(serviceId, delta) {
    const id = Number(serviceId);
    const item = cartItems.find(i => i.service_id === id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) {
        removeCartItem(id);
    }
}

export function removeCartItem(serviceId) {
    const id = Number(serviceId);
    cartItems = cartItems.filter(i => i.service_id !== id);
}

export function clearCartItems() {
    cartItems = [];
    discountValue = 0;
    previousPaidValue = 0;
}

export function setDiscount(val) {
    discountValue = Math.max(0, Number(val || 0));
}

export function getDiscount() {
    return discountValue;
}

export function setPreviousPaid(val) {
    previousPaidValue = Math.max(0, Number(val || 0));
}

export function getPreviousPaid() {
    return previousPaidValue;
}

export function calculateTotals() {
    const subtotal = cartItems.reduce((acc, it) => acc + (it.price * it.qty), 0);
    const discount = Math.min(subtotal, discountValue);
    const afterDiscount = Math.max(0, subtotal - discount);
    const remaining = Math.max(0, afterDiscount - previousPaidValue);

    return {
        subtotal: subtotal,
        discount: discount,
        paid: previousPaidValue,
        remaining: remaining,
        itemCount: cartItems.reduce((acc, it) => acc + it.qty, 0)
    };
}

export function renderCartItemsUI(containerEl, onQtyChange, onRemove) {
    if (!containerEl) return;
    containerEl.innerHTML = '';

    if (cartItems.length === 0) {
        return;
    }

    cartItems.forEach(item => {
        const itemSubtotal = item.price * item.qty;
        const row = document.createElement('div');
        row.className = 'py-2 flex items-center justify-between gap-2 text-xs border-b border-[#E4E7EC]/60 last:border-b-0';
        row.innerHTML = `
            <div class="flex-1 min-w-0 pr-2">
                <span class="font-bold text-[#101828] block truncate">${item.name}</span>
                <span class="text-[11px] text-[#667085]">${formatRupiah(item.price)} / unit</span>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" data-action="decrease-qty" data-id="${item.service_id}" class="w-6 h-6 rounded bg-[#F5F8FC] border border-[#D0D5DD] hover:bg-[#E4E7EC] flex items-center justify-center font-bold text-[#101828] cursor-pointer">-</button>
                <span class="w-6 text-center font-extrabold text-xs text-[#101828]">${item.qty}</span>
                <button type="button" data-action="increase-qty" data-id="${item.service_id}" class="w-6 h-6 rounded bg-[#F5F8FC] border border-[#D0D5DD] hover:bg-[#E4E7EC] flex items-center justify-center font-bold text-[#101828] cursor-pointer">+</button>
            </div>
            <div class="w-20 text-right font-extrabold text-[#062072] shrink-0">
                ${formatRupiah(itemSubtotal)}
            </div>
            <button type="button" data-action="remove-item" data-id="${item.service_id}" class="text-[#667085] hover:text-rose-600 transition-colors p-1 cursor-pointer shrink-0" title="Hapus layanan">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        `;

        row.querySelector('[data-action="decrease-qty"]')?.addEventListener('click', () => onQtyChange(item.service_id, -1));
        row.querySelector('[data-action="increase-qty"]')?.addEventListener('click', () => onQtyChange(item.service_id, 1));
        row.querySelector('[data-action="remove-item"]')?.addEventListener('click', () => onRemove(item.service_id));

        containerEl.appendChild(row);
    });
}

export function searchCatalogServices(query, branchId = null) {
    const q = String(query || '').trim().toLowerCase();
    if (availableCatalog.length > 0) {
        return Promise.resolve(
            availableCatalog.filter(s => !q || s.name.toLowerCase().includes(q) || (s.category_name && s.category_name.toLowerCase().includes(q)))
        );
    }

    const url = branchId ? `/pos/services?branch_id=${branchId}` : '/pos/services';
    return fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            if (Array.isArray(data)) {
                availableCatalog = data;
                return availableCatalog.filter(s => !q || s.name.toLowerCase().includes(q) || (s.category_name && s.category_name.toLowerCase().includes(q)));
            }
            return [];
        })
        .catch(err => {
            console.warn('Gagal memuat katalog layanan:', err);
            return [];
        });
}

