import { initCustomSelects } from './components/custom-select.js';
import { initCustomDatePickers } from './components/custom-date-picker.js';
import { initCustomTimePickers, closeAllTimePickers } from './components/custom-time-picker.js';
import { initModalListeners } from './components/modal.js';
import { initStarRating } from './components/star-rating.js';
import { initCustomerNavbar } from './customer/navbar.js';
import { initCustomerDashboard } from './customer/dashboard.js';
import './modules/membership-promo/promo.js';
import { initBookingForm } from './modules/booking-antrian/booking.js';
import { initPesananCreate } from './modules/booking-antrian/pesanan.js';
import { restoreSidebarState, initSidebarListeners } from './internal/sidebar.js';
import { initHeaderClock } from './internal/header-clock.js';
import { initProfileDropdown } from './internal/profile-dropdown.js';
import { initLinkPrefetch } from './internal/link-prefetch.js';
import { initNavProgress } from './internal/nav-progress.js';
import './modules/kasir-pos/kasir-shift.js';
import { initPosCatalogListeners } from './modules/kasir-pos/pos-transaction.js';
import './modules/karyawan/employee-skills.js';
import { initPaymentPolling } from './modules/kasir-pos/payment-polling.js';
import { initShiftPageListeners, initPosQueryLoader } from './modules/kasir-pos/shift-page.js';
import { initDashboardTable } from './modules/kasir-pos/dashboard-table.js';
import { initReportFilter } from './modules/laporan/filter.js';
import { initNotificationBell } from './modules/notifications/bell-poll.js';
import { initBranchModalListeners } from './modules/cabang/branch-modal.js';
import { initUserModalListeners } from './modules/users/user-modal.js';
import { initEmployeeModalListeners } from './modules/karyawan/employee-modal.js';
import { initShiftModalListeners } from './modules/karyawan/shift-modal.js';
import { initInventoryModalListeners } from './modules/inventori/inventory-modal.js';
import { initServiceModalListeners } from './modules/katalog/service-modal.js';
import { initReceiptWaHandlers } from './modules/kasir-pos/receipt.js';
import { initPaymentModal } from './modules/kasir-pos/payment-modal.js';
import { initTaskModalListeners } from './modules/karyawan/task-modal.js';
import { initManagerBookingCancel } from './modules/manager/booking-cancel.js';
import { initCommissionFormListeners } from './modules/manager/commission-form.js';
import { initCustomerBookingCancel } from './modules/booking-antrian/customer-booking-cancel.js';
import { initCustomerBookingCreate } from './modules/booking-antrian/customer-booking-create.js';
import { initWeightModal } from './modules/kasir-pos/weight-modal.js';
import { initReceiveModal } from './modules/kasir-pos/receive-modal.js';
import { initCustomerDelivery } from './modules/booking-antrian/customer-delivery.js';
import './landing/carousel.js';

// Expose fungsi ke window untuk aksesibilitas global dan inline handler
window.initCustomSelects = initCustomSelects;
window.initCustomDatePickers = initCustomDatePickers;
window.initCustomTimePickers = initCustomTimePickers;
window.closeAllTimePickers = closeAllTimePickers;
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
window.initProfileDropdown = initProfileDropdown;
window.initNavProgress = initNavProgress;
window.initShiftModalListeners = initShiftModalListeners;

export function initApp(root = document) {
    initNavProgress();
    initCustomSelects(root);
    initCustomDatePickers(root);
    initCustomTimePickers(root);
    initModalListeners(root);
    initCustomerNavbar();
    initCustomerDashboard(root);
    initStarRating(root);
    restoreSidebarState();
    initSidebarListeners(root);
    initHeaderClock();
    initProfileDropdown(root);
    initLinkPrefetch();
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
    initShiftModalListeners(root);
    initInventoryModalListeners(root);
    initServiceModalListeners(root);
    initReceiptWaHandlers(root);
    initPaymentModal();
    initPosCatalogListeners();
    initTaskModalListeners();
    initManagerBookingCancel();
    initCommissionFormListeners();
    initCustomerBookingCancel();
    initCustomerBookingCreate();
    initWeightModal();
    initReceiveModal();
    initCustomerDelivery();
}

window.initAllPickers = initApp;

// Inisialisasi saat DOM siap
if (typeof document !== 'undefined') {
    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-action="print"]') || e.target.closest('#btn-print-receipt')) {
            window.print();
        }
    });

    const onReady = () => {
        initApp();
        setupDynamicMutationObserver();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }

    // Safe scoped observer untuk node baru yang disisipkan secara dinamis (mis. AJAX/Modal)
    function setupDynamicMutationObserver() {
        if (typeof MutationObserver === 'undefined') return;

        let isMutatingSelf = false;
        let mutationRaf = null;
        const pendingRoots = new Set();

        const observer = new MutationObserver((mutations) => {
            if (isMutatingSelf) return;

            for (const mutation of mutations) {
                if (mutation.type === 'childList') {
                    for (const node of mutation.addedNodes) {
                        if (node.nodeType === 1) {
                            if (node.matches?.('.custom-select-container, .custom-date-container, .custom-date-panel, .custom-time-container, .custom-time-panel') ||
                                node.closest?.('.custom-select-container, .custom-date-container, .custom-time-container')) {
                                continue;
                            }
                            if (node.tagName === 'SELECT' || node.tagName === 'INPUT' || node.querySelector?.('select, input[type="date"], input[type="time"], input[data-custom-time-picker], [data-modal-open], [data-action="edit-shift"]')) {
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
                                initCustomTimePickers(rootEl);
                                initModalListeners(rootEl);
                                initBranchModalListeners(rootEl);
                                initUserModalListeners(rootEl);
                                initEmployeeModalListeners(rootEl);
                                initShiftModalListeners(rootEl);
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
