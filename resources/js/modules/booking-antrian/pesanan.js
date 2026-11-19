/**
 * Logika form pesanan mandiri customer:
 * 1. Filter pilihan layanan dinamis via fetch API berdasarkan cabang yang dipilih.
 * 2. Form generik dinamis berdasarkan jenis layanan (fixed-price vs weight-based).
 * 3. Modal pop-up interaktif untuk Buat Pesanan.
 */

export function openOrderModal(branchId = null, serviceId = null, triggerBtn = null) {
    const modal = document.getElementById('modal-buat-pesanan');
    if (!modal) return;

    const branchSelect = document.getElementById('branch_id');
    const serviceSelect = document.getElementById('service_id');

    if (branchId && branchSelect) {
        branchSelect.value = String(branchId);
        branchSelect.dispatchEvent(new Event('change', { bubbles: true }));
        branchSelect.dispatchEvent(new Event('render-custom-select'));
        if (typeof window.filterServicesByBranch === 'function') {
            window.filterServicesByBranch(serviceId);
        }
    } else if (serviceId && serviceSelect) {
        serviceSelect.value = String(serviceId);
        serviceSelect.dispatchEvent(new Event('change', { bubbles: true }));
        serviceSelect.dispatchEvent(new Event('render-custom-select'));
        if (typeof window.updateQuantityLabel === 'function') {
            window.updateQuantityLabel();
        }
    }

    if (typeof window.openModal === 'function') {
        window.openModal('modal-buat-pesanan', triggerBtn);
    } else {
        modal.classList.remove('hidden');
        modal.classList.add('is-open');
        document.body.classList.add('overflow-hidden');
    }

    setTimeout(() => {
        if (typeof window.invalidatePickupMaps === 'function') {
            window.invalidatePickupMaps();
        }
    }, 180);
}

