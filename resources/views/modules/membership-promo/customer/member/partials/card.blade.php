<div class="lg:col-span-1">
    <div class="bg-gradient-to-br from-[#062072] via-[#0B3A9E] to-[#04184F] rounded-xl text-white p-5 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <span class="text-[10px] uppercase tracking-widest text-[#DCE8FF] font-semibold">Member Card</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-white/20 text-white">
                {{ $membership->status->value === 'active' ? 'AKTIF' : strtoupper($membership->status->value) }}
            </span>
        </div>
        <p class="text-xs text-[#DCE8FF] mb-0.5">Nomor Member</p>
        <p class="text-lg font-bold tabular-nums tracking-wider text-white mb-3">{{ $membership->member_code }}</p>
        <p class="text-xs text-[#DCE8FF] mb-0.5">Nama</p>
        <p class="text-sm font-semibold text-white mb-3">{{ $membership->user?->name }}</p>
        <div class="flex items-center justify-between pt-3 border-t border-white/20">
            <div>
                <p class="text-[10px] text-[#DCE8FF] m-0">Bergabung Sejak</p>
                <p class="text-xs font-medium text-white m-0">{{ $membership->joined_at?->format('d M Y') }}</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] text-[#DCE8FF] m-0">Total Poin</p>
                <p class="text-lg font-bold text-white m-0">{{ number_format($membership->loyaltyPoint?->total_points ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Tier Info -->
    @php
        $totalPoints = $membership->loyaltyPoint?->total_points ?? 0;
        $currentTier = collect($packages)->filter(fn($p) => $totalPoints >= $p['min_points'])->last();
    @endphp
    @if ($currentTier)
    <div class="mt-4 bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-4">
        <div class="flex items-center gap-2 mb-2">
            <span class="text-xs font-bold text-[#101828]">Tier Saat Ini:</span>
            <span class="text-xs font-bold text-[#062072]">{{ $currentTier['level'] }}</span>
        </div>
        <p class="text-xs text-[#667085] m-0">{{ $currentTier['description'] }}</p>
    </div>
    @endif
</div>
