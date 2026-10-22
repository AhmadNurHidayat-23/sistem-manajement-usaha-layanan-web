import { initHeaderScroll } from '../landing/header-scroll.js';

/**
 * Dropdown profil pelanggan di header.
 */
export function initCustomerProfileDropdown(root = document) {
    const btn = root.getElementById?.('customer-profile-btn') ?? document.getElementById('customer-profile-btn');
    const dropdown = root.getElementById?.('customer-profile-dropdown') ?? document.getElementById('customer-profile-dropdown');
    const menu = root.getElementById?.('customer-profile-menu') ?? document.getElementById('customer-profile-menu');

    if (!btn || !dropdown || !menu || btn.dataset.boundCustomerProfile) return;
    btn.dataset.boundCustomerProfile = '1';

    function openDropdown() {
        dropdown.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
    }

    function toggleDropdown() {
        if (dropdown.classList.contains('hidden')) {
            openDropdown();
        } else {
            closeDropdown();
        }
    }

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleDropdown();
    });

    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target)) {
            closeDropdown();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeDropdown();
        }
    });
}

/**
 * Kontrol navigasi customer dan mobile toggle panel.
 */
export function initCustomerNavbar() {
    initHeaderScroll();
    initCustomerProfileDropdown();
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
    window.initCustomerProfileDropdown = initCustomerProfileDropdown;
}

