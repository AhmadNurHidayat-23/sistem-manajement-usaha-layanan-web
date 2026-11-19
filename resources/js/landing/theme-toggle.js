/**
 * Floating Dark Mode Toggle Controller for SIMAUL Landing Page.
 * Manages theme persistence via localStorage ('simaul_theme') and instant DOM toggling.
 */

export function initThemeToggle() {
    const toggleBtn = document.getElementById('landing-theme-toggle');
    const moonIcon = document.getElementById('landing-theme-icon-moon');
    const sunIcon = document.getElementById('landing-theme-icon-sun');

    if (!toggleBtn) return;

    // Ambil preferensi tema dari localStorage
    const savedTheme = localStorage.getItem('simaul_theme') ||
                       localStorage.getItem('simaul_landing_theme') ||
                       (document.documentElement.classList.contains('dark') ? 'dark' : 'light');

    function renderTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            if (moonIcon) moonIcon.style.display = 'none';
            if (sunIcon) sunIcon.style.display = 'block';
            toggleBtn.setAttribute('aria-checked', 'true');
        } else {
            document.documentElement.classList.remove('dark');
            if (moonIcon) moonIcon.style.display = 'block';
            if (sunIcon) sunIcon.style.display = 'none';
            toggleBtn.setAttribute('aria-checked', 'false');
        }
    }

    // Terapkan saat inisialisasi
    renderTheme(savedTheme);

    // Cegah double event listener binding
    if (toggleBtn.dataset.themeBound === '1') {
        return;
    }
    toggleBtn.dataset.themeBound = '1';

    // Event listener klik untuk beralih tema
    toggleBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        const nextTheme = isDark ? 'light' : 'dark';

        try {
            localStorage.setItem('simaul_theme', nextTheme);
            localStorage.setItem('simaul_landing_theme', nextTheme);
        } catch (err) {}

        renderTheme(nextTheme);

        // Beritahu window jika ada komponen scroll navbar yang mendengarkan perubahan tema
        window.dispatchEvent(new CustomEvent('simaul:theme-change', { detail: { theme: nextTheme } }));
    });
}

if (typeof window !== 'undefined') {
    window.initThemeToggle = initThemeToggle;
}
