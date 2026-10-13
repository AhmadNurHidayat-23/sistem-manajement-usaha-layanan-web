<!-- Digital Member Card & Status -->
<div class="space-y-4">
    <!-- Virtual Card -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#04184F] via-[#062072] to-[#0B3A9E] p-6 text-white shadow-xl border border-[#0B3A9E]/40">
        <!-- Background ambient patterns -->
        <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/5 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute -left-8 -bottom-8 w-40 h-40 bg-[#DCE8FF]/10 rounded-full blur-xl pointer-events-none"></div>

        <!-- Card Header -->
        <div class="relative flex items-center justify-between mb-8">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white border border-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold tracking-wider uppercase text-white/90 m-0">Service POS</p>
                    <span class="text-[9px] text-[#DCE8FF]/70 tracking-widest uppercase">Loyalty Club</span>
                </div>
            </div>

            <!-- Tier Badge -->
            @php
                $tier = strtolower($membership->tier?->value ?? 'silver');
                $tierColor = match($tier) {
                    'platinum' => 'bg-[#DCE8FF]/20 text-[#DCE8FF] border-[#DCE8FF]/40',
                    'gold' => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
                    default => 'bg-slate-500/20 text-slate-200 border-slate-400/40'
                };
            @endphp
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border backdrop-blur-md {{ $tierColor }}">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
                {{ $membership->tier?->value ?? 'Silver' }}
            </span>
        </div>

        <!-- Member Code -->
        <div class="relative mb-6">
            <span class="text-[10px] uppercase font-semibold text-[#DCE8FF]/70 tracking-wider">Nomor Kartu Member</span>
            <div class="text-xl tabular-nums tracking-widest font-bold mt-1 text-white">
                {{ $membership->member_code }}
            </div>
        </div>

        <!-- Card Footer: Name & Expiry / Joined -->
        <div class="relative flex items-end justify-between border-t border-white/10 pt-4">
            <div>
                <span class="text-[10px] uppercase text-[#DCE8FF]/70 block font-medium">Nama Pemegang</span>
                <span class="text-sm font-semibold text-white tracking-wide block truncate max-w-[170px]">{{ $membership->user->name }}</span>
            </div>
            <div class="text-right">
                <span class="text-[10px] uppercase text-[#DCE8FF]/70 block font-medium">Terdaftar Sejak</span>
                <span class="text-xs tabular-nums font-medium text-white">{{ $membership->created_at->format('m/y') }}</span>
            </div>
        </div>
    </div>

    <!-- Status Card -->
    <x-cards.card class="p-4">
        <div class="flex items-center justify-between text-xs">
            <span class="text-[#667085]">Status Keanggotaan</span>
            @if ($membership->status->value === 'active')
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
            @else
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">{{ ucfirst($membership->status->value) }}</span>
            @endif
        </div>
        <div class="flex items-center justify-between text-xs mt-3 pt-3 border-t border-[#E4E7EC]">
            <span class="text-[#667085]">Email Terdaftar</span>
            <span class="font-medium text-[#101828]">{{ $membership->user->email }}</span>
        </div>
        <div class="flex items-center justify-between text-xs mt-2">
            <span class="text-[#667085]">Nomor Telepon</span>
            <span class="font-medium text-[#101828]">{{ $membership->user->phone ?? '-' }}</span>
        </div>
    </x-cards.card>
</div>
