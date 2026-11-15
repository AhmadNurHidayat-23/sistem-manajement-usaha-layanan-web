/**
 * Modul Pembatalan Booking Pelanggan SIMAUL
 */

export function initCustomerBookingCancel() {
    const cancelForm = document.getElementById('form-cancel-booking');
    const confirmBtn = document.getElementById('btn-cancel-dialog-confirm');
    const backBtn = document.getElementById('btn-cancel-dialog-back');
    const reasonRadios = document.querySelectorAll('input[name="cancellation_reason"]');
    const otherWrapper = document.getElementById('cancellation-other-wrapper');
    const otherInput = document.getElementById('other_cancellation_reason');
    const reasonError = document.getElementById('cancellation-reason-error');
    const otherError = document.getElementById('other-reason-error');
    const cancelModal = document.getElementById('modal-cancel-booking');

    if (!cancelForm) return;

    reasonRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (reasonError) reasonError.classList.add('hidden');
            if (this.value === 'Alasan lainnya') {
                otherWrapper?.classList.remove('hidden');
                otherInput?.focus();
            } else {
                otherWrapper?.classList.add('hidden');
                if (otherError) otherError.classList.add('hidden');
            }
        });
    });

    if (otherInput) {
        otherInput.addEventListener('input', function () {
            if (this.value.trim().length > 0 && otherError) {
                otherError.classList.add('hidden');
            }
        });
    }

    if (confirmBtn) {
        cancelForm.addEventListener('submit', function (e) {
            const selectedReason = cancelForm.querySelector('input[name="cancellation_reason"]:checked');

            if (!selectedReason) {
                e.preventDefault();
                reasonError?.classList.remove('hidden');
                cancelForm.querySelector('input[name="cancellation_reason"]')?.focus();
                return;
            }

            if (selectedReason.value === 'Alasan lainnya') {
                const otherValue = otherInput ? otherInput.value.trim() : '';
                if (!otherValue) {
                    e.preventDefault();
                    otherError?.classList.remove('hidden');
                    otherInput?.focus();
                    return;
                }
            }

            if (confirmBtn.disabled) {
                e.preventDefault();
                return;
            }

            confirmBtn.disabled = true;
            if (backBtn) backBtn.disabled = true;
            confirmBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Membatalkan...</span>
            `;
        });
    }

    // Buka modal secara otomatis jika terdapat flag error validasi
    if (cancelModal && cancelModal.getAttribute('data-has-errors') === 'true') {
        if (typeof window.openModal === 'function') {
            window.openModal(cancelModal);
        } else {
            cancelModal.classList.remove('hidden');
        }
    }
}

window.initCustomerBookingCancel = initCustomerBookingCancel;

