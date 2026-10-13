/**
 * Salin teks (seperti nomor VA) ke clipboard tanpa inline script.
 */
export function initPaymentCopyAction(root = document) {
    const copyBtns = root.querySelectorAll('[data-copy-target]');
    copyBtns.forEach(btn => {
        if (btn.dataset.copyBound === 'true') return;
        btn.dataset.copyBound = 'true';
        btn.addEventListener('click', () => {
            const text = btn.getAttribute('data-copy-target') || '';
            if (!text) return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    const labelSpan = btn.querySelector('span');
                    if (labelSpan) {
                        const originalText = labelSpan.textContent;
                        labelSpan.textContent = 'Disalin!';
                        setTimeout(() => {
                            labelSpan.textContent = originalText;
                        }, 2000);
                    } else {
                        const originalText = btn.textContent;
                        btn.textContent = 'Disalin!';
                        setTimeout(() => {
                            btn.textContent = originalText;
                        }, 2000);
                    }
                }).catch(() => {});
            }
        });
    });
}

/**
 * Polling status pembayaran gateway secara berkala.
 */
export function initPaymentPolling(container = document.querySelector('[data-payment-polling="true"]')) {
    initPaymentCopyAction(document);

    if (!container) return;

    const pollUrl = container.dataset.pollUrl;
    const isPending = container.dataset.isPending === 'true';

    if (!isPending || !pollUrl) return;

    const pollInterval = parseInt(container.dataset.pollInterval, 10) || 5000;
    let timer = null;

    function poll() {
        fetch(pollUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.is_paid && data.receipt_url) {
                if (timer) clearInterval(timer);
                window.location.href = data.receipt_url;
            } else if (data.is_final) {
                if (timer) clearInterval(timer);
                window.location.reload();
            }
        })
        .catch(() => {});
    }

    timer = setInterval(poll, pollInterval);
}

if (typeof window !== 'undefined') {
    window.initPaymentPolling = initPaymentPolling;
    window.initPaymentCopyAction = initPaymentCopyAction;
}

