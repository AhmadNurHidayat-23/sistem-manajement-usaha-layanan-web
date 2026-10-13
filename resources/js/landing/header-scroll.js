/**
 * Kontrol transisi navbar Landing Page saat window di-scroll.
 */
export function initHeaderScroll() {
    const navbarBar = document.getElementById('navbar-bar');
    if (!navbarBar) return;

    const brandLogoImg = document.getElementById('brand-logo-img');
    const navLinks = document.querySelectorAll('.nav-link-item');
    const loginBtn = document.getElementById('header-login-btn');
    const dashboardBtn = document.getElementById('header-dashboard-btn');
    const logoutBtn = document.getElementById('header-logout-btn');
    const mobileBtn = document.getElementById('mobile-toggle-btn');

    const isHomePage = window.location.pathname === '/' || window.location.pathname === '';

    function updateNavbar() {
        if (!navbarBar || !isHomePage) return;
        const scrollY = window.scrollY || window.pageYOffset;

        if (scrollY > 20) {
            navbarBar.className = 'pointer-events-auto transition-all duration-300 ease-out max-w-5xl mx-auto mt-2 sm:mt-3 px-5 sm:px-6 py-2.5 rounded-2xl bg-white/90 backdrop-blur-md backdrop-saturate-150 border border-[#E4E7EC]/80 shadow-[0_4px_20px_-4px_rgba(16,24,40,0.06)]';

            if (brandLogoImg) {
                brandLogoImg.classList.remove('brightness-0', 'invert');
            }

            navLinks.forEach((link) => {
                link.className = 'nav-link-item px-3 py-1.5 text-sm font-medium rounded-lg transition-colors no-underline text-[#101828]/80 hover:text-[#062072] hover:bg-[#062072]/5';
            });

            if (loginBtn) {
                loginBtn.className = 'inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold text-white bg-[#062072] hover:bg-[#04184F] transition-all no-underline shadow-xs';
            }

            if (dashboardBtn) {
                dashboardBtn.className = 'hidden sm:inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-[#062072] bg-[#062072]/10 hover:bg-[#062072]/15 transition-colors no-underline';
            }

            if (logoutBtn) {
                logoutBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#667085] hover:text-[#101828] border border-[#E4E7EC] hover:bg-neutral-50 transition-colors cursor-pointer bg-transparent';
            }

            if (mobileBtn) {
                mobileBtn.className = 'lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg text-[#101828] hover:bg-neutral-100 transition-colors cursor-pointer border-0 bg-transparent';
            }
        } else {
            navbarBar.className = 'pointer-events-auto transition-all duration-300 ease-out max-w-7xl mx-auto py-5 bg-transparent border-transparent shadow-none';

            if (brandLogoImg) {
                brandLogoImg.classList.add('brightness-0', 'invert');
            }

            navLinks.forEach((link) => {
                link.className = 'nav-link-item px-3 py-1.5 text-sm font-medium rounded-lg transition-colors no-underline text-white/80 hover:text-white hover:bg-white/10';
            });

            if (loginBtn) {
                loginBtn.className = 'inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold text-[#062072] bg-white hover:bg-neutral-100 transition-all no-underline shadow-xs';
            }

            if (dashboardBtn) {
                dashboardBtn.className = 'hidden sm:inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-white/15 hover:bg-white/25 transition-colors no-underline';
            }

            if (logoutBtn) {
                logoutBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-medium text-white/90 hover:text-white border border-white/20 hover:bg-white/10 transition-colors cursor-pointer bg-transparent';
            }

            if (mobileBtn) {
                mobileBtn.className = 'lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg text-white hover:bg-white/10 transition-colors cursor-pointer border-0 bg-transparent';
            }
        }
    }

    window.addEventListener('scroll', updateNavbar, { passive: true });
    updateNavbar();
}

if (typeof window !== 'undefined') {
    window.initHeaderScroll = initHeaderScroll;
}
