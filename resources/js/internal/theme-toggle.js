/**
 * Dark / Light Mode Toggle untuk Dashboard Internal (Owner, Manager, Kasir, Karyawan).
 * Persisten di localStorage (key: 'simaul_theme').
 */

export function initInternalThemeToggle(root = document) {
    const btn = root.getElementById?.('internal-theme-toggle-btn') ?? document.getElementById('internal-theme-toggle-btn');
    if (!btn || btn.dataset.boundTheme) return;
    btn.dataset.boundTheme = '1';

    function applyTheme(theme) {
        const isDark = theme === 'dark';
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        btn.setAttribute('aria-label', isDark ? 'Ganti ke Light Mode' : 'Ganti ke Dark Mode');
        btn.setAttribute('title', isDark ? 'Ganti ke Light Mode' : 'Ganti ke Dark Mode');
    }

    btn.addEventListener('click', () => {
        const isDark = document.documentElement.classList.contains('dark');
        const nextTheme = isDark ? 'light' : 'dark';
        try {
            localStorage.setItem('simaul_theme', nextTheme);
        } catch (e) {}
        applyTheme(nextTheme);
    });

    let savedTheme = 'light';
    try {
        savedTheme = localStorage.getItem('simaul_theme') || 'light';
    } catch (e) {}
    applyTheme(savedTheme);
}

if (typeof window !== 'undefined') {
    window.initInternalThemeToggle = initInternalThemeToggle;
}
