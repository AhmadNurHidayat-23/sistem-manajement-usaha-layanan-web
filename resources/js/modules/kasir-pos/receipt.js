/**
 * Modul Interaksi Struk Kasir & Pengiriman WhatsApp
 */

export function normalizeIndonesianPhone(phone) {
    if (!phone) return '';
    let digits = String(phone).replace(/\D/g, '');
    if (digits.startsWith('0')) {
        digits = '62' + digits.substring(1);
    } else if (digits.startsWith('8')) {
        digits = '62' + digits;
    }
    return digits;
}

export function isValidIndonesianPhone(phone) {
    const normalized = normalizeIndonesianPhone(phone);
    return /^628[0-9]{7,12}$/.test(normalized);
}

function setButtonLoading(button, loadingText = 'Mengirim...') {
    if (!button) return;
    button.disabled = true;
    button.classList.add('opacity-75', 'cursor-not-allowed');

    const spinnerSvg = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    `;
    button.innerHTML = `${spinnerSvg}<span>${loadingText}</span>`;
}

export function initReceiptWaHandlers(root = document) {
    const modalForm = root.querySelector('#form-send-wa-modal');
    const phoneInput = root.querySelector('#wa-receipt-phone');
    const errorMsg = root.querySelector('#wa-phone-error');
    const consentCheck = root.querySelector('#wa-consent-check');

    if (modalForm && !modalForm.dataset.boundReceipt) {
        modalForm.dataset.boundReceipt = 'true';

        // Real-time clearance of error on input
        phoneInput?.addEventListener('input', () => {
            if (errorMsg) {
                errorMsg.textContent = '';
                errorMsg.classList.add('hidden');
            }
            phoneInput.classList.remove('border-rose-500', 'focus:border-rose-500');
        });

        modalForm.addEventListener('submit', (e) => {
            if (modalForm.dataset.submitting === 'true') {
                e.preventDefault();
                return;
            }

            const rawVal = phoneInput ? phoneInput.value.trim() : '';

            if (!rawVal || !isValidIndonesianPhone(rawVal)) {
                e.preventDefault();
                if (errorMsg) {
                    errorMsg.textContent = 'Nomor WhatsApp tidak valid. Gunakan awalan 08, 62, atau +62 (10-15 digit).';
                    errorMsg.classList.remove('hidden');
                }
                phoneInput?.classList.add('border-rose-500', 'focus:border-rose-500');
                phoneInput?.focus();
                return;
            }

            if (consentCheck && !consentCheck.checked) {
                e.preventDefault();
                alert('Persetujuan pelanggan wajib dicentang sebelum mengirim struk.');
                consentCheck.focus();
                return;
            }

            // Normalisasi nilai input sebelum submit
            phoneInput.value = normalizeIndonesianPhone(rawVal);
            modalForm.dataset.submitting = 'true';

            const submitBtn = modalForm.querySelector('#btn-submit-wa-modal');
            setButtonLoading(submitBtn, 'Mengirim Struk...');
        });
    }

    // Direct submit form (ketika nomor pelanggan sudah tercatat)
    const directForm = root.querySelector('.form-send-wa-direct');
    if (directForm && !directForm.dataset.boundReceipt) {
        directForm.dataset.boundReceipt = 'true';

        directForm.addEventListener('submit', (e) => {
            if (directForm.dataset.submitting === 'true') {
                e.preventDefault();
                return;
            }

            directForm.dataset.submitting = 'true';
            const btn = directForm.querySelector('#btn-send-wa');
            setButtonLoading(btn, 'Mengirim...');
        });
    }
}
