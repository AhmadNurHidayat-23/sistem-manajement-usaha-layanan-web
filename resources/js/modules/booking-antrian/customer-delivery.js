/**
 * Modul Interaksi Pilihan Penyerahan Barang Pelanggan (Ambil di Toko / Antar ke Alamat)
 */

export function initCustomerDelivery() {
    const card = document.getElementById('delivery-method-card');
    if (!card) {
        return;
    }

    const currentSelection = document.getElementById('delivery-current-selection');
    const form = document.getElementById('form-delivery-method');
    const btnToggle = document.getElementById('btn-toggle-delivery-form');
    const btnCancel = document.getElementById('btn-cancel-delivery-edit');
    const radioLabels = card.querySelectorAll('.delivery-option-label');

    btnToggle?.addEventListener('click', () => {
        currentSelection?.classList.add('hidden');
        form?.classList.remove('hidden');
    });

    btnCancel?.addEventListener('click', () => {
        form?.classList.add('hidden');
        currentSelection?.classList.remove('hidden');
    });

    radioLabels.forEach(label => {
        const radio = label.querySelector('input[type="radio"]');
        if (!radio) return;

        radio.addEventListener('change', () => {
            radioLabels.forEach(l => {
                l.classList.remove('border-[#062072]', 'bg-blue-50/40');
                l.classList.add('border-[#E4E7EC]');
            });
            if (radio.checked) {
                label.classList.add('border-[#062072]', 'bg-blue-50/40');
                label.classList.remove('border-[#E4E7EC]');
            }
        });
    });
}

