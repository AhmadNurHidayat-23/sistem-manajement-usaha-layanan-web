/**
 * Logika form pesanan mandiri customer:
 * 1. Filter pilihan layanan dinamis via fetch API berdasarkan cabang yang dipilih.
 * 2. Form generik dinamis berdasarkan jenis layanan (fixed-price vs weight-based).
 */
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
    function filterServicesByBranch() {
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
                    serviceSelect.appendChild(opt);
                });

                // Pastikan service lama direset dan custom-select di-update
                serviceSelect.value = '';
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
        branchSelect.addEventListener('change', filterServicesByBranch);
    }

    serviceSelect.addEventListener('change', updateServiceFields);
    if (qtyInput) {
        qtyInput.addEventListener('input', updateServiceFields);
    }

    if (typeof window !== 'undefined') {
        window.updateQuantityLabel = updateServiceFields;
        window.filterServicesByBranch = filterServicesByBranch;
    }

    // Jalankan update form saat initial load
    if (serviceSelect.value) {
        updateServiceFields();
    }
}

if (typeof window !== 'undefined') {
    window.initPesananCreate = initPesananCreate;
}
