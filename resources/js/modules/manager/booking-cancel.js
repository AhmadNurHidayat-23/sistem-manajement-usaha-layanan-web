/**
 * Modul Pembatalan Booking Manager SIMAUL
 */

export function initManagerBookingCancel() {
    const radios = document.querySelectorAll('.manager-cancel-reason-radio');
    const customWrapper = document.getElementById('wrapper-mgr-reason-custom');
    const customInput = document.getElementById('mgr_reason_custom');
    const form = document.getElementById('form-cancel-booking-manager');
    const confirmBtn = document.getElementById('btn-mgr-confirm-cancel');
    const errorPreset = document.getElementById('error-mgr-preset');
    const errorCustom = document.getElementById('error-mgr-custom');

    if (!form || !confirmBtn) return;

    radios.forEach(r => {
        r.addEventListener('change', function () {
            if (errorPreset) errorPreset.classList.add('hidden');
            if (this.value === 'Alasan lainnya') {
                customWrapper?.classList.remove('hidden');
                customInput?.focus();
            } else {
                customWrapper?.classList.add('hidden');
                if (errorCustom) errorCustom.classList.add('hidden');
            }
        });
    });

    form.addEventListener('submit', function (e) {
        const checked = document.querySelector('.manager-cancel-reason-radio:checked');
        if (!checked) {
            e.preventDefault();
            if (errorPreset) {
                errorPreset.textContent = 'Silakan pilih alasan pembatalan.';
                errorPreset.classList.remove('hidden');
            }
            return;
        }
        if (checked.value === 'Alasan lainnya') {
            const val = customInput?.value.trim() ?? '';
            if (!val) {
                e.preventDefault();
                if (errorCustom) {
                    errorCustom.textContent = 'Alasan pembatalan lainnya wajib diisi.';
                    errorCustom.classList.remove('hidden');
                }
                customInput?.focus();
                return;
            }
        }

        if (confirmBtn.disabled) {
            e.preventDefault();
            return;
        }
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Membatalkan...</span>
        `;
    });
}

window.initManagerBookingCancel = initManagerBookingCancel;

