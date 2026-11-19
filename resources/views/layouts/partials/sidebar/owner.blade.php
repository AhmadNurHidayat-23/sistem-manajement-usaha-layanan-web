
<aside id="sidebar-nav" class="sidebar-nav fixed top-0 left-0 bg-white text-[#101828] flex flex-col h-screen z-30 select-none border-r border-[#E4E7EC] {{ ($isCollapsed ?? false) ? 'collapsed' : '' }}">
    {{-- Header Sidebar (h-[72px] seragam dengan Topbar, tanpa garis pembatas horizontal) --}}
    <div class="sidebar-header h-[72px] flex items-center justify-between px-5 sm:px-6 bg-white shrink-0">
        {{-- Logo Brand Full (Saat sidebar terbuka 250px & mobile drawer) --}}
        <a href="{{ route('owner.dashboard') }}" class="sidebar-brand-full flex items-center min-w-0" title="SIMAUL">
            <img src="{{ asset('images/brand/logo-header.png') }}" alt="SIMAUL" class="h-9 sm:h-10 w-auto max-w-[170px] object-contain ml-1">
        </a>

        {{-- Logo Brand Mini (Saat sidebar collapsed 68px: tombol untuk membuka kembali ke 250px) --}}
        <button type="button"
                class="sidebar-brand-mini sidebar-toggle-btn items-center justify-center p-1.5 rounded-xl hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] hover:scale-105 active:scale-95 transition-all cursor-pointer border border-transparent hover:border-[#E4E7EC] dark:hover:border-[#1E2D4A]"
                title="{{ __('dashboard.topbar.open_nav') }}"
                aria-label="{{ __('dashboard.topbar.open_nav') }}">
            <img src="{{ asset('images/brand/favicon.png') }}" alt="SIMAUL" class="w-[30px] h-[30px] rounded-lg object-contain shadow-xs shrink-0 pointer-events-none">
        </button>

        {{-- Tombol Toggle Desktop (Kecilkan ke 68px saat sidebar terbuka) --}}
        <button type="button"
                class="sidebar-toggle-btn sidebar-toggle-close hidden lg:flex items-center justify-center w-8 h-8 rounded-lg border border-[#E4E7EC] dark:border-[#1E2D4A] hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] text-[#667085] hover:text-[#062072] dark:hover:text-[#93C5FD] transition-colors cursor-pointer shrink-0"
                title="{{ __('dashboard.topbar.collapse_sidebar') }}"
                aria-label="{{ __('dashboard.topbar.collapse_sidebar') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </button>

        {{-- Tombol Tutup Mobile Drawer --}}
        <button type="button"
                onclick="closeMobileSidebar()"
                class="sidebar-close-btn lg:hidden flex items-center justify-center w-8 h-8 rounded-lg border border-[#E4E7EC] dark:border-[#1E2D4A] hover:bg-rose-50 dark:hover:bg-rose-950/30 text-[#667085] hover:text-rose-600 transition-colors cursor-pointer shrink-0"
                title="{{ __('dashboard.topbar.close_nav') }}"
                aria-label="{{ __('dashboard.topbar.close_nav') }}">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="flex-1 px-3 py-3 space-y-3.5 overflow-x-hidden overflow-y-auto">
        <!-- Seksi UTAMA -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085] dark:text-[#94A3B8]">
                {{ __('dashboard.sections.main') }}
            </div>
            <a href="{{ route('owner.dashboard') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('owner.dashboard') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] dark:text-[#CBD5E1] hover:text-[#062072] dark:hover:text-white hover:bg-[#EBF2FE] dark:hover:bg-[#1E2D4A]' }}"
               title="{{ __('dashboard.nav.dashboard') }}">
                <x-icons.icon name="dashboard" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('owner.dashboard') ? 'text-white' : 'text-[#667085] dark:text-[#94A3B8]' }}" />
                <span class="sidebar-label truncate">{{ __('dashboard.nav.dashboard') }}</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">{{ __('dashboard.nav.dashboard') }}</span>
            </a>
        </div>

        <!-- Seksi MANAJEMEN BISNIS -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085] dark:text-[#94A3B8]">
                {{ __('dashboard.sections.business_mgmt') }}
            </div>
            <a href="{{ route('branches.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('branches.*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] dark:text-[#CBD5E1] hover:text-[#062072] dark:hover:text-white hover:bg-[#EBF2FE] dark:hover:bg-[#1E2D4A]' }}"
               title="{{ __('dashboard.nav.branches') }}">
                <x-icons.icon name="branch" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('branches.*') ? 'text-white' : 'text-[#667085] dark:text-[#94A3B8]' }}" />
                <span class="sidebar-label truncate">{{ __('dashboard.nav.branches') }}</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">{{ __('dashboard.nav.branches') }}</span>
            </a>
            <a href="{{ route('catalog.services.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('catalog.*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] dark:text-[#CBD5E1] hover:text-[#062072] dark:hover:text-white hover:bg-[#EBF2FE] dark:hover:bg-[#1E2D4A]' }}"
               title="{{ __('dashboard.nav.catalog') }}">
                <x-icons.icon name="service" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('catalog.*') ? 'text-white' : 'text-[#667085] dark:text-[#94A3B8]' }}" />
                <span class="sidebar-label truncate">{{ __('dashboard.nav.catalog') }}</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">{{ __('dashboard.nav.catalog') }}</span>
            </a>
            <a href="{{ route('promotions.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('promotions.*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] dark:text-[#CBD5E1] hover:text-[#062072] dark:hover:text-white hover:bg-[#EBF2FE] dark:hover:bg-[#1E2D4A]' }}"
               title="{{ __('dashboard.nav.promotions') }}">
                <x-icons.icon name="ticket" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('promotions.*') ? 'text-white' : 'text-[#667085] dark:text-[#94A3B8]' }}" />
                <span class="sidebar-label truncate">{{ __('dashboard.nav.promotions') }}</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">{{ __('dashboard.nav.promotions') }}</span>
            </a>
        </div>

        <!-- Seksi KARYAWAN -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085] dark:text-[#94A3B8]">
                {{ __('dashboard.sections.employees') }}
            </div>
            <a href="{{ route('employees.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('employees.*') && !request()->routeIs('employees.commissions.*') && !request()->routeIs('employees.my-*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] dark:text-[#CBD5E1] hover:text-[#062072] dark:hover:text-white hover:bg-[#EBF2FE] dark:hover:bg-[#1E2D4A]' }}"
               title="{{ __('dashboard.nav.employees_data') }}">
                <x-icons.icon name="employee" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('employees.*') && !request()->routeIs('employees.commissions.*') && !request()->routeIs('employees.my-*') ? 'text-white' : 'text-[#667085] dark:text-[#94A3B8]' }}" />
                <span class="sidebar-label truncate">{{ __('dashboard.nav.employees_data') }}</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">{{ __('dashboard.nav.employees_data') }}</span>
            </a>
            <a href="{{ route('employees.commissions.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('employees.commissions.*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] dark:text-[#CBD5E1] hover:text-[#062072] dark:hover:text-white hover:bg-[#EBF2FE] dark:hover:bg-[#1E2D4A]' }}"
               title="{{ __('dashboard.nav.commissions') }}">
                <x-icons.icon name="money" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('employees.commissions.*') ? 'text-white' : 'text-[#667085] dark:text-[#94A3B8]' }}" />
                <span class="sidebar-label truncate">{{ __('dashboard.nav.commissions') }}</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">{{ __('dashboard.nav.commissions') }}</span>
            </a>
        </div>

        <!-- Seksi ADMINISTRASI -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085] dark:text-[#94A3B8]">
                {{ __('dashboard.sections.administration') }}
            </div>
            <a href="{{ route('owner.internal-users.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('owner.internal-users.*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] dark:text-[#CBD5E1] hover:text-[#062072] dark:hover:text-white hover:bg-[#EBF2FE] dark:hover:bg-[#1E2D4A]' }}"
               title="{{ __('dashboard.nav.internal_users') }}">
                <x-icons.icon name="user" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('owner.internal-users.*') ? 'text-white' : 'text-[#667085] dark:text-[#94A3B8]' }}" />
                <span class="sidebar-label truncate">{{ __('dashboard.nav.internal_users') }}</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">{{ __('dashboard.nav.internal_users') }}</span>
            </a>
        </div>

        <!-- Seksi LAPORAN -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085] dark:text-[#94A3B8]">
                {{ __('dashboard.sections.reports') }}
            </div>
            <a href="{{ route('reports.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('reports.*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] dark:text-[#CBD5E1] hover:text-[#062072] dark:hover:text-white hover:bg-[#EBF2FE] dark:hover:bg-[#1E2D4A]' }}"
               title="{{ __('dashboard.nav.reports_finance') }}">
                <x-icons.icon name="report" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-[#667085] dark:text-[#94A3B8]' }}" />
                <span class="sidebar-label truncate">{{ __('dashboard.nav.reports_finance') }}</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">{{ __('dashboard.nav.reports_finance') }}</span>
            </a>
        </div>
    </nav>

    <!-- Tombol Keluar di Bawah (tanpa garis pembatas horizontal) -->
    <div class="sidebar-footer-section px-3 pb-3 pt-2.5 bg-white shrink-0">
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="sidebar-link sidebar-logout-btn w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] font-medium text-[#344054] dark:text-[#CBD5E1] hover:bg-rose-50 dark:hover:bg-rose-950/30 hover:text-rose-600 dark:hover:text-rose-400 transition-colors cursor-pointer group focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-rose-500" title="{{ __('dashboard.nav.logout_account') }}">
                <x-icons.icon name="logout" class="w-4.5 h-4.5 shrink-0 text-[#667085] dark:text-[#94A3B8] group-hover:text-rose-600 dark:group-hover:text-rose-400" />
                <span class="sidebar-logout-label truncate">{{ __('dashboard.nav.logout') }}</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">{{ __('dashboard.nav.logout') }}</span>
            </button>
        </form>
    </div>
</aside>
