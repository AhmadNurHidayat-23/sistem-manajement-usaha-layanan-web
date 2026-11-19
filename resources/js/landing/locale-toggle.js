/**
 * Kontrol pergantian bahasa ID 🇮🇩 <-> EN 🇬🇧.
 * Tampilan dan navigasi sinkron tanpa refresh manual pengguna.
 */
export function initLocaleToggle(root = document) {
    const buttons = root.querySelectorAll('#btn-switch-locale, #mobile-switch-locale, .btn-switch-locale');
    if (!buttons.length) return;

    function switchLocale(e) {
        if (e) e.preventDefault();

        const labels = document.querySelectorAll('#locale-label, #mobile-locale-label, .locale-label');
        labels.forEach(el => el.classList.add('opacity-50'));

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        fetch('/locale/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.locale) {
                try {
                    localStorage.setItem('simaul_locale', data.locale);
                } catch (err) {}
            }
            window.location.reload();
        })
        .catch(() => {
            window.location.href = '/locale/toggle';
        });
    }

    buttons.forEach((btn) => {
        if (!btn.dataset.boundLocale) {
            btn.dataset.boundLocale = '1';
            btn.addEventListener('click', switchLocale);
        }
    });
}

if (typeof window !== 'undefined') {
    window.initLocaleToggle = initLocaleToggle;
}
