import { initHeaderScroll } from '../landing/header-scroll.js';

/**
 * Kontrol navigasi customer dan mobile toggle panel.
 */
export function initCustomerNavbar() {
    initHeaderScroll();
}

export function toggleMobileNav() {
    const panel = document.getElementById('mobile-nav-panel');
    if (panel) {
        panel.classList.toggle('hidden');
    }
}

export function closeMobileNav() {
    const panel = document.getElementById('mobile-nav-panel');
    if (panel && !panel.classList.contains('hidden')) {
        panel.classList.add('hidden');
    }
}

// Bind secara global untuk inline handler Blade
if (typeof window !== 'undefined') {
    window.toggleMobileNav = toggleMobileNav;
    window.closeMobileNav = closeMobileNav;
    window.initCustomerNavbar = initCustomerNavbar;
}
