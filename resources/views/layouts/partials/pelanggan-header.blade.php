@php
    $isHome = request()->routeIs('customer.home');
    $isCustomerArea = request()->routeIs('customer.*') && !$isHome;
    $currentRoute = request()->route()?->getName() ?? '';
    $userRole = auth()->user()?->role?->name;
    $isCustomer = $userRole === 'customer';

    // Nav items berbeda untuk customer yang login di area customer vs landing
    if ($isCustomer && $isCustomerArea) {
        $navItems = [
            ['label' => __('public.navbar.home'), 'href' => route('customer.dashboard'), 'active' => request()->routeIs('customer.dashboard')],
            ['label' => __('public.navbar.my_activity'), 'href' => route('customer.aktivitas'), 'active' => request()->routeIs('customer.aktivitas*') || request()->routeIs('customer.booking.*') || request()->routeIs('customer.pesanan.*')],
            ['label' => __('public.navbar.member_points'), 'href' => route('customer.member'), 'active' => request()->routeIs('customer.member*')],
            ['label' => __('public.navbar.reviews'), 'href' => route('customer.ulasan.index'), 'active' => request()->routeIs('customer.ulasan.*')],
        ];
    } else {
        $navItems = [
            ['label' => __('public.navbar.home'), 'href' => route('customer.home'), 'active' => $isHome],
            ['label' => __('public.navbar.workflow'), 'href' => $isHome ? '#cara-kerja' : route('customer.home') . '#cara-kerja', 'active' => false],
            ['label' => __('public.navbar.branches'), 'href' => $isHome ? '#cabang' : route('customer.home') . '#cabang', 'active' => false],
            ['label' => __('public.navbar.services'), 'href' => $isHome ? '#layanan' : route('customer.home') . '#layanan', 'active' => false],
            ['label' => __('public.navbar.promo'), 'href' => $isHome ? '#promo' : route('customer.home') . '#promo', 'active' => false],
            ['label' => __('public.navbar.reviews'), 'href' => $isHome ? '#ulasan' : route('customer.home') . '#ulasan', 'active' => false],
        ];
    }
@endphp

