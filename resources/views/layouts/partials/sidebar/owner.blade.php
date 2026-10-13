@php
    $user = auth()->user();
    $cleanName = preg_replace('/\s*\(.*?\)\s*$/', '', $user?->name ?? 'Owner');
@endphp

<aside id="sidebar-nav" class="sidebar-nav fixed top-0 left-0 bg-white text-[#101828] flex flex-col h-screen z-30 select-none border-r border-[#E4E7EC] {{ ($isCollapsed ?? false) ? 'collapsed' : '' }}">
    {{-- Header Sidebar (h-[72px] seragam dengan Topbar) --}}
    <div class="sidebar-header h-[72px] flex items-center justify-between px-5 sm:px-6 border-b border-[#E4E7EC] bg-white shrink-0">
        <div class="flex items-center min-w-0">
            {{-- Logo Brand Full (Sama persis dengan header landing page publik SIMAUL) --}}
            <a href="{{ route('owner.dashboard') }}" class="sidebar-brand-full flex items-center" title="SIMAUL">
                <img src="{{ asset('images/brand/logo-header.png') }}" alt="SIMAUL" class="h-9 sm:h-10 w-auto max-w-[170px] object-contain ml-1">
            </a>

            {{-- Logo Brand Mini (Saat sidebar collapse) --}}
            <a href="{{ route('owner.dashboard') }}" class="sidebar-brand-mini" title="SIMAUL">
                <img src="{{ asset('images/brand/favicon.png') }}" alt="SIMAUL" class="w-8 h-8 rounded-lg object-contain shadow-xs">
            </a>
        </div>

        <button type="button" class="sidebar-toggle-btn p-1.5 rounded-lg border border-[#E4E7EC] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#062072] transition-colors cursor-pointer shrink-0" title="Toggle Sidebar" aria-label="Toggle Sidebar">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </button>
    </div>

    <nav class="flex-1 px-3 py-3 space-y-3.5 overflow-x-hidden overflow-y-auto">
        <!-- Seksi UTAMA -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085]">
                Utama
            </div>
            <a href="{{ route('owner.dashboard') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('owner.dashboard') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] hover:text-[#062072] hover:bg-[#EBF2FE]' }}"
               title="Dashboard">
                <x-icons.icon name="dashboard" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('owner.dashboard') ? 'text-white' : 'text-[#667085]' }}" />
                <span class="sidebar-label truncate">Dashboard</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Dashboard</span>
            </a>
        </div>

        <!-- Seksi MANAJEMEN BISNIS -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085]">
                Manajemen Bisnis
            </div>
            <a href="{{ route('branches.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('branches.*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] hover:text-[#062072] hover:bg-[#EBF2FE]' }}"
               title="Cabang">
                <x-icons.icon name="branch" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('branches.*') ? 'text-white' : 'text-[#667085]' }}" />
                <span class="sidebar-label truncate">Cabang</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Cabang</span>
            </a>
            <a href="{{ route('catalog.services.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('catalog.*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] hover:text-[#062072] hover:bg-[#EBF2FE]' }}"
               title="Katalog Layanan">
                <x-icons.icon name="service" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('catalog.*') ? 'text-white' : 'text-[#667085]' }}" />
                <span class="sidebar-label truncate">Katalog Layanan</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Katalog Layanan</span>
            </a>
            <a href="{{ route('promotions.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('promotions.*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] hover:text-[#062072] hover:bg-[#EBF2FE]' }}"
               title="Promo & Voucher">
                <x-icons.icon name="ticket" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('promotions.*') ? 'text-white' : 'text-[#667085]' }}" />
                <span class="sidebar-label truncate">Promo & Voucher</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Promo & Voucher</span>
            </a>
        </div>

        <!-- Seksi KARYAWAN -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085]">
                Karyawan
            </div>
            <a href="{{ route('employees.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('employees.*') && !request()->routeIs('employees.commissions.*') && !request()->routeIs('employees.my-*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] hover:text-[#062072] hover:bg-[#EBF2FE]' }}"
               title="Data Karyawan">
                <x-icons.icon name="employee" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('employees.*') && !request()->routeIs('employees.commissions.*') && !request()->routeIs('employees.my-*') ? 'text-white' : 'text-[#667085]' }}" />
                <span class="sidebar-label truncate">Data Karyawan</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Data Karyawan</span>
            </a>
            <a href="{{ route('employees.commissions.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('employees.commissions.*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] hover:text-[#062072] hover:bg-[#EBF2FE]' }}"
               title="Skema & Komisi">
                <x-icons.icon name="money" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('employees.commissions.*') ? 'text-white' : 'text-[#667085]' }}" />
                <span class="sidebar-label truncate">Skema & Komisi</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Skema & Komisi</span>
            </a>
        </div>

        <!-- Seksi ADMINISTRASI -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085]">
                Administrasi
            </div>
            <a href="{{ route('owner.internal-users.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('owner.internal-users.*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] hover:text-[#062072] hover:bg-[#EBF2FE]' }}"
               title="Akun & Hak Akses">
                <x-icons.icon name="user" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('owner.internal-users.*') ? 'text-white' : 'text-[#667085]' }}" />
                <span class="sidebar-label truncate">Akun & Hak Akses</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Akun & Hak Akses</span>
            </a>
        </div>

        <!-- Seksi LAPORAN -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085]">
                Laporan
            </div>
            <a href="{{ route('reports.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('reports.*') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] hover:text-[#062072] hover:bg-[#EBF2FE]' }}"
               title="Laporan Keuangan">
                <x-icons.icon name="report" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-[#667085]' }}" />
                <span class="sidebar-label truncate">Laporan Keuangan</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Laporan Keuangan</span>
            </a>
        </div>
    </nav>

    <!-- Tombol Keluar + Identitas Owner di Bawah -->
    <div class="sidebar-footer-section px-3 pb-3 pt-2.5 border-t border-[#E4E7EC] bg-white shrink-0 flex flex-col gap-2">
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="sidebar-link sidebar-logout-btn w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] font-medium text-[#344054] hover:bg-rose-50 hover:text-rose-600 transition-colors cursor-pointer group focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-rose-500" title="Keluar dari Akun">
                <x-icons.icon name="logout" class="w-4.5 h-4.5 shrink-0 text-[#667085] group-hover:text-rose-600" />
                <span class="sidebar-logout-label truncate">Keluar</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Keluar</span>
            </button>
        </form>

        <div class="flex items-center gap-2.5 px-1 py-1">
            @if(auth()->user()?->avatar_url)
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ $cleanName }}" class="w-8 h-8 rounded-full object-cover shrink-0 shadow-xs border border-[#E4E7EC]">
            @else
                <div class="w-8 h-8 rounded-full bg-[#062072] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    {{ strtoupper(substr($cleanName, 0, 2)) }}
                </div>
            @endif
            <div class="sidebar-user-info flex-1 min-w-0">
                <p class="text-xs font-semibold text-[#101828] truncate m-0 leading-tight">{{ $cleanName }}</p>
                <p class="text-[11px] text-[#667085] truncate m-0 font-medium leading-none mt-1">Owner</p>
            </div>
        </div>
    </div>
</aside>
