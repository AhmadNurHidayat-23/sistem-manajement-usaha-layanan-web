@php
    $user = auth()->user();
    $roleName = $user?->role?->name ?? 'Guest';
    $cleanName = preg_replace('/\s*\(.*?\)\s*$/', '', $user?->name ?? 'User');
    $roleLabel = match($roleName) {
        'owner' => 'Owner',
        'manager' => 'Manajer',
        'cashier' => 'Kasir',
        'employee' => 'Karyawan',
        'customer' => 'Pelanggan',
        default => ucfirst($roleName),
    };
    $isInternalStaff = in_array($roleName, ['owner', 'manager', 'cashier', 'employee'], true);
@endphp

<!-- Header / Topbar: Tinggi sama persis dengan sidebar header (h-[72px]) -->
<header class="h-[72px] bg-white border-b border-[#E4E7EC] px-3 sm:px-6 flex items-center justify-between shrink-0 z-20">
    <!-- Sisi Kiri: Mobile Toggle + Jam & Tanggal Real-time -->
    <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
        <button type="button"
                id="mobile-sidebar-toggle-btn"
                class="lg:hidden w-9 h-9 rounded-lg flex items-center justify-center text-[#101828] hover:bg-[#F5F8FC] border border-[#E4E7EC] transition-colors cursor-pointer"
                title="Buka Navigasi"
                aria-label="Buka Menu Navigasi">
            <svg class="w-5 h-5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div id="header-realtime-clock" class="flex flex-col text-left select-none" aria-hidden="true">
            <span id="realtime-clock-time" class="text-xs sm:text-[14px] leading-tight font-semibold sm:font-medium text-[#101828]"></span>
            <span id="realtime-clock-date" class="hidden sm:inline-block text-[11px] sm:text-[13px] leading-tight text-[#667085] sm:text-[#101828] mt-[1px] sm:mt-[2px]"></span>
        </div>
    </div>

    <!-- Sisi Kanan: Urutan: Pencarian -> Notifikasi -> Foto Profil + Nama Pengguna -->
    <div class="flex items-center gap-1.5 xs:gap-2 sm:gap-3 shrink-0">
        {{-- 1. Pencarian Menu (Pill Rounded-Full, Tanpa Dropdown) --}}
        @include('layouts.partials.menu-search')

        {{-- 2. Lonceng Notifikasi --}}
        @include('layouts.partials.notification-bell')

        {{-- 3. Profil: Foto Profil Berbentuk Lingkaran + Nama Pengguna --}}
        <div class="relative" id="header-profile-menu">
            <button type="button"
                    id="header-profile-btn"
                    class="flex items-center gap-1.5 sm:gap-2.5 py-1 pl-1 pr-1 sm:pr-3 rounded-full hover:bg-[#F5F8FC] transition-colors cursor-pointer select-none border border-transparent hover:border-[#E4E7EC] text-left group"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-label="Menu Profil: {{ $cleanName }}">
                @if ($user?->avatar_url)
                    <img src="{{ $user->avatar_url }}"
                         alt="{{ $cleanName }}"
                         class="w-8 h-8 rounded-full object-cover border border-[#E4E7EC] shrink-0">
                @else
                    <div class="w-8 h-8 rounded-full bg-[#062072] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs border border-[#062072]">
                        {{ strtoupper(substr($cleanName, 0, 2)) }}
                    </div>
                @endif

                <span class="hidden sm:inline-block text-xs sm:text-[13px] font-semibold text-[#101828] group-hover:text-[#062072] max-w-[120px] sm:max-w-[150px] md:max-w-[180px] truncate leading-tight transition-colors">
                    {{ $cleanName }}
                </span>
            </button>

            {{-- Dropdown Menu Profil --}}
            <div id="header-profile-dropdown"
                 class="hidden absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-xl border border-[#E4E7EC] z-50 overflow-hidden divide-y divide-[#F2F4F7]"
                 role="menu"
                 aria-orientation="vertical">
                <div class="px-4 py-3 bg-[#F8FAFC]">
                    <p class="text-xs font-bold text-[#101828] truncate m-0 leading-tight">{{ $cleanName }}</p>
                    <p class="text-[11px] text-[#667085] truncate m-0 mt-0.5">{{ $user?->email }}</p>
                    <span class="mt-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-[#EBF2FE] text-[#062072]">
                        {{ $roleLabel }}
                    </span>
                </div>
                <div class="py-1">
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors text-left cursor-pointer border-0 bg-transparent"
                                role="menuitem">
                            <x-icons.icon name="logout" class="w-4 h-4 text-rose-500" />
                            <span>Keluar dari Akun</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
