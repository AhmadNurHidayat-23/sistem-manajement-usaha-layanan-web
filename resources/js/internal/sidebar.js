/**
 * Logika interaksi collapse sidebar internal dan responsive mobile drawer.
 */
export function toggleSidebar() {
    const sidebar = document.getElementById('sidebar-nav');
    const main = document.getElementById('main-area');
    if (!sidebar || !main) return;

    // Aktifkan animasi transisi halus hanya ketika pengguna menekan tombol toggle
    sidebar.classList.add('sidebar-animate');
    main.classList.add('sidebar-animate');

    sidebar.classList.toggle('collapsed');
    main.classList.toggle('sidebar-collapsed');

    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebar-collapsed', isCollapsed ? '1' : '0');
    document.cookie = `sidebar_collapsed=${isCollapsed ? '1' : '0'}; path=/; max-age=31536000; SameSite=Lax`;
}

export function openMobileSidebar() {
    const sidebar = document.getElementById('sidebar-nav');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (sidebar) sidebar.classList.add('mobile-open');
    if (backdrop) backdrop.classList.add('active');
    document.body.style.overflow = 'hidden';
}

export function closeMobileSidebar() {
    const sidebar = document.getElementById('sidebar-nav');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (sidebar) sidebar.classList.remove('mobile-open');
    if (backdrop) backdrop.classList.remove('active');
    document.body.style.overflow = '';
}

export function restoreSidebarState() {
    const cookieMatch = document.cookie.split('; ').find(row => row.startsWith('sidebar_collapsed='));
    const cookieVal = cookieMatch ? cookieMatch.split('=')[1] : null;
    const localVal = localStorage.getItem('sidebar-collapsed');

    const isCollapsed = cookieVal !== null ? (cookieVal === '1') : (localVal === '1');

    if (cookieVal === null && localVal !== null) {
        document.cookie = `sidebar_collapsed=${localVal}; path=/; max-age=31536000; SameSite=Lax`;
    } else if (cookieVal !== null && localVal !== cookieVal) {
        localStorage.setItem('sidebar-collapsed', cookieVal);
    }

    const sidebar = document.getElementById('sidebar-nav');
    const main = document.getElementById('main-area');
    if (sidebar && main) {
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            main.classList.add('sidebar-collapsed');
        } else {
            sidebar.classList.remove('collapsed');
            main.classList.remove('sidebar-collapsed');
        }
    }
}

export function initSidebarListeners(root = document) {
    const toggleBtns = root.querySelectorAll('.sidebar-toggle-btn');
    toggleBtns.forEach((btn) => {
        if (!btn.dataset.sidebarBound) {
            btn.dataset.sidebarBound = '1';
            btn.addEventListener('click', toggleSidebar);
        }
    });

    const mobileBtn = root.getElementById?.('mobile-sidebar-toggle-btn') ?? document.getElementById('mobile-sidebar-toggle-btn');
    if (mobileBtn && !mobileBtn.dataset.sidebarBound) {
        mobileBtn.dataset.sidebarBound = '1';
        mobileBtn.addEventListener('click', openMobileSidebar);
    }

    const closeBtns = root.querySelectorAll('.sidebar-close-btn');
    closeBtns.forEach((btn) => {
        if (!btn.dataset.sidebarBound) {
            btn.dataset.sidebarBound = '1';
            btn.addEventListener('click', closeMobileSidebar);
        }
    });

    const backdrop = root.getElementById?.('sidebar-backdrop') ?? document.getElementById('sidebar-backdrop');
    if (backdrop && !backdrop.dataset.sidebarBound) {
        backdrop.dataset.sidebarBound = '1';
        backdrop.addEventListener('click', closeMobileSidebar);
    }

    initSidebarTooltips(root);
}

export function initSidebarTooltips(root = document) {
    if (typeof document === 'undefined') return;

    let tooltipEl = document.getElementById('sidebar-floating-tooltip');
    if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        tooltipEl.id = 'sidebar-floating-tooltip';
        tooltipEl.className = 'fixed pointer-events-none z-[9999] px-2.5 py-1 bg-[#101828] text-white text-xs font-medium rounded-md whitespace-nowrap shadow-lg border border-white/10 opacity-0 transition-opacity duration-150';
        document.body.appendChild(tooltipEl);
    }

    const links = root.querySelectorAll('.sidebar-nav .sidebar-link');
    links.forEach((link) => {
        if (link.dataset.tooltipBound) return;
        link.dataset.tooltipBound = '1';

        link.addEventListener('mouseenter', () => {
            const sidebar = document.getElementById('sidebar-nav');
            if (!sidebar || !sidebar.classList.contains('collapsed') || window.innerWidth < 1024) return;

            const tooltipSpan = link.querySelector('.sidebar-tooltip');
            const tooltipText = tooltipSpan?.textContent?.trim() || link.getAttribute('title');
            if (!tooltipText || !tooltipEl) return;

            tooltipEl.textContent = tooltipText;
            const rect = link.getBoundingClientRect();
            const top = rect.top + (rect.height - (tooltipEl.offsetHeight || 24)) / 2;
            const left = rect.right + 10;

            tooltipEl.style.top = `${top}px`;
            tooltipEl.style.left = `${left}px`;
            tooltipEl.style.opacity = '1';
        });

        link.addEventListener('mouseleave', () => {
            if (tooltipEl) {
                tooltipEl.style.opacity = '0';
            }
        });

        link.addEventListener('click', () => {
            if (tooltipEl) {
                tooltipEl.style.opacity = '0';
            }
        });
    });
}

// Global initialization & event listeners
if (typeof window !== 'undefined') {
    window.toggleSidebar = toggleSidebar;
    window.openMobileSidebar = openMobileSidebar;
    window.closeMobileSidebar = closeMobileSidebar;
    window.restoreSidebarState = restoreSidebarState;
    window.initSidebarListeners = initSidebarListeners;
    window.initSidebarTooltips = initSidebarTooltips;

    // Tutup mobile drawer saat tombol Escape ditekan
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileSidebar();
        }
    });

    // Reset drawer state jika layar di-resize kembali ke desktop (>= 1024px) dengan debounce
    let resizeTimer = null;
    window.addEventListener('resize', () => {
        if (resizeTimer) clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.innerWidth >= 1024) {
                closeMobileSidebar();
            }
        }, 100);
    }, { passive: true });
}

