/**
 * Interaksi rating bintang reusable untuk form ulasan pelanggan dan owner.
 */
export function initStarRating(root = document) {
    const input = root.getElementById ? root.getElementById('rating-input') : root.querySelector('#rating-input');
    const labelEl = root.getElementById ? root.getElementById('rating-label') : root.querySelector('#rating-label');
    const starBtns = root.querySelectorAll ? root.querySelectorAll('.star-btn') : [];

    if (!input || starBtns.length === 0) return;

    // Deteksi tipe ulasan: customer (data-rating) atau owner (data-value)
    const isOwnerVariant = starBtns[0].dataset.value !== undefined;

    const customerLabels = {
        1: 'Sangat Buruk (1/5)',
        2: 'Kurang Puas (2/5)',
        3: 'Cukup Baik (3/5)',
        4: 'Memuaskan (4/5)',
        5: 'Sangat Memuaskan! (5/5)'
    };

    const ownerLabels = ['', 'Sangat Buruk', 'Buruk', 'Cukup', 'Baik', 'Sangat Baik'];

    let selected = parseInt(input.value, 10) || (isOwnerVariant ? 0 : 5);

    function updateView(val) {
        if (isOwnerVariant) {
            starBtns.forEach((btn, idx) => {
                const v = idx + 1;
                const star = btn.querySelector('svg');
                if (v <= val) {
                    star?.classList.replace('text-[#D0D5DD]', 'text-amber-400');
                    btn.classList.add('border-amber-300', 'bg-amber-50');
                    btn.classList.remove('border-[#E4E7EC]', 'bg-white');
                } else {
                    star?.classList.replace('text-amber-400', 'text-[#D0D5DD]');
                    btn.classList.remove('border-amber-300', 'bg-amber-50');
                    btn.classList.add('border-[#E4E7EC]', 'bg-white');
                }
            });
            if (labelEl) {
                labelEl.textContent = ownerLabels[val] || '';
            }
        } else {
            starBtns.forEach(btn => {
                const btnVal = parseInt(btn.dataset.rating, 10);
                if (btnVal <= val) {
                    btn.classList.remove('text-gray-300');
                    btn.classList.add('text-amber-400');
                } else {
                    btn.classList.remove('text-amber-400');
                    btn.classList.add('text-gray-300');
                }
            });
            if (labelEl) {
                labelEl.textContent = customerLabels[val] || 'Pilih Rating';
            }
        }
    }

    if (selected) {
        updateView(selected);
    }

    starBtns.forEach(btn => {
        const getVal = () => parseInt(isOwnerVariant ? btn.dataset.value : btn.dataset.rating, 10);

        btn.addEventListener('mouseenter', () => updateView(getVal()));
        btn.addEventListener('mouseleave', () => updateView(selected));
        btn.addEventListener('click', () => {
            selected = getVal();
            input.value = selected;
            updateView(selected);
        });
    });

    const container = root.getElementById ? root.getElementById('star-rating-container') : root.querySelector('#star-rating-container');
    if (container && !isOwnerVariant) {
        container.addEventListener('mouseleave', () => {
            const currentVal = parseInt(input.value, 10) || 5;
            updateView(currentVal);
        });
    }
}

if (typeof window !== 'undefined') {
    window.initStarRating = initStarRating;
}
