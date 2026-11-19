@php
    $authUser = auth()->user();
    $unreadCount = $authUser ? \Illuminate\Support\Facades\Cache::remember(
        "user_notif_unread_{$authUser->id}",
        120,
        fn () => $authUser->unreadNotifications()->count()
    ) : 0;
    $isOwnerManager = in_array($authUser?->role?->name, ['owner', 'manager'], true);
@endphp

<div class="relative shrink-0 flex items-center justify-center" id="notification-bell-wrapper">
    {{-- Tombol Lonceng Header (Pill Rounded-Full 36x36px, Ikon 20px) --}}
    <button type="button"
            id="notification-bell-btn"
            class="relative w-9 h-9 rounded-full shrink-0 flex items-center justify-center text-[#062072] dark:text-[#93C5FD] hover:bg-[#EBF2FE] dark:hover:bg-[#1E2D4A] border border-transparent hover:border-[#E4E7EC] dark:hover:border-[#1E2D4A] transition-colors cursor-pointer"
            title="{{ __('dashboard.topbar.notifications') }}"
            aria-label="{{ __('dashboard.topbar.notifications') }}"
            aria-expanded="false"
            aria-controls="notification-bell-dropdown">
        <x-icons.icon name="bell" class="w-5 h-5 text-[#062072] dark:text-[#93C5FD]" />

        {{-- Badge Jumlah Belum Dibaca --}}
        <span id="notification-bell-badge"
              class="{{ $unreadCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 min-w-[17px] h-[17px] px-1 bg-rose-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-xs">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    </button>

    {{-- Panel Dropdown Notifikasi --}}
    <div id="notification-bell-dropdown"
         class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-[#111C35] rounded-xl shadow-xl border border-[#E4E7EC] dark:border-[#1E2D4A] z-50 overflow-hidden"
         role="region"
         aria-label="{{ __('dashboard.topbar.notifications') }}">
        
        {{-- Header Dropdown --}}
        <div class="px-4 py-3 bg-[#F8FAFC] dark:bg-[#0E172B] border-b border-[#E4E7EC] dark:border-[#1E2D4A] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#101828] dark:text-white uppercase tracking-wider">{{ __('dashboard.topbar.notifications') }}</span>
                <span id="notification-header-count"
                      class="{{ $unreadCount > 0 ? '' : 'hidden' }} px-1.5 py-0.5 text-[10px] font-semibold bg-[#EBF2FE] dark:bg-[#062072]/30 text-[#062072] dark:text-[#93C5FD] rounded-full">
                    {{ $unreadCount }} {{ __('public.notifications.new') }}
                </span>
            </div>
            <button type="button"
                    id="btn-mark-all-notifications"
                    class="text-[11px] font-semibold text-[#062072] dark:text-[#93C5FD] hover:text-[#04184F] hover:underline cursor-pointer transition-colors bg-transparent border-0 p-0">
                {{ __('public.notifications.mark_all_read') }}
            </button>
        </div>

        {{-- Daftar Notifikasi (Scrollable) --}}
        <div id="notification-feed-list" class="max-h-96 overflow-y-auto divide-y divide-[#F2F4F7] dark:divide-[#1E2D4A]">
            {{-- Template item akan dirender dinamis via JavaScript/Polling --}}
        </div>

        {{-- Empty State --}}
        <div id="notification-feed-empty" class="{{ $unreadCount > 0 ? 'hidden' : '' }} py-8 px-4 text-center">
            <div class="w-10 h-10 mx-auto rounded-full bg-[#F2F4F7] dark:bg-[#0E172B] flex items-center justify-center text-[#98A2B3] mb-2.5">
                <x-icons.icon name="bell" class="w-5 h-5" />
            </div>
            <p class="text-xs font-semibold text-[#101828] dark:text-white m-0">{{ __('public.notifications.empty_title') }}</p>
            <p class="text-[11px] text-[#667085] dark:text-[#94A3B8] mt-0.5 m-0">{{ __('public.notifications.empty_desc') }}</p>
        </div>

        {{-- Footer --}}
        @if ($isOwnerManager && Route::has('notifikasi.index'))
            <div class="px-4 py-2 bg-[#F8FAFC] dark:bg-[#0E172B] border-t border-[#E4E7EC] dark:border-[#1E2D4A] text-center">
                <a href="{{ route('notifikasi.index') }}"
                   class="text-[11px] font-semibold text-[#062072] dark:text-[#93C5FD] hover:underline">
                    {{ __('public.notifications.view_whatsapp_log') }} &rarr;
                </a>
            </div>
        @endif
    </div>
</div>
