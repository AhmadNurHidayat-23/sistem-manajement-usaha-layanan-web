/**
 * Modal Input Berat Laundry Kiloan untuk Kasir Dashboard.
 */

function formatRupiah(num) {
    return Number(num || 0).toLocaleString('id-ID');
}

export function initWeightModal() {
    const modal = document.getElementById('modal-input-berat');
    const form = document.getElementById('form-input-berat');
    const weightInput = document.getElementById('weight-actual-input');
    const employeeSelect = document.getElementById('weight-employee-select');
    const errorBox = document.getElementById('weight-modal-error');
    const submitBtn = document.getElementById('btn-submit-weight');
    const spinner = document.getElementById('btn-submit-weight-spinner');
    const btnText = document.getElementById('btn-submit-weight-text');

    if (!modal || !form) {
        return;
    }

    async function loadBranchEmployees(branchId) {
        if (!employeeSelect) return;
        employeeSelect.innerHTML = '<option value="">— Memuat Petugas... —</option>';
        if (!branchId) {
            employeeSelect.innerHTML = '<option value="">— Pilih Petugas —</option>';
            return;
        }

        try {
            const res = await fetch(`/pos/branches/${encodeURIComponent(branchId)}/employees`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('Gagal memuat petugas');
            const employees = await res.json();
            employeeSelect.innerHTML = '<option value="">— Pilih Petugas —</option>';
            employees.forEach(emp => {
                const opt = document.createElement('option');
                opt.value = emp.id;
                opt.textContent = emp.name;
                employeeSelect.appendChild(opt);
            });
        } catch (err) {
            employeeSelect.innerHTML = '<option value="">— Gagal memuat petugas —</option>';
        }
    }

    function calculateTotal() {
        const weight = parseFloat(weightInput?.value || '0') || 0;
        const price = parseFloat(document.getElementById('weight-price-per-kg-raw')?.value || '0') || 0;
        const total = weight * price;

        const formulaEl = document.getElementById('weight-calc-formula');
        const totalEl = document.getElementById('weight-calculated-total');

        if (formulaEl) {
            formulaEl.textContent = `${weight} kg × Rp ${formatRupiah(price)}`;
        }
        if (totalEl) {
            totalEl.textContent = `Rp ${formatRupiah(total)}`;
        }
    }

    function openModal(btn) {
        const orderId = btn.getAttribute('data-order-id') || '';
        const customerName = btn.getAttribute('data-customer-name') || '-';
        const serviceName = btn.getAttribute('data-service-name') || '-';
        const pricePerKg = parseFloat(btn.getAttribute('data-price-per-kg') || '0');
        const actualWeight = btn.getAttribute('data-actual-weight') || '';
        const submitUrl = btn.getAttribute('data-submit-url') || '';
        const branchId = btn.getAttribute('data-branch-id') || '';

        document.getElementById('weight-order-id-input').value = orderId;
        document.getElementById('weight-submit-url').value = submitUrl;
        document.getElementById('weight-order-code').textContent = `#${orderId}`;
        document.getElementById('weight-customer-name').textContent = customerName;
        document.getElementById('weight-service-name').textContent = serviceName;
        document.getElementById('weight-price-per-kg-raw').value = pricePerKg;
        document.getElementById('weight-price-per-kg-display').textContent = `Rp ${formatRupiah(pricePerKg)} / kg`;

        loadBranchEmployees(branchId);

        if (weightInput) {
            weightInput.value = actualWeight;
        }

        if (errorBox) {
            errorBox.textContent = '';
            errorBox.classList.add('hidden');
        }

        calculateTotal();

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        weightInput?.focus();
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        form.reset();
        if (errorBox) {
            errorBox.textContent = '';
            errorBox.classList.add('hidden');
        }
    }

    weightInput?.addEventListener('input', calculateTotal);

    // Event delegation for opening and closing modal
    document.addEventListener('click', (e) => {
        const openBtn = e.target.closest('[data-action="open-weight-modal"]');
        if (openBtn) {
            e.preventDefault();
            openModal(openBtn);
            return;
        }

        const closeBtn = e.target.closest('[data-action="close-weight-modal"]');
        if (closeBtn) {
            e.preventDefault();
            closeModal();
            return;
        }

        if (e.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitUrl = document.getElementById('weight-submit-url')?.value;
        const weightVal = parseFloat(weightInput?.value || '0');
        const employeeId = employeeSelect?.value;

        if (!submitUrl) {
            showError('URL pengiriman tidak valid.');
            return;
        }

        if (isNaN(weightVal) || weightVal <= 0) {
            showError('Silakan masukkan berat timbangan yang valid (minimal 0.01 kg).');
            weightInput?.focus();
            return;
        }

        if (!employeeId) {
            showError('Silakan pilih petugas pengerjaan.');
            employeeSelect?.focus();
            return;
        }

        setLoading(true);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    actual_weight: weightVal,
                    employee_id: employeeId
                })
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal menyimpan berat timbangan.');
            }

            closeModal();
            window.location.reload();
        } catch (err) {
            showError(err.message || 'Terjadi kesalahan sistem saat menyimpan berat.');
        } finally {
            setLoading(false);
        }
    });

    function showError(msg) {
        if (errorBox) {
            errorBox.textContent = msg;
            errorBox.classList.remove('hidden');
        }
    }

    function setLoading(isLoading) {
        if (submitBtn) {
            submitBtn.disabled = isLoading;
        }
        if (spinner) {
            spinner.classList.toggle('hidden', !isLoading);
        }
        if (btnText) {
            btnText.textContent = isLoading ? 'Menyimpan...' : 'Kirim';
        }
    }
}

