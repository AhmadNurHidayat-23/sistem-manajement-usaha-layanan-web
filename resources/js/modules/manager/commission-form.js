/**
 * Modul Form Komisi Manager SIMAUL
 */

export function initCommissionFormListeners() {
    const form = document.getElementById('form-create-commission');
    const submitBtn = document.getElementById('btn-submit-commission');
    const submitText = document.getElementById('btn-submit-text');

    if (!form || !submitBtn) return;

    form.addEventListener('submit', function (e) {
        if (submitBtn.disabled) {
            e.preventDefault();
            return;
        }
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        if (submitText) {
            submitText.textContent = 'Menyimpan...';
        }
    });
}

window.initCommissionFormListeners = initCommissionFormListeners;

