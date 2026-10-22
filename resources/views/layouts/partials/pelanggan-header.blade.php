@php
    $isHome = request()->routeIs('customer.home');
    $isCustomerArea = request()->routeIs('customer.*') && !$isHome;
    $currentRoute = request()->route()?->getName() ?? '';
    $userRole = auth()->user()?->role?->name;
    $isCustomer = $userRole === 'customer';

    // Nav items berbeda untuk customer yang login di area customer vs landing
    if ($isCustomer && $isCustomerArea) {
        $navItems = [
            ['label' => 'Beranda', 'href' => route('customer.dashboard'), 'active' => request()->routeIs('customer.dashboard')],
            ['label' => 'Layanan', 'href' => route('customer.dashboard') . '#pilih-layanan', 'active' => false],
            ['label' => 'Aktivitas Saya', 'href' => route('customer.dashboard') . '#aktivitas-saya', 'active' => request()->routeIs('customer.booking.*') || request()->routeIs('customer.pesanan.*')],
            ['label' => 'Member & Poin', 'href' => route('customer.member'), 'active' => request()->routeIs('customer.member*')],
            ['label' => 'Ulasan', 'href' => route('customer.ulasan.index'), 'active' => request()->routeIs('customer.ulasan.*')],
        ];
    } else {
        $navItems = [
            ['label' => 'Beranda', 'href' => route('customer.home'), 'active' => $isHome],
            ['label' => 'Cabang', 'href' => $isHome ? '#cabang' : route('customer.home') . '#cabang', 'active' => false],
            ['label' => 'Layanan', 'href' => $isHome ? '#layanan' : route('customer.home') . '#layanan', 'active' => false],
            ['label' => 'Cara Kerja', 'href' => $isHome ? '#cara-kerja' : route('customer.home') . '#cara-kerja', 'active' => false],
            ['label' => 'Promo', 'href' => $isHome ? '#promo' : route('customer.home') . '#promo', 'active' => false],
            ['label' => 'Tentang', 'href' => $isHome ? '#tentang' : route('customer.home') . '#tentang', 'active' => false],
        ];
    }
@endphp

