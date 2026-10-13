import { initCustomSelects } from './components/custom-select.js';
import { initCustomDatePickers } from './components/custom-date-picker.js';
import { initModalListeners } from './components/modal.js';
import { initStarRating } from './components/star-rating.js';
import { initCustomerNavbar } from './customer/navbar.js';
import { initCustomerDashboard } from './customer/dashboard.js';
import './modules/membership-promo/promo.js';
import { initBookingForm } from './modules/booking-antrian/booking.js';
import { initPesananCreate } from './modules/booking-antrian/pesanan.js';
import { restoreSidebarState, initSidebarListeners } from './internal/sidebar.js';
import { initHeaderClock } from './internal/header-clock.js';
import './modules/kasir-pos/kasir-shift.js';
import './modules/kasir-pos/pos-transaction.js';
import './modules/karyawan/employee-skills.js';
import { initPaymentPolling } from './modules/kasir-pos/payment-polling.js';
import { initShiftPageListeners, initPosQueryLoader } from './modules/kasir-pos/shift-page.js';
import { initDashboardTable } from './modules/kasir-pos/dashboard-table.js';
import { initReportFilter } from './modules/laporan/filter.js';
import { initNotificationBell } from './modules/notifications/bell-poll.js';
import { initBranchModalListeners } from './modules/cabang/branch-modal.js';
import { initUserModalListeners } from './modules/users/user-modal.js';
import { initEmployeeModalListeners } from './modules/karyawan/employee-modal.js';
import { initInventoryModalListeners } from './modules/inventori/inventory-modal.js';
import { initServiceModalListeners } from './modules/katalog/service-modal.js';
import { initReceiptWaHandlers } from './modules/kasir-pos/receipt.js';
import './landing/carousel.js';

// Expose fungsi ke window untuk aksesibilitas global dan inline handler
window.initCustomSelects = initCustomSelects;
window.initCustomDatePickers = initCustomDatePickers;
window.initCustomerNavbar = initCustomerNavbar;
window.initCustomerDashboard = initCustomerDashboard;
window.initStarRating = initStarRating;
window.initBookingForm = initBookingForm;
window.initPesananCreate = initPesananCreate;
window.initPaymentPolling = initPaymentPolling;
window.initHeaderClock = initHeaderClock;
window.initDashboardTable = initDashboardTable;
window.initReportFilter = initReportFilter;
window.initReceiptWaHandlers = initReceiptWaHandlers;

export function initApp(root = document) {
    initCustomSelects(root);
    initCustomDatePickers(root);
    initModalListeners(root);
    initCustomerNavbar();
    initCustomerDashboard(root);
    initStarRating(root);
    restoreSidebarState();
    initSidebarListeners(root);
    initHeaderClock();
    initBookingForm();
    initPesananCreate();
    initPaymentPolling();
    initShiftPageListeners();
    initPosQueryLoader();
    initDashboardTable();
    initReportFilter(root);
    initNotificationBell();
    initBranchModalListeners(root);
    initUserModalListeners(root);
    initEmployeeModalListeners(root);
    initInventoryModalListeners(root);
    initServiceModalListeners(root);
    initReceiptWaHandlers(root);
}

window.initAllPickers = initApp;

// Inisialisasi saat DOM siap
if (typeof document !== 'undefined') {
    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-action="print"]') || e.target.closest('#btn-print-receipt')) {
            window.print();
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initApp());
    } else {
        initApp();
    }

    // Safe scoped observer untuk node baru yang disisipkan secara dinamis (mis. AJAX)
    if (typeof MutationObserver !== 'undefined') {
        let isMutatingSelf = false;
        let mutationRaf = null;
        const pendingRoots = new Set();

        const observer = new MutationObserver((mutations) => {
            if (isMutatingSelf) return;

            for (const mutation of mutations) {
                if (mutation.type === 'childList') {
                    for (const node of mutation.addedNodes) {
                        if (node.nodeType === 1) {
                            if (node.matches?.('.custom-select-container, .custom-date-container, .custom-date-panel') ||
                                node.closest?.('.custom-select-container, .custom-date-container')) {
                                continue;
                            }
                            if (node.tagName === 'SELECT' || node.tagName === 'INPUT' || node.querySelector?.('select, input[type="date"], [data-modal-open]')) {
                                pendingRoots.add(node);
                            }
                        }
                    }
                }
            }

            if (pendingRoots.size > 0 && !mutationRaf) {
                mutationRaf = requestAnimationFrame(() => {
                    isMutatingSelf = true;
                    try {
                        pendingRoots.forEach(rootEl => {
                            if (document.body.contains(rootEl)) {
                                initCustomSelects(rootEl);
                                initCustomDatePickers(rootEl);
                                initModalListeners(rootEl);
                                initBranchModalListeners(rootEl);
                                initUserModalListeners(rootEl);
                                initEmployeeModalListeners(rootEl);
                                initInventoryModalListeners(rootEl);
                                initServiceModalListeners(rootEl);
                            }
                        });
                    } finally {
                        pendingRoots.clear();
                        isMutatingSelf = false;
                        mutationRaf = null;
                    }
                });
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
}
