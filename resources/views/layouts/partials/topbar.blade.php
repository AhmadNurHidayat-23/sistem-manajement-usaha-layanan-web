@php
    $user = auth()->user();
    $roleName = $user?->role?->name ?? 'Guest';
    $cleanName = preg_replace('/\s*\(.*?\)\s*$/', '', $user?->name ?? 'User');
    $roleLabel = match($roleName) {
        'owner' => __('dashboard.roles.owner'),
        'manager' => __('dashboard.roles.manager'),
        'cashier' => __('dashboard.roles.cashier'),
        'employee' => __('dashboard.roles.employee'),
        'customer' => __('dashboard.roles.customer'),
        default => ucfirst($roleName),
    };
    $isInternalStaff = in_array($roleName, ['owner', 'manager', 'cashier', 'employee'], true);
@endphp

<!-- Header / Topbar: Tinggi sama persis dengan sidebar header (h-[72px]) -->
<header class="h-[72px] bg-white dark:bg-[#111C35] border-b border-[#E4E7EC] dark:border-[#1E2D4A] px-3 sm:px-6 flex items-center justify-between shrink-0 z-20">
    <!-- Sisi Kiri: Mobile Toggle + Jam & Tanggal Real-time -->
    <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
        {{-- Mobile Drawer Toggle Button --}}
        <button type="button"
                id="mobile-sidebar-toggle-btn"
                class="lg:hidden w-9 h-9 rounded-lg flex items-center justify-center text-[#101828] dark:text-[#F8FAFC] hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] border border-[#E4E7EC] dark:border-[#1E2D4A] transition-colors cursor-pointer"
                title="{{ __('dashboard.topbar.open_nav') }}"
                aria-label="{{ __('dashboard.topbar.open_nav') }}">
            <svg class="w-5 h-5 text-[#062072] dark:text-[#93C5FD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div id="header-realtime-clock" class="flex flex-col text-left select-none" aria-hidden="true">
            <span id="realtime-clock-time" class="text-xs sm:text-[14px] leading-tight font-semibold sm:font-medium text-[#101828] dark:text-[#F8FAFC]"></span>
            <span id="realtime-clock-date" class="hidden sm:inline-block text-[11px] sm:text-[13px] leading-tight text-[#667085] sm:text-[#101828] dark:text-[#94A3B8] sm:dark:text-[#CBD5E1] mt-[1px] sm:mt-[2px]"></span>
        </div>
    </div>

    <!-- Sisi Kanan Urutan: 1. Bahasa -> 2. Notifikasi -> 3. Dark Mode (Bulat) -> 4. Profil -->
    <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
        {{-- 1. Tombol Pergantian Bahasa ID / EN (tinggi 36px, padding 10px) --}}
        <x-locale-switch variant="topbar" id="btn-switch-locale" />

        {{-- 2. Ikon Notifikasi (36 × 36px, ikon 20px) --}}
        @include('layouts.partials.notification-bell')

        {{-- 3. Tombol Dark Mode Berbentuk Lingkaran Sempurna (36 × 36px, ikon 19px) --}}
        <button type="button"
                id="internal-theme-toggle-btn"
                class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center text-[#667085] hover:text-[#101828] dark:text-[#94A3B8] dark:hover:text-[#F8FAFC] bg-white dark:bg-[#0E172E] hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] border border-[#E4E7EC] dark:border-[#1E2D4A] transition-colors cursor-pointer select-none focus:outline-none"
                title="{{ __('dashboard.topbar.toggle_theme') }}"
                aria-label="{{ __('dashboard.topbar.toggle_dark_mode') }}">
            <svg id="internal-theme-moon" class="w-[19px] h-[19px] block dark:hidden text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg id="internal-theme-sun" class="w-[19px] h-[19px] hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </button>

        {{-- 4. Foto Profil Pengguna Berbentuk Lingkaran (36 × 36px) --}}
        <div class="relative shrink-0 flex items-center justify-center" id="header-profile-menu">
            <button type="button"
                    id="header-profile-btn"
                    class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] transition-colors cursor-pointer select-none border border-transparent hover:border-[#E4E7EC] dark:hover:border-[#1E2D4A] focus:outline-hidden p-0"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-label="{{ __('dashboard.topbar.profile_menu') }}: {{ $cleanName }}"
                    title="{{ __('dashboard.topbar.profile_menu') }}: {{ $cleanName }}">
                @if ($user?->avatar_url)
                    <img src="{{ $user->avatar_url }}"
                         alt="{{ $cleanName }}"
                         class="w-9 h-9 rounded-full object-cover border border-[#E4E7EC] dark:border-[#1E2D4A] shrink-0">
                @else
                    <div class="w-9 h-9 rounded-full bg-[#062072] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs border border-[#062072]">
                        {{ strtoupper(substr($cleanName, 0, 2)) }}
                    </div>
                @endif
            </button>

            {{-- Dropdown Menu Profil --}}
            <div id="header-profile-dropdown"
                 class="hidden absolute right-0 top-full mt-2 w-56 bg-white dark:bg-[#111C35] rounded-xl shadow-xl border border-[#E4E7EC] dark:border-[#1E2D4A] z-50 overflow-hidden divide-y divide-[#F2F4F7] dark:divide-[#1E2D4A]"
                 role="menu"
                 aria-orientation="vertical">
                <div class="px-4 py-3 bg-[#F8FAFC] dark:bg-[#0E172E]">
                    <p class="text-xs font-bold text-[#101828] dark:text-[#F8FAFC] truncate m-0 leading-tight">{{ $cleanName }}</p>
                    <p class="text-[11px] text-[#667085] dark:text-[#94A3B8] truncate m-0 mt-0.5">{{ $user?->email }}</p>
                    <span class="mt-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-[#EBF2FE] dark:bg-[#062072]/30 text-[#062072] dark:text-[#93C5FD]">
                        {{ $roleLabel }}
                    </span>
                </div>
                <div class="py-1">
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors text-left cursor-pointer border-0 bg-transparent"
                                role="menuitem">
                            <x-icons.icon name="logout" class="w-4 h-4 text-rose-500" />
                            <span>{{ __('dashboard.nav.logout_account') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
