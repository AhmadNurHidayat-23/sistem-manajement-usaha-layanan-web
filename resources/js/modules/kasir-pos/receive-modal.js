/**
 * Modal Terima Pesanan Drop-off untuk Dashboard Kasir.
 */

export function initReceiveModal() {
    const modal = document.getElementById('modal-terima-pesanan');
    const form = document.getElementById('form-terima-pesanan');
    const employeeContainer = document.getElementById('receive-employee-container');
    const employeeSelect = document.getElementById('receive-employee-select');
    const kiloanInfo = document.getElementById('receive-kiloan-info');
    const errorBox = document.getElementById('receive-modal-error');
    const submitBtn = document.getElementById('btn-submit-receive');
    const spinner = document.getElementById('btn-submit-receive-spinner');
    const btnText = document.getElementById('btn-submit-receive-text');

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
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
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

    function openModal(btn) {
        const orderId = btn.getAttribute('data-order-id') || '';
        const customerName = btn.getAttribute('data-customer-name') || '-';
        const serviceName = btn.getAttribute('data-service-name') || '-';
        const isWeightBased = btn.getAttribute('data-is-weight-based') === '1';
        const submitUrl = btn.getAttribute('data-submit-url') || '';
        const branchId = btn.getAttribute('data-branch-id') || '';

        document.getElementById('receive-order-id-input').value = orderId;
        document.getElementById('receive-submit-url').value = submitUrl;
        document.getElementById('receive-is-weight-based').value = isWeightBased ? '1' : '0';
        document.getElementById('receive-order-code').textContent = `#${orderId}`;
        document.getElementById('receive-customer-name').textContent = customerName;
        document.getElementById('receive-service-name').textContent = serviceName;

        if (errorBox) {
            errorBox.textContent = '';
            errorBox.classList.add('hidden');
        }

        if (isWeightBased) {
            employeeContainer?.classList.add('hidden');
            if (employeeSelect) {
                employeeSelect.removeAttribute('required');
                employeeSelect.value = '';
            }
            kiloanInfo?.classList.remove('hidden');
        } else {
            employeeContainer?.classList.remove('hidden');
            if (employeeSelect) {
                employeeSelect.setAttribute('required', 'required');
            }
            kiloanInfo?.classList.add('hidden');
            loadBranchEmployees(branchId);
        }

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
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

    // Event delegation
    document.addEventListener('click', (e) => {
        const openBtn = e.target.closest('[data-action="open-receive-modal"]');
        if (openBtn) {
            e.preventDefault();
            openModal(openBtn);
            return;
        }

        const closeBtn = e.target.closest('[data-action="close-receive-modal"]');
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

        const submitUrl = document.getElementById('receive-submit-url')?.value;
        const isWeightBased = document.getElementById('receive-is-weight-based')?.value === '1';
        const employeeId = employeeSelect?.value;

        if (!submitUrl) {
            showError('URL pengiriman tidak valid.');
            return;
        }

        if (!isWeightBased && !employeeId) {
            showError('Silakan pilih petugas pengerjaan terlebih dahulu.');
            employeeSelect?.focus();
            return;
        }

        setLoading(true);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const payload = {};
            if (!isWeightBased && employeeId) {
                payload.employee_id = employeeId;
            }

            const res = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal mengonfirmasi penerimaan pesanan.');
            }

            closeModal();
            window.location.reload();
        } catch (err) {
            showError(err.message || 'Terjadi kesalahan sistem saat menerima pesanan.');
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
            btnText.textContent = isLoading ? 'Memproses...' : 'Kirim';
        }
    }
}
