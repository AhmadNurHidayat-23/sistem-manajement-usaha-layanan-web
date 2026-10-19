/**
 * Profile Dropdown toggle for SIMAUL Header.
 */
export function initProfileDropdown(root = document) {
    const btn = root.getElementById?.('header-profile-btn') ?? document.getElementById('header-profile-btn');
    const dropdown = root.getElementById?.('header-profile-dropdown') ?? document.getElementById('header-profile-dropdown');
    const wrapper = root.getElementById?.('header-profile-menu') ?? document.getElementById('header-profile-menu');

    if (!btn || !dropdown || !wrapper || btn.dataset.boundProfile) return;
    btn.dataset.boundProfile = '1';

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
        if (!wrapper.contains(e.target)) {
            closeDropdown();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeDropdown();
        }
    });
}
