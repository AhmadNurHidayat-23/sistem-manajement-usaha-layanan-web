/**
 * Modul Filter Laporan Keuangan SIMAUL.
 * Mengelola toggle filter kustom, validasi form kustom,
 * dan sinkronisasi perubahan cabang.
 */
export function initReportFilter(root = document) {
    const customContainer = root.querySelector('#report-custom-filter-form');
    const btnToggleCustom = root.querySelector('#btn-toggle-custom-period');
    const branchSelect = root.querySelector('#report-branch-select');
    const startDateInput = root.querySelector('#filter-start-date');
    const endDateInput = root.querySelector('#filter-end-date');
    const customError = root.querySelector('#custom-date-error');

    // 1. Toggle tampilan form kustom
    if (btnToggleCustom && customContainer) {
        if (btnToggleCustom.dataset.bound !== 'true') {
            btnToggleCustom.dataset.bound = 'true';
            btnToggleCustom.addEventListener('click', () => {
                const isHidden = customContainer.classList.contains('hidden');
                if (isHidden) {
                    customContainer.classList.remove('hidden');
                    customContainer.classList.add('flex');
                    const triggerOrInput = customContainer.querySelector('.custom-date-trigger') || startDateInput;
                    triggerOrInput?.focus();
                } else {
                    customContainer.classList.add('hidden');
                    customContainer.classList.remove('flex');
                }
            });
        }
    }

    // 2. Navigasi saat Owner memilih cabang lain
    if (branchSelect && branchSelect.dataset.bound !== 'true') {
        branchSelect.dataset.bound = 'true';
        branchSelect.addEventListener('change', () => {
            const url = new URL(window.location.href);
            const val = branchSelect.value;
            if (val) {
                url.searchParams.set('branch_id', val);
            } else {
                url.searchParams.delete('branch_id');
            }
            window.location.href = url.toString();
        });
    }

    // 3. Validasi tanggal mulai dan akhir saat filter kustom disubmit
    if (customContainer && customContainer.dataset.bound !== 'true') {
        customContainer.dataset.bound = 'true';
        customContainer.addEventListener('submit', (e) => {
            if (customError) {
                customError.textContent = '';
                customError.classList.add('hidden');
            }

            const startVal = startDateInput?.value?.trim();
            const endVal = endDateInput?.value?.trim();

            if (!startVal || !endVal) {
                e.preventDefault();
                const msg = 'Silakan pilih kedua tanggal (Tanggal Mulai dan Tanggal Akhir).';
                if (customError) {
                    customError.textContent = msg;
                    customError.classList.remove('hidden');
                } else {
                    alert(msg);
                }
                return;
            }

            if (startVal > endVal) {
                e.preventDefault();
                const msg = 'Tanggal mulai tidak boleh lebih besar daripada tanggal akhir.';
                if (customError) {
                    customError.textContent = msg;
                    customError.classList.remove('hidden');
                } else {
                    alert(msg);
                }
            }
        });
    }
}
