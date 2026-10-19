@php
    $authUser = auth()->user();
    $unreadCount = $authUser ? \Illuminate\Support\Facades\Cache::remember(
        "user_notif_unread_{$authUser->id}",
        20,
        fn () => $authUser->unreadNotifications()->count()
    ) : 0;
    $isOwnerManager = in_array($authUser?->role?->name, ['owner', 'manager'], true);
@endphp

<div class="relative" id="notification-bell-wrapper">
    {{-- Tombol Lonceng Header (Pill Rounded-Full) --}}
    <button type="button"
            id="notification-bell-btn"
            class="relative w-9 h-9 rounded-full flex items-center justify-center text-[#062072] hover:bg-[#EBF2FE] border border-transparent hover:border-[#E4E7EC] transition-colors cursor-pointer"
            title="Notifikasi"
            aria-label="Lihat Notifikasi"
            aria-expanded="false"
            aria-controls="notification-bell-dropdown">
        <x-icons.icon name="bell" class="w-[18px] h-[18px] text-[#062072]" />

        {{-- Badge Jumlah Belum Dibaca --}}
        <span id="notification-bell-badge"
              class="{{ $unreadCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-rose-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-xs">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    </button>

    {{-- Panel Dropdown Notifikasi --}}
    <div id="notification-bell-dropdown"
         class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-[#E4E7EC] z-50 overflow-hidden"
         role="region"
         aria-label="Daftar Notifikasi">
        
        {{-- Header Dropdown --}}
        <div class="px-4 py-3 bg-[#F8FAFC] border-b border-[#E4E7EC] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#101828] uppercase tracking-wider">Notifikasi</span>
                <span id="notification-header-count"
                      class="{{ $unreadCount > 0 ? '' : 'hidden' }} px-1.5 py-0.5 text-[10px] font-semibold bg-[#EBF2FE] text-[#062072] rounded-full">
                    {{ $unreadCount }} Baru
                </span>
            </div>
            <button type="button"
                    id="btn-mark-all-notifications"
                    class="text-[11px] font-semibold text-[#062072] hover:text-[#04184F] hover:underline cursor-pointer transition-colors bg-transparent border-0 p-0">
                Tandai semua dibaca
            </button>
        </div>

        {{-- Daftar Notifikasi (Scrollable) --}}
        <div id="notification-feed-list" class="max-h-96 overflow-y-auto divide-y divide-[#F2F4F7]">
            {{-- Template item akan dirender dinamis via JavaScript/Polling --}}
        </div>

        {{-- Empty State --}}
        <div id="notification-feed-empty" class="{{ $unreadCount > 0 ? 'hidden' : '' }} py-8 px-4 text-center">
            <div class="w-10 h-10 mx-auto rounded-full bg-[#F2F4F7] flex items-center justify-center text-[#98A2B3] mb-2.5">
                <x-icons.icon name="bell" class="w-5 h-5" />
            </div>
            <p class="text-xs font-semibold text-[#101828] m-0">Tidak ada notifikasi baru</p>
            <p class="text-[11px] text-[#667085] mt-0.5 m-0">Pemberitahuan tugas dan transaksi akan muncul di sini.</p>
        </div>

        {{-- Footer --}}
        @if ($isOwnerManager && Route::has('notifikasi.index'))
            <div class="px-4 py-2 bg-[#F8FAFC] border-t border-[#E4E7EC] text-center">
                <a href="{{ route('notifikasi.index') }}"
                   class="text-[11px] font-semibold text-[#062072] hover:underline">
                    Lihat Log Notifikasi WhatsApp &rarr;
                </a>
            </div>
        @endif
    </div>
</div>
