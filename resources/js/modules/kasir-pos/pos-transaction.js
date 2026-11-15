/**
 * Modul Katalog Layanan & Interaksi POS SIMAUL
 * Menangani pemilihan tipe transaksi, pemuatan booking/drop-off, filter katalog,
 * dan sinkronisasi dengan modal keranjang & pembayaran terpadu.
 */

import {
    addCartItem,
    setCartItems,
    getCartItems,
    clearCartItems,
    calculateTotals,
    formatRupiah,
    setAvailableCatalog
} from './payment-cart.js';

import { openPosDirectPayment } from './payment-modal.js';

let activeSource = 'direct';
let currentSourceMeta = {
    bookingId: '',
    orderId: '',
    customerName: 'Pelanggan Umum',
    customerPhone: '',
    type: 'direct',
    typeLabel: 'Walk-in'
};

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
            el.className = 'py-2 px-3 rounded-md text-center text-[#667085] hover:text-[#101828] transition-all flex items-center justify-center gap-1.5 cursor-pointer';
        }
    });

    if (source === 'direct') {
        if (tabDirect) tabDirect.className = 'py-2 px-3 rounded-md text-center font-bold bg-white text-[#062072] shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer';
        if (badge) badge.innerText = 'Transaksi Langsung (Walk-in)';
        bookingContainer?.classList.add('hidden');
        orderContainer?.classList.add('hidden');
        currentSourceMeta = { bookingId: '', orderId: '', customerName: 'Pelanggan Umum', customerPhone: '', type: 'direct', typeLabel: 'Walk-in' };
    } else if (source === 'booking') {
        if (tabBooking) tabBooking.className = 'py-2 px-3 rounded-md text-center font-bold bg-white text-[#062072] shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer';
        if (badge) badge.innerText = 'Dari Booking Appointment';
        bookingContainer?.classList.remove('hidden');
        orderContainer?.classList.add('hidden');
    } else if (source === 'order') {
        if (tabOrder) tabOrder.className = 'py-2 px-3 rounded-md text-center font-bold bg-white text-[#062072] shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer';
        if (badge) badge.innerText = 'Dari Drop-off Pesanan';
        orderContainer?.classList.remove('hidden');
        bookingContainer?.classList.add('hidden');
    }
}

export function loadBookingData(bookingId) {
    if (!bookingId) {
        document.getElementById('booking-action-bar')?.classList.add('hidden');
        return;
    }

    fetch(`/pos/source-data?type=booking&id=${bookingId}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        currentSourceMeta = {
            bookingId: data.id,
            orderId: '',
            customerName: data.customer_name || 'Pelanggan Booking',
            customerPhone: data.customer_phone || '',
            type: 'appointment',
            typeLabel: 'Appointment'
        };

        if (data.items && data.items.length > 0) {
            setCartItems(data.items);
        } else {
            clearCartItems();
        }

        updateHeaderCartBadge();
        const actionBar = document.getElementById('booking-action-bar');
        actionBar?.classList.remove('hidden');
    })
    .catch(err => console.error('Gagal memuat booking:', err));
}

export function loadOrderData(orderId) {
    if (!orderId) {
        document.getElementById('order-action-bar')?.classList.add('hidden');
        return;
    }

    fetch(`/pos/source-data?type=order&id=${orderId}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        currentSourceMeta = {
            bookingId: '',
            orderId: data.id,
            customerName: data.customer_name || 'Pelanggan Drop-off',
            customerPhone: data.customer_phone || '',
            type: 'drop_off',
            typeLabel: 'Drop-off'
        };

        if (data.items && data.items.length > 0) {
            setCartItems(data.items);
        } else {
            clearCartItems();
        }

        updateHeaderCartBadge();
        const actionBar = document.getElementById('order-action-bar');
        actionBar?.classList.remove('hidden');
    })
    .catch(err => console.error('Gagal memuat order:', err));
}

export function addItem(id, name, price) {
    addCartItem(id, name, price, 1);
    updateHeaderCartBadge();

    // Visual feedback pada kartu layanan
    const cardBtn = document.querySelector(`.service-card[data-id="${id}"] button`);
    if (cardBtn) {
        cardBtn.classList.add('bg-emerald-600', 'text-white');
        setTimeout(() => {
            cardBtn.classList.remove('bg-emerald-600', 'text-white');
        }, 350);
    }
}

export function openCatalogCartModal() {
    const items = getCartItems();
    const branchEl = document.querySelector('[data-active-branch-id]');
    const branchId = branchEl ? branchEl.getAttribute('data-active-branch-id') : '';

    openPosDirectPayment(items, {
        branchId: branchId,
        bookingId: currentSourceMeta.bookingId,
        orderId: currentSourceMeta.orderId,
        customerName: currentSourceMeta.customerName,
        customerPhone: currentSourceMeta.customerPhone,
        type: currentSourceMeta.type,
        typeLabel: currentSourceMeta.typeLabel
    });
}

export function updateHeaderCartBadge() {
    const totals = calculateTotals();
    const countBadge = document.getElementById('pos-header-cart-count');
    const totalEl = document.getElementById('pos-header-cart-total');

    if (countBadge) countBadge.textContent = totals.itemCount;
    if (totalEl) totalEl.textContent = formatRupiah(totals.remaining);
}

export function filterServices() {
    const searchEl = document.getElementById('service-search');
    const query = (searchEl?.value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.service-card');
    cards.forEach(card => {
        const name = (card.getAttribute('data-name') || '').toLowerCase();
        card.style.display = (!query || name.includes(query)) ? 'flex' : 'none';
    });
}

export function filterCategory(catId) {
    document.querySelectorAll('.cat-btn').forEach(btn => {
        if (btn.getAttribute('data-cat') === String(catId)) {
            btn.className = 'cat-btn px-2.5 py-1 rounded-md font-semibold bg-[#062072] text-white shrink-0 cursor-pointer';
        } else {
            btn.className = 'cat-btn px-2.5 py-1 rounded-md font-medium text-[#667085] hover:bg-[#F5F8FC] hover:text-[#101828] border border-transparent shrink-0 cursor-pointer';
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

export function syncPosFromModalCart() {
    updateHeaderCartBadge();
}

// Inisialisasi event listeners katalog
export function initPosCatalogListeners() {
    document.getElementById('service-search')?.addEventListener('input', filterServices);
    document.getElementById('btn-open-cart-checkout')?.addEventListener('click', openCatalogCartModal);
    document.getElementById('btn-booking-checkout')?.addEventListener('click', openCatalogCartModal);
    document.getElementById('btn-order-checkout')?.addEventListener('click', openCatalogCartModal);

    // Kumpulkan layanan yang ter-render di katalog untuk caching search modal
    const services = [];
    document.querySelectorAll('.service-card').forEach(card => {
        services.push({
            id: Number(card.getAttribute('data-id')),
            name: card.getAttribute('data-name'),
            price: Number(card.getAttribute('data-price')),
            category_id: card.getAttribute('data-cat'),
            category_name: card.querySelector('.category-label')?.textContent?.trim() || 'Layanan'
        });
    });
    if (services.length > 0) {
        setAvailableCatalog(services);
    }
}

// Expose global
window.selectSource = selectSource;
window.loadBookingData = loadBookingData;
window.loadOrderData = loadOrderData;
window.addItem = addItem;
window.openCatalogCartModal = openCatalogCartModal;
window.filterServices = filterServices;
window.filterCategory = filterCategory;
window.syncPosFromModalCart = syncPosFromModalCart;
window.initPosCatalogListeners = initPosCatalogListeners;
