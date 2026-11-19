/**
 * Kontrol transisi navbar Landing Page saat window di-scroll.
 * Navbar standar profesional full-width edge-to-edge.
 * Mendukung transisi Light Mode dan Dark Mode.
 */
export function initHeaderScroll() {
    const header = document.getElementById('floating-header');
    if (!header) return;

    const brandLogoImg = document.getElementById('brand-logo-img');
    const navLinks = document.querySelectorAll('.nav-link-item');
    const loginBtn = document.getElementById('header-login-btn');
    const dashboardBtn = document.getElementById('header-dashboard-btn');
    const logoutBtn = document.getElementById('header-logout-btn');
    const mobileBtn = document.getElementById('mobile-toggle-btn');
    const customerProfileBtn = document.getElementById('customer-profile-btn');
    const localeBtn = document.getElementById('btn-switch-locale');
    const themeBtn = document.getElementById('theme-toggle-btn');

    const isHomePage = window.location.pathname === '/' || window.location.pathname === '';

    function updateNavbar() {
        if (!isHomePage) return;
        const scrollY = window.scrollY || window.pageYOffset;
        const isDark = document.documentElement.classList.contains('dark');

        if (scrollY > 20) {
            if (isDark) {
                // Saat di-scroll (Dark Mode): Navy gelap solid
                header.className = 'fixed top-0 inset-x-0 z-50 w-full transition-colors duration-200 bg-[#080E1E] border-b border-[#1E2D4A] shadow-xs';

                if (brandLogoImg) {
                    brandLogoImg.classList.add('brightness-0', 'invert');
                }

                navLinks.forEach((link) => {
                    link.className = 'nav-link-item px-3 py-1 text-[13px] sm:text-sm font-medium rounded-lg transition-colors no-underline text-[#F1F5F9]/85 hover:text-white hover:bg-white/10';
                });

                if (localeBtn) {
                    localeBtn.className = 'inline-flex items-center justify-center px-2.5 py-1.5 h-8 rounded-lg text-xs font-semibold border transition-all cursor-pointer text-white border-white/20 hover:bg-white/10';
                }

                if (themeBtn) {
                    themeBtn.className = 'inline-flex items-center justify-center w-8 h-8 rounded-lg border transition-all cursor-pointer text-white border-white/20 hover:bg-white/10';
                }

                if (loginBtn) {
                    loginBtn.className = 'inline-flex items-center px-3.5 sm:px-4 py-1.5 h-8 rounded-lg text-xs font-semibold text-[#080E1E] bg-white hover:bg-neutral-100 transition-all no-underline shadow-xs';
                }

                if (dashboardBtn) {
                    dashboardBtn.className = 'hidden sm:inline-flex items-center px-3 py-1.5 h-8 rounded-lg text-xs font-medium text-white bg-white/15 hover:bg-white/25 transition-colors no-underline';
                }

                if (customerProfileBtn) {
                    customerProfileBtn.className = 'flex items-center justify-center w-8 h-8 rounded-full border transition-all cursor-pointer select-none group border-white/20 hover:border-white hover:bg-white/10';
                }

                if (logoutBtn) {
                    logoutBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-medium text-white/90 hover:text-white border border-white/20 hover:bg-white/10 transition-colors cursor-pointer bg-transparent';
                }

                if (mobileBtn) {
                    mobileBtn.className = 'lg:hidden inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-white hover:bg-white/10 transition-colors cursor-pointer border-0 bg-transparent';
                }
            } else {
                // Saat di-scroll (Light Mode): Putih solid
                header.className = 'fixed top-0 inset-x-0 z-50 w-full transition-colors duration-200 bg-white border-b border-[#E4E7EC] shadow-xs';

                if (brandLogoImg) {
                    brandLogoImg.classList.remove('brightness-0', 'invert');
                }

                navLinks.forEach((link) => {
                    link.className = 'nav-link-item px-3 py-1 text-[13px] sm:text-sm font-medium rounded-lg transition-colors no-underline text-[#101828]/80 hover:text-[#062072] hover:bg-[#062072]/5';
                });

                if (localeBtn) {
                    localeBtn.className = 'inline-flex items-center justify-center px-2.5 py-1.5 h-8 rounded-lg text-xs font-semibold border transition-all cursor-pointer text-[#101828] border-[#E4E7EC] hover:bg-[#F5F8FC]';
                }

                if (themeBtn) {
                    themeBtn.className = 'inline-flex items-center justify-center w-8 h-8 rounded-lg border transition-all cursor-pointer text-[#101828] border-[#E4E7EC] hover:bg-[#F5F8FC]';
                }

                if (loginBtn) {
                    loginBtn.className = 'inline-flex items-center px-3.5 sm:px-4 py-1.5 h-8 rounded-lg text-xs font-semibold text-white bg-[#062072] hover:bg-[#04184F] transition-all no-underline shadow-xs';
                }

                if (dashboardBtn) {
                    dashboardBtn.className = 'hidden sm:inline-flex items-center px-3 py-1.5 h-8 rounded-lg text-xs font-medium text-[#062072] bg-[#062072]/10 hover:bg-[#062072]/15 transition-colors no-underline';
                }

                if (customerProfileBtn) {
                    customerProfileBtn.className = 'flex items-center justify-center w-8 h-8 rounded-full border transition-all cursor-pointer select-none group border-[#E4E7EC] hover:border-[#062072]/40 hover:bg-[#F5F8FC]';
                }

                if (logoutBtn) {
                    logoutBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-medium text-[#667085] hover:text-[#101828] border border-[#E4E7EC] hover:bg-neutral-50 transition-colors cursor-pointer bg-transparent';
                }

                if (mobileBtn) {
                    mobileBtn.className = 'lg:hidden inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-[#101828] hover:bg-neutral-100 transition-colors cursor-pointer border-0 bg-transparent';
                }
            }
        } else {
            // Saat di paling atas: Transparan menyatu dengan hero biru navy
            header.className = 'fixed top-0 inset-x-0 z-50 w-full transition-colors duration-200 bg-transparent border-b border-transparent';

            if (brandLogoImg) {
                brandLogoImg.classList.add('brightness-0', 'invert');
            }

            navLinks.forEach((link) => {
                link.className = 'nav-link-item px-3 py-1 text-[13px] sm:text-sm font-medium rounded-lg transition-colors no-underline text-white/80 hover:text-white hover:bg-white/10';
            });

            if (localeBtn) {
                localeBtn.className = 'inline-flex items-center justify-center px-2.5 py-1.5 h-8 rounded-lg text-xs font-semibold border transition-all cursor-pointer text-white border-white/30 hover:bg-white/10';
            }

            if (themeBtn) {
                themeBtn.className = 'inline-flex items-center justify-center w-8 h-8 rounded-lg border transition-all cursor-pointer text-white border-white/30 hover:bg-white/10';
            }

            if (loginBtn) {
                loginBtn.className = 'inline-flex items-center px-3.5 sm:px-4 py-1.5 h-8 rounded-lg text-xs font-semibold text-[#062072] bg-white hover:bg-neutral-100 transition-all no-underline shadow-xs';
            }

            if (dashboardBtn) {
                dashboardBtn.className = 'hidden sm:inline-flex items-center px-3 py-1.5 h-8 rounded-lg text-xs font-medium text-white bg-white/15 hover:bg-white/25 transition-colors no-underline';
            }

            if (customerProfileBtn) {
                customerProfileBtn.className = 'flex items-center justify-center w-8 h-8 rounded-full border transition-all cursor-pointer select-none group border-white/40 hover:border-white hover:bg-white/15';
            }

            if (logoutBtn) {
                logoutBtn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-medium text-white/90 hover:text-white border border-white/20 hover:bg-white/10 transition-colors cursor-pointer bg-transparent';
            }

            if (mobileBtn) {
                mobileBtn.className = 'lg:hidden inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-white hover:bg-white/10 transition-colors cursor-pointer border-0 bg-transparent';
            }
        }
    }

    window.updateNavbarState = updateNavbar;
    window.addEventListener('scroll', updateNavbar, { passive: true });
    window.addEventListener('simaul:theme-change', updateNavbar, { passive: true });
    updateNavbar();
}

if (typeof window !== 'undefined') {
    window.initHeaderScroll = initHeaderScroll;
}
