/**
 * Interaktivitas tabel Pembayaran yang Perlu Ditangani pada Dashboard Kasir.
 * Mendukung filter instan (Semua, Appointment, Drop-off) dan sinkronisasi URL.
 */

export function initDashboardTable() {
    const card = document.getElementById('dashboard-payment-card');
    if (!card) return;

    const tabButtons = card.querySelectorAll('.payment-tab-btn');
    const rows = card.querySelectorAll('.payment-row');
    const emptyState = document.getElementById('payment-empty-state');
    const emptyText = document.getElementById('payment-empty-text');

    const emptyMessages = {
        all: 'Tidak ada pembayaran yang perlu ditangani.',
        appointment: 'Tidak ada pembayaran Appointment yang perlu ditangani.',
        drop_off: 'Tidak ada pembayaran Drop-off yang perlu ditangani.',
    };

    function applyFilter(filter, updateHistory = true) {
        let visibleCount = 0;

        tabButtons.forEach(btn => {
            const btnFilter = btn.getAttribute('data-filter');
            if (btnFilter === filter) {
                btn.className = 'payment-tab-btn px-3 py-1.5 rounded-md text-xs font-semibold transition-all bg-[#062072] text-white shadow-2xs';
            } else {
                btn.className = 'payment-tab-btn px-3 py-1.5 rounded-md text-xs font-semibold transition-all text-[#667085] hover:text-[#101828] hover:bg-white';
            }
        });

        rows.forEach(row => {
            const rowType = row.getAttribute('data-type');
            const shouldShow = (filter === 'all' || rowType === filter);
            row.style.display = shouldShow ? '' : 'none';
            if (shouldShow) {
                visibleCount++;
            }
        });

        if (emptyState && emptyText) {
            if (visibleCount === 0) {
                emptyText.textContent = emptyMessages[filter] || emptyMessages.all;
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }

        if (updateHistory && typeof window !== 'undefined' && window.history) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('type', filter);
            window.history.pushState({ type: filter }, '', currentUrl.toString());
        }
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const filter = btn.getAttribute('data-filter') || 'all';
            applyFilter(filter, true);
        });
    });

    // Sinkronisasi saat tombol back/forward browser ditekan
    window.addEventListener('popstate', (e) => {
        const urlParams = new URLSearchParams(window.location.search);
        const filter = urlParams.get('type') || 'all';
        applyFilter(filter, false);
    });
}
