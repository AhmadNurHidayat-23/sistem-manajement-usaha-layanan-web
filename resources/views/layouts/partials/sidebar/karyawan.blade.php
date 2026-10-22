@php
    $isQueueActive = request()->routeIs('queue.*')
        || request()->routeIs('bookings.show')
        || request()->routeIs('orders.show')
        || request()->routeIs('employee.tugas.detail');
@endphp

<aside id="sidebar-nav" class="sidebar-nav fixed top-0 left-0 bg-white text-[#101828] flex flex-col h-screen z-30 select-none border-r border-[#E4E7EC] {{ ($isCollapsed ?? false) ? 'collapsed' : '' }}">
    
    <div class="sidebar-header h-[72px] flex items-center justify-between px-5 sm:px-6 border-b border-[#E4E7EC] bg-white shrink-0">
        <div class="flex items-center min-w-0">
            {{-- Logo Brand Full (Sama persis dengan aset header landing page) --}}
            <a href="{{ route('employee.dashboard') }}" class="sidebar-brand-full flex items-center" title="SIMAUL">
                <img src="{{ asset('images/brand/logo-header.png') }}" alt="SIMAUL" class="h-9 sm:h-10 w-auto max-w-[170px] object-contain ml-1">
            </a>
           
            {{-- Logo Brand Mini (Saat sidebar collapse) --}}
            <a href="{{ route('employee.dashboard') }}" class="sidebar-brand-mini" title="SIMAUL">
                <img src="{{ asset('images/brand/favicon.png') }}" alt="SIMAUL" class="w-8 h-8 rounded-lg object-contain shadow-xs">
            </a>
        </div>
       
        {{-- Tombol Toggle Desktop (Kecilkan/Perlebar Sidebar) --}}
        <button type="button" class="sidebar-toggle-btn hidden lg:flex items-center justify-center p-1.5 rounded-lg border border-[#E4E7EC] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#062072] transition-colors cursor-pointer shrink-0" title="Kecilkan Sidebar" aria-label="Kecilkan Sidebar">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </button>

        {{-- Tombol Tutup Mobile Drawer --}}
        <button type="button" onclick="closeMobileSidebar()" class="sidebar-close-btn lg:hidden flex items-center justify-center w-8 h-8 rounded-lg border border-[#E4E7EC] hover:bg-rose-50 text-[#667085] hover:text-rose-600 transition-colors cursor-pointer shrink-0" title="Tutup Menu Navigasi" aria-label="Tutup Menu Navigasi">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="flex-1 px-3 py-3 space-y-3.5 overflow-x-hidden overflow-y-auto">
        <!-- Seksi UTAMA -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085]">
                Utama
            </div>
            <a href="{{ route('employee.dashboard') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('employee.dashboard') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] hover:text-[#062072] hover:bg-[#EBF2FE]' }}"
               title="Dashboard">
                <x-icons.icon name="dashboard" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('employee.dashboard') ? 'text-white' : 'text-[#667085]' }}" />
                <span class="sidebar-label truncate">Dashboard</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Dashboard</span>
            </a>
        </div>

        <!-- Seksi OPERASIONAL -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085]">
                Operasional
            </div>
            <a href="{{ route('queue.index') }}"
               data-alias="Antrian"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ $isQueueActive ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] hover:text-[#062072] hover:bg-[#EBF2FE]' }}"
               title="Antrean & Tugas">
                <x-icons.icon name="clock" class="w-4.5 h-4.5 shrink-0 {{ $isQueueActive ? 'text-white' : 'text-[#667085]' }}" />
                <span class="sidebar-label truncate">Antrean & Tugas</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Antrean & Tugas</span>
            </a>
            <a href="{{ route('employees.my-shifts') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('employees.my-shifts') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] hover:text-[#062072] hover:bg-[#EBF2FE]' }}"
               title="Jadwal Shift">
                <x-icons.icon name="calendar" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('employees.my-shifts') ? 'text-white' : 'text-[#667085]' }}" />
                <span class="sidebar-label truncate">Jadwal Shift</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Jadwal Shift</span>
            </a>
        </div>

        <!-- Seksi KINERJA -->
        <div class="space-y-1">
            <div class="sidebar-section-header px-3 pt-1.5 pb-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#667085]">
                Kinerja
            </div>
            <a href="{{ route('employees.my-commissions') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 relative focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#062072] {{ request()->routeIs('employees.my-commissions') ? 'bg-[#062072] text-white font-semibold shadow-xs' : 'text-[#344054] hover:text-[#062072] hover:bg-[#EBF2FE]' }}"
               title="Komisi Saya">
                <x-icons.icon name="star" class="w-4.5 h-4.5 shrink-0 {{ request()->routeIs('employees.my-commissions') ? 'text-white' : 'text-[#667085]' }}" />
                <span class="sidebar-label truncate">Komisi Saya</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Komisi Saya</span>
            </a>
        </div>
    </nav>

    <!-- Tombol Keluar di Bawah -->
    <div class="sidebar-footer-section px-3 pb-3 pt-2.5 border-t border-[#E4E7EC] bg-white shrink-0">
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="sidebar-link sidebar-logout-btn w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] font-medium text-[#344054] hover:bg-rose-50 hover:text-rose-600 transition-colors cursor-pointer group focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-rose-500" title="Keluar dari Akun">
                <x-icons.icon name="logout" class="w-4.5 h-4.5 shrink-0 text-[#667085] group-hover:text-rose-600" />
                <span class="sidebar-logout-label truncate">Keluar</span>
                <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1 bg-[#101828] text-white text-xs rounded-md whitespace-nowrap z-50 shadow-lg border border-white/10 pointer-events-none">Keluar</span>
            </button>
        </form>
    </div>
</aside>
