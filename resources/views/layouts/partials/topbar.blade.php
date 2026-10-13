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
    $hasNotifikasiAccess = in_array($roleName, ['owner', 'manager']) && Route::has('notifikasi.index');
    $isInternalStaff = in_array($roleName, ['owner', 'manager', 'cashier', 'employee'], true);
@endphp

<!-- Header / Topbar: Tinggi sama persis dengan sidebar header (h-[72px]) -->
<header class="h-[72px] bg-white border-b border-[#E4E7EC] px-4 sm:px-6 flex items-center justify-between {{ $isInternalStaff ? '' : 'lg:justify-end' }} shrink-0 z-20">
    @if ($isInternalStaff)
        <!-- Sisi Kiri Karyawan: Mobile Toggle + Jam & Tanggal Real-time -->
        <div class="flex items-center gap-3">
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
                <span id="realtime-clock-time" class="text-[14px] leading-tight font-medium text-[#101828]"></span>
                <span id="realtime-clock-date" class="text-[14px] leading-tight font-medium text-[#101828] mt-[2px]"></span>
            </div>
        </div>
    @else
        <!-- Tombol Hamburger Mobile/Tablet untuk Non-Karyawan (HANYA lg:hidden) -->
        <button type="button"
                id="mobile-sidebar-toggle-btn"
                class="lg:hidden w-9 h-9 rounded-lg flex items-center justify-center text-[#101828] hover:bg-[#F5F8FC] border border-[#E4E7EC] transition-colors cursor-pointer"
                title="Buka Navigasi"
                aria-label="Buka Menu Navigasi">
            <svg class="w-5 h-5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    @endif

    <!-- Sisi Kanan: Badge Role + Icon Notifikasi + (Ikon Profil / Nama User) -->
    <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
        <x-badges.badge variant="primary">
            {{ strtoupper($roleLabel) }}
        </x-badges.badge>
        @include('layouts.partials.notification-bell')

        @if ($isInternalStaff)
            <!-- Ikon / Foto Profil Internal Staff -->
            <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center text-[#062072] select-none border border-[#E4E7EC]"
                 title="{{ $cleanName }}"
                 aria-label="Profil {{ $roleLabel }}">
                @if ($user?->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $cleanName }}" class="w-full h-full object-cover">
                @else
                    <x-icons.icon name="user" class="w-[18px] h-[18px] text-[#062072]" />
                @endif
            </div>
        @else
            <div class="h-5 w-px bg-[#E4E7EC] hidden sm:block"></div>

            <span class="text-xs font-semibold text-[#101828] hidden sm:inline max-w-[150px] truncate">
                {{ $cleanName }}
            </span>
        @endif
    </div>
</header>