{{-- Header: Navbar Standar Profesional Full-Width Ramping (56px mobile, 64px desktop) --}}
<header id="floating-header" class="fixed top-0 inset-x-0 z-50 w-full transition-colors duration-200 {{ $isHome ? 'bg-transparent border-b border-transparent' : 'bg-white border-b border-[#E4E7EC] shadow-xs' }}">
    <div id="navbar-bar" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-3 sm:gap-4 h-14 sm:h-16">
            
            {{-- Brand Logo --}}
            <a href="{{ route('customer.home') }}" class="flex items-center no-underline shrink-0 group">
                <img id="brand-logo-img"
                     src="{{ asset('images/brand/logo-header.png') }}"
                     alt="SIMAUL"
                     class="h-7 sm:h-8 w-auto object-contain {{ $isHome ? 'brightness-0 invert' : '' }} transition-all duration-200">
            </a>

            {{-- Nav Items --}}
            <nav class="hidden lg:flex items-center gap-1">
                @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}"
                        class="nav-link-item px-3 py-1 text-[13px] sm:text-sm font-medium rounded-lg transition-colors no-underline {{ $isHome ? 'text-white/80 hover:text-white hover:bg-white/10' : ($item['active'] ? 'text-[#062072] font-semibold bg-[#062072]/5' : 'text-[#101828]/80 hover:text-[#062072] hover:bg-[#062072]/5') }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Action Bahasa & Login / Profile --}}
            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                {{-- Tombol Bahasa Tunggal: ID 🇮🇩 atau EN 🇬🇧 (Tinggi 36px, px 10px) --}}
                <button type="button"
                        id="btn-switch-locale"
                        class="inline-flex items-center justify-center px-2.5 h-9 rounded-lg text-xs font-semibold border transition-all cursor-pointer {{ $isHome ? 'text-white border-white/30 hover:bg-white/10' : 'text-[#101828] dark:text-[#F8FAFC] border-[#E4E7EC] dark:border-[#1E2D4A] hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A]' }}"
                        aria-label="{{ app()->getLocale() === 'en' ? 'Ganti ke Bahasa Indonesia' : 'Switch to English' }}"
                        title="{{ app()->getLocale() === 'en' ? 'Ganti ke Bahasa Indonesia' : 'Switch to English' }}">
                    <span id="locale-label" class="tracking-wide">{{ app()->getLocale() === 'en' ? 'EN 🇬🇧' : 'ID 🇮🇩' }}</span>
                </button>

                @if (auth()->check())
                    @php
                        $cleanUserName = preg_replace('/\s*\(.*?\)\s*$/', '', auth()->user()->name);
                        $dashboardRoute = auth()->user()->getDashboardUrl();
                        $profileRoute = route('customer.profile');
                    @endphp

                    @include('layouts.partials.notification-bell')

                    @if ($dashboardRoute && !($isCustomer && $isCustomerArea))
                        <a href="{{ $dashboardRoute }}"
                            id="header-dashboard-btn"
                            class="hidden sm:inline-flex items-center px-3 h-9 rounded-lg text-xs font-medium {{ $isHome ? 'text-white bg-white/15 hover:bg-white/25' : 'text-[#062072] bg-[#062072]/10 hover:bg-[#062072]/15 dark:text-[#93C5FD] dark:bg-[#062072]/30' }} transition-colors no-underline">
                            {{ __('public.navbar.dashboard') }}
                        </a>
                    @endif

                    {{-- Dropdown Profil Pelanggan: Hanya Lingkaran Foto Profil 36x36px Tanpa Teks Nama --}}
                    <div class="relative hidden sm:block shrink-0" id="customer-profile-menu">
                        <button type="button"
                            id="customer-profile-btn"
                            class="flex items-center justify-center w-9 h-9 rounded-full border transition-all cursor-pointer select-none group {{ $isHome ? 'border-white/40 hover:border-white hover:bg-white/15' : 'border-[#E4E7EC] dark:border-[#1E2D4A] hover:border-[#062072]/40 hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A]' }}"
                            aria-haspopup="true"
                            aria-expanded="false"
                            aria-label="{{ __('public.navbar.profile') }}: {{ $cleanUserName }}"
                            title="{{ $cleanUserName }}">
                            @if (auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}"
                                     alt="{{ $cleanUserName }}"
                                     class="w-full h-full rounded-full object-cover">
                            @else
                                <div class="w-full h-full rounded-full {{ $isHome ? 'bg-white text-[#062072]' : 'bg-[#062072] text-white' }} flex items-center justify-center font-bold text-xs shadow-2xs">
                                     {{ strtoupper(substr($cleanUserName, 0, 2)) }}
                                </div>
                            @endif
                        </button>

                        {{-- Dropdown Menu --}}
                        <div id="customer-profile-dropdown"
                             class="hidden absolute right-0 top-full mt-2 w-52 bg-white dark:bg-[#111C35] rounded-xl shadow-xl border border-[#E4E7EC] dark:border-[#1E2D4A] z-50 overflow-hidden divide-y divide-[#F2F4F7] dark:divide-[#1E2D4A]"
                             role="menu"
                             aria-orientation="vertical">
                            <div class="px-4 py-3 bg-[#F8FAFC] dark:bg-[#0E172B]">
                                <p class="text-xs font-bold text-[#101828] dark:text-[#F8FAFC] truncate m-0 leading-tight">{{ $cleanUserName }}</p>
                                <p class="text-[11px] text-[#667085] dark:text-[#94A3B8] truncate m-0 mt-0.5">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ $profileRoute }}"
                                   id="customer-profile-link"
                                   class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-[#344054] dark:text-[#CBD5E1] hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] hover:text-[#062072] dark:hover:text-white transition-colors text-left no-underline cursor-pointer"
                                   role="menuitem">
                                    <svg class="w-4 h-4 text-[#667085] dark:text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>{{ __('public.navbar.profile') }}</span>
                                </a>
                            </div>
                            <div class="py-1">
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit"
                                        id="header-logout-btn"
                                        title="{{ __('public.navbar.logout') }}"
                                        class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors text-left cursor-pointer border-0 bg-transparent"
                                        role="menuitem">
                                        <x-icons.icon name="logout" class="w-4 h-4 text-rose-500" />
                                        <span>{{ __('public.navbar.logout') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        id="header-login-btn"
                        class="inline-flex items-center px-3.5 sm:px-4 h-9 rounded-lg text-xs font-semibold {{ $isHome ? 'text-[#062072] bg-white hover:bg-neutral-100 shadow-xs' : 'text-white bg-[#062072] hover:bg-[#04184F]' }} transition-all no-underline">
                        {{ __('public.navbar.login') }}
                    </a>
                @endif

                {{-- Mobile Button --}}
                <button type="button" onclick="toggleMobileNav()"
                    id="mobile-toggle-btn"
                    class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg {{ $isHome ? 'text-white hover:bg-white/10' : 'text-[#101828] dark:text-white hover:bg-neutral-100 dark:hover:bg-[#1E2D4A]' }} transition-colors cursor-pointer border-0 bg-transparent"
                    aria-label="{{ __('public.navbar.open_nav') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Dropdown Panel --}}
        <div id="mobile-nav-panel" class="hidden lg:hidden bg-white dark:bg-[#111C35] border-t border-[#E4E7EC] dark:border-[#1E2D4A] shadow-lg rounded-b-2xl p-4 space-y-1">
            @if (auth()->check())
                <div class="py-2.5 px-3 mb-2 rounded-xl bg-[#F5F8FC] dark:bg-[#0E172B] border border-[#E4E7EC] dark:border-[#1E2D4A] flex items-center gap-3">
                    @if (auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}"
                             alt="{{ $cleanUserName }}"
                             class="w-9 h-9 rounded-full object-cover border border-[#E4E7EC] dark:border-[#2D4166] shrink-0">
                    @else
                        <div class="w-9 h-9 rounded-full bg-[#062072] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs border border-[#062072]">
                            {{ strtoupper(substr($cleanUserName, 0, 2)) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-[#101828] dark:text-[#F8FAFC] truncate m-0">{{ $cleanUserName ?? 'Pelanggan' }}</p>
                        <p class="text-[11px] text-[#667085] dark:text-[#94A3B8] truncate m-0">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <a href="{{ $profileRoute }}" onclick="closeMobileNav()"
                    id="mobile-profile-link"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-[#344054] dark:text-[#CBD5E1] hover:text-[#062072] dark:hover:text-white hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A] no-underline">
                    <svg class="w-4 h-4 text-[#667085] dark:text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>{{ __('public.navbar.profile') }}</span>
                </a>
            @endif

            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}" onclick="closeMobileNav()"
                    class="block px-3 py-2 rounded-lg text-sm font-medium {{ $item['active'] ? 'text-[#062072] dark:text-white bg-[#062072]/5 dark:bg-white/10 font-semibold' : 'text-[#667085] dark:text-[#CBD5E1] hover:text-[#101828] dark:hover:text-white hover:bg-[#F5F8FC] dark:hover:bg-[#1E2D4A]' }} no-underline">
                    {{ $item['label'] }}
                </a>
            @endforeach

            {{-- Mobile Language Switch --}}
            <div class="pt-2 border-t border-[#E4E7EC] flex items-center justify-between px-3 py-2 text-xs text-[#667085]">
                <span>{{ __('public.navbar.switch_lang') }}</span>
                <button type="button"
                        id="mobile-switch-locale"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-[#F5F8FC] border border-[#E4E7EC] text-[#062072] cursor-pointer">
                    <span id="mobile-locale-label">{{ app()->getLocale() === 'en' ? 'EN 🇬🇧' : 'ID 🇮🇩' }}</span>
                </button>
            </div>

            @if (auth()->check())
                @if ($dashboardRoute && !($isCustomer && $isCustomerArea))
                    <div class="pt-2 border-t border-[#E4E7EC]">
                        <a href="{{ $dashboardRoute }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-[#101828] bg-neutral-100 no-underline">
                            {{ __('public.navbar.dashboard') }}
                        </a>
                    </div>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="m-0 pt-2 border-t border-[#E4E7EC]">
                    @csrf
                    <button type="submit"
                        title="{{ __('public.navbar.logout') }}"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-rose-600 hover:bg-rose-50 cursor-pointer border-0 bg-transparent">
                        <x-icons.icon name="logout" class="w-4 h-4 text-rose-500" />
                        <span>{{ __('public.navbar.logout') }}</span>
                    </button>
                </form>
            @else
                <div class="pt-3 border-t border-[#E4E7EC]">
                    <a href="{{ route('login') }}" class="block text-center px-4 py-2 rounded-lg text-sm font-semibold text-white bg-[#062072] hover:bg-[#04184F] no-underline">
                        {{ __('public.navbar.login') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</header>
