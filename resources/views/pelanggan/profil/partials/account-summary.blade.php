{{-- Kolom Kiri: Kartu Ringkasan Akun --}}
<div class="lg:col-span-1 space-y-6">
    <div class="bg-white border border-[#E4E7EC] rounded-2xl p-6 text-center shadow-sm">
        {{-- Foto Profil / Avatar Inisial --}}
        <div class="flex justify-center mb-4">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}"
                     alt="{{ $cleanUserName }}"
                     class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border-2 border-[#E4E7EC] shadow-sm">
            @else
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-[#062072] text-white flex items-center justify-center font-bold text-2xl sm:text-3xl shadow-sm border-2 border-white">
                    {{ strtoupper(substr($cleanUserName, 0, 2)) }}
                </div>
            @endif
        </div>

        <h3 class="text-base sm:text-lg font-bold text-[#101828] m-0">{{ $cleanUserName }}</h3>
        <p class="text-xs text-[#667085] m-0 mt-0.5">{{ $user->email }}</p>

        @if($user->phone)
            <div class="mt-2 inline-flex items-center gap-1.5 text-xs text-[#344054] bg-[#F8F9FC] px-2.5 py-1 rounded-lg border border-[#E4E7EC]">
                <svg class="w-3.5 h-3.5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <span>{{ $user->phone }}</span>
            </div>
        @else
            <span class="mt-2 inline-block text-[11px] text-[#98A2B3] italic">Nomor telepon belum diisi</span>
        @endif

        <div class="mt-6 pt-5 border-t border-[#E4E7EC] text-left space-y-3 text-xs">
            <div class="flex items-center justify-between">
                <span class="text-[#667085]">Status Keanggotaan</span>
                @if($user->membership)
                    <a href="{{ route('customer.member') }}" class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 no-underline hover:underline">
                        Member Aktif
                    </a>
                @else
                    <a href="{{ route('customer.member') }}" class="font-medium text-[#062072] bg-[#EBF2FE] px-2 py-0.5 rounded border border-[#062072]/20 no-underline hover:underline">
                        Daftar Member &rarr;
                    </a>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[#667085]">Total Janji Temu</span>
                <span class="font-bold text-[#101828]">{{ $totalBookings ?? 0 }} reservasi</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[#667085]">Total Pesanan Drop-off</span>
                <span class="font-bold text-[#101828]">{{ $totalOrders ?? 0 }} pesanan</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[#667085]">Bergabung Sejak</span>
                <span class="font-medium text-[#101828]">{{ \Carbon\Carbon::parse($user->created_at)->translatedFormat('d F Y') }}</span>
            </div>
        </div>
    </div>
</div>
