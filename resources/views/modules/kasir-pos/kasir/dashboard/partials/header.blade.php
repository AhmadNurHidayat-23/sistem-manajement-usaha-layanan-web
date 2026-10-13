<!-- Top Action Bar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h3 class="text-xl font-bold text-[#101828] m-0 flex items-center gap-2">
            <svg class="w-6 h-6 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Dashboard Kasir
        </h3>
        <p class="text-xs text-[#667085] mt-1 m-0">Ringkasan operasional shift kasir, transaksi harian, dan antrian pembayaran.</p>
    </div>

    @if ($accessibleBranches->count() > 1 || $activeBranch)
        <div class="flex items-center gap-2">
            @if ($accessibleBranches->count() > 1)
                <form method="GET" action="{{ route('cashier.dashboard') }}" class="flex items-center gap-2">
                    <label for="dash_branch_id" class="text-xs font-medium text-[#667085] whitespace-nowrap">Cabang:</label>
                    <select id="dash_branch_id" name="branch_id" class="branch-switch-select px-3 py-1.5 bg-white border border-[#E4E7EC] rounded-lg text-xs font-semibold text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        @foreach ($accessibleBranches as $b)
                            <option value="{{ $b->id }}" {{ $activeBranch?->id === $b->id ? 'selected' : '' }}>{{ $b->display_name }}</option>
                        @endforeach
                    </select>
                </form>
            @elseif ($activeBranch)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-[#E4E7EC] text-xs font-semibold text-[#101828]">
                    <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    {{ $activeBranch->display_name }}
                </span>
            @endif
        </div>
    @endif
</div>