export function initPesananCreate() {
    const branchSelect = document.getElementById('branch_id');
    const serviceSelect = document.getElementById('service_id');
    const qtyInput = document.getElementById('estimated_quantity');
    const weightInfoBox = document.getElementById('weight-info-box');
    const qtyContainer = document.getElementById('quantity-field-container');
    const panel = document.getElementById('estimation-panel');
    const unitLabelEl = document.getElementById('est-unit-label');
    const unitPriceEl = document.getElementById('est-unit-price');
    const qtyRowEl = document.getElementById('est-qty-row');
    const qtyEl = document.getElementById('est-qty');
    const totalLabelEl = document.getElementById('est-total-label');
    const totalEl = document.getElementById('est-total');
    const weightNoteEl = document.getElementById('est-weight-note');

    if (!serviceSelect) return;

    /**
     * Filter dropdown layanan berdasarkan cabang yang aktif via fetch
     */
    function filterServicesByBranch(targetServiceId = null) {
        const branchId = branchSelect ? branchSelect.value.trim() : '';

        // 1. Kosongkan options & reset pilihan service lama segera
        serviceSelect.innerHTML = '';
        serviceSelect.value = '';

        if (!branchId) {
            const placeholderOpt = document.createElement('option');
            placeholderOpt.value = '';
            placeholderOpt.textContent = '— Pilih Cabang Terlebih Dahulu —';
            serviceSelect.appendChild(placeholderOpt);
            serviceSelect.dispatchEvent(new Event('change', { bubbles: true }));
            serviceSelect.dispatchEvent(new Event('render-custom-select'));
            updateServiceFields();
            return;
        }

        // Tampilkan loading placeholder
        const loadingOpt = document.createElement('option');
        loadingOpt.value = '';
        loadingOpt.textContent = '— Memuat Layanan... —';
        serviceSelect.appendChild(loadingOpt);
        serviceSelect.dispatchEvent(new Event('change', { bubbles: true }));
        serviceSelect.dispatchEvent(new Event('render-custom-select'));
        updateServiceFields();

        // 2. Request ulang layanan cabang tersebut
        fetch(`/customer/pesanan/branch-services?branch_id=${encodeURIComponent(branchId)}`)
            .then((res) => {
                if (!res.ok) throw new Error('Gagal memuat layanan cabang');
                return res.json();
            })
            .then((services) => {
                // 3. Tampilkan hanya layanan cabang tersebut
                serviceSelect.innerHTML = '';

                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.dataset.price = '0';
                defaultOpt.dataset.isWeight = '0';
                defaultOpt.dataset.unit = '';
                defaultOpt.dataset.duration = '0';
                defaultOpt.textContent = '— Pilih Layanan —';
                serviceSelect.appendChild(defaultOpt);

                services.forEach((s) => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.dataset.branchId = s.branch_id || '';
                    opt.dataset.price = s.price;
                    opt.dataset.isWeight = s.is_weight;
                    opt.dataset.unit = s.unit;
                    opt.dataset.duration = s.duration;
                    opt.dataset.durationFormatted = s.duration_formatted;
                    opt.textContent = s.label;
                    if (targetServiceId && String(s.id) === String(targetServiceId)) {
                        opt.selected = true;
                    }
                    serviceSelect.appendChild(opt);
                });

                if (targetServiceId) {
                    serviceSelect.value = String(targetServiceId);
                } else if (!serviceSelect.value) {
                    serviceSelect.value = '';
                }
                serviceSelect.dispatchEvent(new Event('change', { bubbles: true }));
                serviceSelect.dispatchEvent(new Event('render-custom-select'));
                updateServiceFields();
            })
            .catch((err) => {
                console.error(err);
                serviceSelect.innerHTML = '';
                const errOpt = document.createElement('option');
                errOpt.value = '';
                errOpt.textContent = '— Gagal memuat layanan —';
                serviceSelect.appendChild(errOpt);
                serviceSelect.value = '';
                serviceSelect.dispatchEvent(new Event('change', { bubbles: true }));
                serviceSelect.dispatchEvent(new Event('render-custom-select'));
                updateServiceFields();
            });
    }

    /**
     * Update tampilan field form & panel estimasi harga berdasarkan layanan yang dipilih
     */
    function updateServiceFields() {
        const option = serviceSelect.options[serviceSelect.selectedIndex];
        const val = serviceSelect.value;

        if (!val || !option) {
            if (weightInfoBox) weightInfoBox.classList.add('hidden');
            if (qtyContainer) qtyContainer.classList.add('hidden');
            if (panel) panel.classList.add('hidden');
            return;
        }

        const price = parseFloat(option.dataset.price || '0');
        const isWeight = option.dataset.isWeight === '1';

        if (isWeight) {
            // Layanan berbasis berat (laundry / kiloan)
            if (weightInfoBox) weightInfoBox.classList.remove('hidden');
            if (qtyContainer) qtyContainer.classList.remove('hidden');

            if (panel) {
                panel.classList.remove('hidden');
                if (unitLabelEl) unitLabelEl.textContent = 'Harga per kg:';
                if (unitPriceEl) unitPriceEl.textContent = `Rp ${price.toLocaleString('id-ID')} / kg`;

                const qtyVal = qtyInput ? qtyInput.value.trim() : '';
                if (qtyVal && parseFloat(qtyVal) > 0) {
                    if (qtyRowEl) qtyRowEl.classList.remove('hidden');
                    if (qtyEl) qtyEl.textContent = `${qtyVal} kantong/item`;
                } else {
                    if (qtyRowEl) qtyRowEl.classList.add('hidden');
                }

                if (totalLabelEl) totalLabelEl.textContent = 'Estimasi Total:';
                if (totalEl) totalEl.textContent = 'Menunggu Penimbangan';
                if (weightNoteEl) weightNoteEl.classList.remove('hidden');
            }
        } else {
            // Layanan fixed price (barbershop, styling, detailing, dll.)
            if (weightInfoBox) weightInfoBox.classList.add('hidden');
            if (qtyContainer) qtyContainer.classList.add('hidden');
            if (qtyInput) qtyInput.value = '';

            if (panel) {
                panel.classList.remove('hidden');
                if (unitLabelEl) unitLabelEl.textContent = 'Harga Layanan:';
                if (unitPriceEl) unitPriceEl.textContent = `Rp ${price.toLocaleString('id-ID')}`;
                if (qtyRowEl) qtyRowEl.classList.add('hidden');
                if (totalLabelEl) totalLabelEl.textContent = 'Estimasi Total:';
                if (totalEl) totalEl.textContent = `Rp ${price.toLocaleString('id-ID')}`;
                if (weightNoteEl) weightNoteEl.classList.add('hidden');
            }
        }
    }

    if (branchSelect) {
        branchSelect.addEventListener('change', () => filterServicesByBranch());
    }

    serviceSelect.addEventListener('change', updateServiceFields);
    if (qtyInput) {
        qtyInput.addEventListener('input', updateServiceFields);
    }

    // Pencegahan double submit pada form Buat Pesanan (#form-order-customer)
    const orderForm = document.getElementById('form-order-customer');
    if (orderForm) {
        orderForm.addEventListener('submit', function () {
            const submitBtn = document.getElementById('btn-konfirmasi-order');
            if (submitBtn) {
                submitBtn.disabled = true;
                const textSpan = submitBtn.querySelector('span');
                if (textSpan) {
                    textSpan.textContent = 'Menyiapkan Konfirmasi...';
                }
            }
        });
    }

    // Auto-open jika modal memiliki atribut data-auto-open="1"
    const modalEl = document.getElementById('modal-buat-pesanan');
    if (modalEl && modalEl.dataset.autoOpen === '1') {
        if (typeof window.openModal === 'function') {
            window.openModal('modal-buat-pesanan');
        } else {
            modalEl.classList.remove('hidden');
            modalEl.classList.add('is-open');
            document.body.classList.add('overflow-hidden');
        }
        setTimeout(() => {
            if (typeof window.invalidatePickupMaps === 'function') {
                window.invalidatePickupMaps();
            }
        }, 180);
    }

    // Standalone modal navigation handler (ketika diakses langsung via URL /customer/pesanan/buat)
    if (modalEl && modalEl.dataset.standalone === '1') {
        modalEl.addEventListener('click', (e) => {
            if (e.target === modalEl) {
                window.location.href = '/customer/dashboard#pilih-layanan';
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modalEl.classList.contains('hidden')) {
                window.location.href = '/customer/dashboard#pilih-layanan';
            }
        });
    }

    // Pencegahan double submit pada form konfirmasi akhir (#form-confirm-order)
    let isSubmittingConfirmOrder = false;
    if (typeof document !== 'undefined' && !document.body.dataset.confirmSubmitBound) {
        document.body.dataset.confirmSubmitBound = '1';
        document.addEventListener('submit', (e) => {
            const confirmForm = e.target.closest('#form-confirm-order');
            if (!confirmForm) return;

            if (isSubmittingConfirmOrder) {
                e.preventDefault();
                return false;
            }

            isSubmittingConfirmOrder = true;
            const submitBtn = confirmForm.querySelector('#btn-submit-order');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                const textSpan = submitBtn.querySelector('span');
                if (textSpan) {
                    textSpan.textContent = 'Memproses Pesanan...';
                }
            }
        });
    }

    // Global listener untuk tombol buka modal [data-action="open-order-modal"]
    if (typeof document !== 'undefined' && !document.body.dataset.orderModalBound) {
        document.body.dataset.orderModalBound = '1';
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-action="open-order-modal"]');
            if (btn) {
                e.preventDefault();
                const branchId = btn.getAttribute('data-branch-id');
                const serviceId = btn.getAttribute('data-service-id');
                openOrderModal(branchId, serviceId, btn);
            }
        });
    }

    if (typeof window !== 'undefined') {
        window.updateQuantityLabel = updateServiceFields;
        window.filterServicesByBranch = filterServicesByBranch;
        window.openOrderModal = openOrderModal;
    }

    // Jalankan update form saat initial load jika service sudah dipilih
    if (serviceSelect.value) {
        updateServiceFields();
    }
}

if (typeof window !== 'undefined') {
    window.initPesananCreate = initPesananCreate;
    window.openOrderModal = openOrderModal;
}
