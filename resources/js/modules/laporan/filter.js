/**
 * Modul Filter Laporan Keuangan SIMAUL.
 * Mengelola dropdown filter periode kustom (Hari, Tanggal, Bulan, Tahun),
 * tombol Batal (revert ke snapshot terakhir), tombol Terapkan,
 * event click outside, Escape, serta sinkronisasi pergantian cabang.
 */
export function initReportFilter(root = document) {
    const dropdownWrapper = root.querySelector('#custom-period-dropdown-wrapper');
    const dropdownPanel = root.querySelector('#report-custom-filter-dropdown');
    const btnToggleCustom = root.querySelector('#btn-toggle-custom-period');
    const btnCancelCustom = root.querySelector('#btn-cancel-custom-date');
    const customForm = root.querySelector('#report-custom-filter-form');
    const branchSelect = root.querySelector('#report-branch-select');
    const chevronIcon = root.querySelector('#custom-dropdown-chevron');

    const selectDay = root.querySelector('#filter-custom-day');
    const selectDate = root.querySelector('#filter-custom-date');
    const selectMonth = root.querySelector('#filter-custom-month');
    const selectYear = root.querySelector('#filter-custom-year');

    // Snapshot nilai filter yang terakhir diterapkan / tersimpan di URL
    let appliedSnapshot = {
        day: selectDay?.value || '',
        date: selectDate?.value || '',
        month: selectMonth?.value || '',
        year: selectYear?.value || '',
    };

    function takeSnapshot() {
        appliedSnapshot = {
            day: selectDay?.value || '',
            date: selectDate?.value || '',
            month: selectMonth?.value || '',
            year: selectYear?.value || '',
        };
    }

    function revertToSnapshot() {
        if (selectDay) selectDay.value = appliedSnapshot.day;
        if (selectDate) selectDate.value = appliedSnapshot.date;
        if (selectMonth) selectMonth.value = appliedSnapshot.month;
        if (selectYear) selectYear.value = appliedSnapshot.year;
    }

    function openDropdown() {
        if (!dropdownPanel) return;
        dropdownPanel.classList.remove('hidden');
        if (btnToggleCustom) btnToggleCustom.setAttribute('aria-expanded', 'true');
        if (chevronIcon) chevronIcon.classList.add('rotate-180');

        adjustDropdownPosition();

        // Pastikan nilai dropdown konsisten dengan filter terakhir yang diterapkan
        revertToSnapshot();

        selectDay?.focus();
    }

    function closeDropdown() {
        if (!dropdownPanel) return;
        dropdownPanel.classList.add('hidden');
        if (btnToggleCustom) btnToggleCustom.setAttribute('aria-expanded', 'false');
        if (chevronIcon) chevronIcon.classList.remove('rotate-180');
    }

    function adjustDropdownPosition() {
        if (!dropdownPanel || !dropdownWrapper) return;
        dropdownPanel.style.left = '0';
        dropdownPanel.style.right = 'auto';

        const wrapperRect = dropdownWrapper.getBoundingClientRect();
        const spaceBelow = window.innerHeight - wrapperRect.bottom;
        const spaceAbove = wrapperRect.top;

        if (spaceBelow < 300 && spaceAbove > spaceBelow) {
            dropdownPanel.style.top = 'auto';
            dropdownPanel.style.bottom = '100%';
            dropdownPanel.style.marginTop = '0';
            dropdownPanel.style.marginBottom = '8px';
        } else {
            dropdownPanel.style.top = '100%';
            dropdownPanel.style.bottom = 'auto';
            dropdownPanel.style.marginTop = '8px';
            dropdownPanel.style.marginBottom = '0';
        }

        const rect = dropdownPanel.getBoundingClientRect();
        const viewportWidth = window.innerWidth || document.documentElement.clientWidth;

        if (rect.right > viewportWidth - 12) {
            dropdownPanel.style.left = 'auto';
            dropdownPanel.style.right = '0';
        }
    }

    // 1. Toggle dropdown saat tombol Kustom diklik
    if (btnToggleCustom && dropdownPanel) {
        if (btnToggleCustom.dataset.bound !== 'true') {
            btnToggleCustom.dataset.bound = 'true';
            btnToggleCustom.addEventListener('click', (e) => {
                e.stopPropagation();
                if (dropdownPanel.classList.contains('hidden')) {
                    openDropdown();
                } else {
                    revertToSnapshot();
                    closeDropdown();
                }
            });
        }
    }

    // 2. Tombol Batal di dalam dropdown: Menutup tanpa mengubah filter aktif dan mengembalikan pilihan
    if (btnCancelCustom && btnCancelCustom.dataset.bound !== 'true') {
        btnCancelCustom.dataset.bound = 'true';
        btnCancelCustom.addEventListener('click', (e) => {
            e.preventDefault();
            revertToSnapshot();
            closeDropdown();
            btnToggleCustom?.focus();
        });
    }

    // 3. Tutup saat klik di luar dropdown (revert pilihan yang belum diterapkan)
    if (dropdownWrapper && dropdownWrapper.dataset.outsideBound !== 'true') {
        dropdownWrapper.dataset.outsideBound = 'true';
        document.addEventListener('click', (e) => {
            if (!dropdownPanel || dropdownPanel.classList.contains('hidden')) return;

            const isClickInsideWrapper = dropdownWrapper.contains(e.target);

            if (!isClickInsideWrapper) {
                revertToSnapshot();
                closeDropdown();
            }
        });
    }

    // 4. Tutup saat tombol Escape ditekan
    if (dropdownWrapper && dropdownWrapper.dataset.escapeBound !== 'true') {
        dropdownWrapper.dataset.escapeBound = 'true';
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && dropdownPanel && !dropdownPanel.classList.contains('hidden')) {
                revertToSnapshot();
                closeDropdown();
                btnToggleCustom?.focus();
            }
        });
    }

    // 5. Navigasi saat Owner memilih cabang lain (pertahankan parameter filter kustom aktif di URL)
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

    // 6. Tombol Terapkan: Terapkan pilihan pengguna dan simpan ke URL
    if (customForm && customForm.dataset.bound !== 'true') {
        customForm.dataset.bound = 'true';
        customForm.addEventListener('submit', () => {
            takeSnapshot();
            closeDropdown();
        });
    }

    window.addEventListener('resize', () => {
        if (dropdownPanel && !dropdownPanel.classList.contains('hidden')) {
            adjustDropdownPosition();
        }
    });
}
