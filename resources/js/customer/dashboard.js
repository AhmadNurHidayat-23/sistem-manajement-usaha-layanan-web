/**
 * Handler interaktif antarmuka customer SIMAUL:
 * - Filter tab aktivitas (Semua, Appointment, Pesanan)
 * - Konfirmasi aksi transaksi (data-confirm)
 */
export function initCustomerDashboard(root = document) {
    // Filter Aktivitas Dashboard
    const filterBtns = root.querySelectorAll('[data-filter-activity]');
    if (filterBtns.length > 0) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.getAttribute('data-filter-activity');
                const cards = root.querySelectorAll('.activity-card');

                cards.forEach(card => {
                    const cardType = card.getAttribute('data-type');
                    if (type === 'semua' || cardType === type) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });

                filterBtns.forEach(b => {
                    b.classList.remove('bg-white', 'text-[#062072]', 'shadow-xs');
                    b.classList.add('text-[#667085]', 'bg-transparent');
                });

                btn.classList.remove('text-[#667085]', 'bg-transparent');
                btn.classList.add('bg-white', 'text-[#062072]', 'shadow-xs');
            });
        });
    }

    // Konfirmasi dialog aksi form
    root.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            const message = el.getAttribute('data-confirm');
            if (message && !window.confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
}

if (typeof window !== 'undefined') {
    window.initCustomerDashboard = initCustomerDashboard;
}