{{-- Header --}}
<header id="floating-header" class="fixed top-0 inset-x-0 z-50 pointer-events-none transition-all duration-300 px-4 sm:px-6 lg:px-8">
    <div id="navbar-bar"
         class="pointer-events-auto transition-all duration-300 ease-out {{ $isHome ? 'max-w-7xl mx-auto py-5 bg-transparent border-transparent shadow-none' : 'max-w-5xl mx-auto mt-2 sm:mt-3 px-5 sm:px-6 py-2.5 rounded-2xl bg-white/90 backdrop-blur-md border border-[#E4E7EC]/80 shadow-[0_4px_20px_-4px_rgba(16,24,40,0.06)]' }}">
        <div class="flex items-center justify-between gap-4">
            
            {{-- Brand Logo --}}
            <a href="{{ route('customer.home') }}" class="flex items-center no-underline shrink-0 group">
                <img id="brand-logo-img"
                     src="{{ asset('images/brand/logo-header.png') }}"
                     alt="SIMAUL"
                     class="h-9 sm:h-10 w-auto object-contain {{ $isHome ? 'brightness-0 invert' : '' }} transition-all">
            </a>

            {{-- Nav Items --}}
            <nav class="hidden lg:flex items-center gap-1">
                @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}"
                        class="nav-link-item px-3 py-1.5 text-sm font-medium rounded-lg transition-colors no-underline {{ $isHome ? 'text-white/80 hover:text-white hover:bg-white/10' : ($item['active'] ? 'text-[#062072] font-semibold bg-[#062072]/5' : 'text-[#101828]/80 hover:text-[#062072] hover:bg-[#062072]/5') }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Action Login / Profile --}}
            <div class="flex items-center gap-2.5">
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
                            class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium {{ $isHome ? 'text-white bg-white/15 hover:bg-white/25' : 'text-[#062072] bg-[#062072]/10 hover:bg-[#062072]/15' }} transition-colors no-underline">
                            Dashboard
                        </a>
                    @endif

                    {{-- Dropdown Profil & Keluar Pelanggan (Desktop & Tablet) --}}
                    <div class="relative hidden sm:block" id="customer-profile-menu">
                        <button type="button"
                            id="customer-profile-btn"
                            class="flex items-center gap-2 py-1 pl-1 pr-2.5 rounded-full transition-colors cursor-pointer select-none text-left group border-0 {{ $isHome ? 'text-white hover:bg-white/15' : 'text-[#101828] hover:bg-[#F5F8FC]' }}"
                            aria-haspopup="true"
                            aria-expanded="false"
                            aria-label="Menu Pelanggan: {{ $cleanUserName }}">
                            @if (auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}"
                                     alt="{{ $cleanUserName }}"
                                     class="w-7 h-7 rounded-full object-cover border border-[#E4E7EC] shrink-0">
                            @else
                                <div class="w-7 h-7 rounded-full {{ $isHome ? 'bg-white text-[#062072]' : 'bg-[#062072] text-white' }} flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                    {{ strtoupper(substr($cleanUserName, 0, 2)) }}
                                </div>
                            @endif

                            <span class="text-xs font-semibold max-w-[130px] truncate leading-tight transition-colors {{ $isHome ? 'text-white' : 'text-[#101828] group-hover:text-[#062072]' }}">
                                {{ $cleanUserName }}
                            </span>

                            <svg class="w-3.5 h-3.5 shrink-0 opacity-70 transition-transform group-aria-expanded:rotate-180 {{ $isHome ? 'text-white' : 'text-[#667085]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div id="customer-profile-dropdown"
                             class="hidden absolute right-0 top-full mt-2 w-52 bg-white rounded-xl shadow-xl border border-[#E4E7EC] z-50 overflow-hidden divide-y divide-[#F2F4F7]"
                             role="menu"
                             aria-orientation="vertical">
                            <div class="px-4 py-3 bg-[#F8FAFC]">
                                <p class="text-xs font-bold text-[#101828] truncate m-0 leading-tight">{{ $cleanUserName }}</p>
                                <p class="text-[11px] text-[#667085] truncate m-0 mt-0.5">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ $profileRoute }}"
                                   id="customer-profile-link"
                                   class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-[#344054] hover:bg-[#F5F8FC] hover:text-[#062072] transition-colors text-left no-underline cursor-pointer"
                                   role="menuitem">
                                    <svg class="w-4 h-4 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Profil</span>
                                </a>
                            </div>
                            <div class="py-1">
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit"
                                        id="header-logout-btn"
                                        title="Logout"
                                        class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors text-left cursor-pointer border-0 bg-transparent"
                                        role="menuitem">
                                        <x-icons.icon name="logout" class="w-4 h-4 text-rose-500" />
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        id="header-login-btn"
                        class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold {{ $isHome ? 'text-[#062072] bg-white hover:bg-neutral-100 shadow-xs' : 'text-white bg-[#062072] hover:bg-[#04184F]' }} transition-all no-underline">
                        Login
                    </a>
                @endif

                {{-- Mobile Button --}}
                <button type="button" onclick="toggleMobileNav()"
                    id="mobile-toggle-btn"
                    class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg {{ $isHome ? 'text-white hover:bg-white/10' : 'text-[#101828] hover:bg-neutral-100' }} transition-colors cursor-pointer border-0 bg-transparent"
                    aria-label="Buka navigasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Dropdown Panel --}}
        <div id="mobile-nav-panel" class="hidden lg:hidden mt-2 pt-3 rounded-2xl bg-white/95 backdrop-blur-md p-4 shadow-xl border border-[#E4E7EC] space-y-1">
            @if (auth()->check())
                <div class="py-2.5 px-3 mb-2 rounded-xl bg-[#F5F8FC] border border-[#E4E7EC] flex items-center gap-3">
                    @if (auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}"
                             alt="{{ $cleanUserName }}"
                             class="w-9 h-9 rounded-full object-cover border border-[#E4E7EC] shrink-0">
                    @else
                        <div class="w-9 h-9 rounded-full bg-[#062072] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs border border-[#062072]">
                            {{ strtoupper(substr($cleanUserName, 0, 2)) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-[#101828] truncate m-0">{{ $cleanUserName ?? 'Pelanggan' }}</p>
                        <p class="text-[11px] text-[#667085] truncate m-0">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <a href="{{ $profileRoute }}" onclick="closeMobileNav()"
                    id="mobile-profile-link"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-[#344054] hover:text-[#062072] hover:bg-[#F5F8FC] no-underline">
                    <svg class="w-4 h-4 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Profil</span>
                </a>
            @endif

            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}" onclick="closeMobileNav()"
                    class="block px-3 py-2 rounded-lg text-sm font-medium {{ $item['active'] ? 'text-[#062072] bg-[#062072]/5 font-semibold' : 'text-[#667085] hover:text-[#101828] hover:bg-[#F5F8FC]' }} no-underline">
                    {{ $item['label'] }}
                </a>
            @endforeach

            @if (auth()->check())
                @if ($dashboardRoute && !($isCustomer && $isCustomerArea))
                    <div class="pt-2 border-t border-[#E4E7EC]">
                        <a href="{{ $dashboardRoute }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-[#101828] bg-neutral-100 no-underline">
                            Dashboard
                        </a>
                    </div>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="m-0 pt-2 border-t border-[#E4E7EC]">
                    @csrf
                    <button type="submit"
                        title="Logout"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-rose-600 hover:bg-rose-50 cursor-pointer border-0 bg-transparent">
                        <x-icons.icon name="logout" class="w-4 h-4 text-rose-500" />
                        <span>Keluar</span>
                    </button>
                </form>
            @else
                <div class="pt-3 border-t border-[#E4E7EC]">
                    <a href="{{ route('login') }}" class="block text-center px-4 py-2 rounded-lg text-sm font-semibold text-white bg-[#062072] hover:bg-[#04184F] no-underline">
                        Login
                    </a>
                </div>
            @endif
        </div>
    </div>
</header>
