<!-- Digital Card (2 cols) -->
<div class="lg:col-span-2">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#04184F] via-[#062072] to-[#0B3A9E] p-6 sm:p-8 text-white shadow-xl border border-[#0B3A9E]/40">
        <!-- Decorative Watermark Pattern -->
        <div class="absolute right-0 top-0 bottom-0 opacity-10 flex items-center pr-6 pointer-events-none">
            <svg class="w-64 h-64 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
        </div>

        <!-- Card Header -->
        <div class="flex items-center justify-between relative z-10">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-[#0B3A9E] flex items-center justify-center text-white font-bold text-sm">
                    SP
                </div>
                <div>
                    <span class="text-xs font-bold tracking-widest uppercase block text-white/90">SERVICE MANAGEMENT</span>
                    <span class="text-[9px] tracking-wider text-amber-300 uppercase font-semibold">Priority Member Card</span>
                </div>
            </div>
            <!-- Chip SVG -->
            <div class="w-11 h-8 rounded-md bg-gradient-to-br from-amber-200 via-amber-400 to-amber-500 p-1 border border-amber-600/30 flex items-center justify-center shadow-inner">
                <div class="w-full h-full border border-amber-700/30 rounded flex flex-col justify-between p-0.5">
                    <div class="h-0.5 bg-amber-800/20"></div>
                    <div class="h-0.5 bg-amber-800/20"></div>
                    <div class="h-0.5 bg-amber-800/20"></div>
                </div>
            </div>
        </div>

        <!-- Card Number -->
        <div class="my-6 relative z-10">
            <span class="text-[10px] tracking-widest text-[#DCE8FF] uppercase font-medium">MEMBER ID</span>
            <h3 class="text-2xl sm:text-3xl font-bold tracking-widest text-white mt-1 mb-0 drop-shadow">
                {{ $membership->member_code }}
            </h3>
        </div>

        <!-- Info kartu member -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pt-4 border-t border-white/10 relative z-10">
            <div>
                <span class="text-[9px] uppercase tracking-wider text-[#DCE8FF]">NAMA PEMEGANG</span>
                <h4 class="text-base font-bold text-white uppercase tracking-wider m-0">
                    {{ $membership->user->name }}
                </h4>
                <span class="text-[10px] text-[#DCE8FF]">Telp: {{ $membership->user->phone ?? '-' }}</span>
            </div>

            <div class="bg-black/30 backdrop-blur-md px-4 py-2.5 rounded-xl border border-white/10 text-right">
                <span class="text-[9px] uppercase tracking-wider text-amber-300 font-semibold block">SALDO POIN</span>
                <span class="text-2xl font-extrabold text-amber-400">
                    {{ number_format($membership->loyaltyPoint?->total_points ?? 0) }}
                </span>
                <span class="text-[10px] text-white/80 block font-medium">Points</span>
            </div>
        </div>
    </div>
</div>
