<x-cards.card class="overflow-hidden h-full flex flex-col">
    <div class="p-4 border-b border-[#E7ECF3] flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">Jadwal Staf Hari Ini</h3>
            <p class="text-xs text-[#6B7280] mt-0.5 m-0">Staf bertugas dan jam kerja shift.</p>
        </div>
        <x-badges.badge variant="neutral" class="text-[10px]">
            {{ count($today_shifts) }} Shift
        </x-badges.badge>
    </div>

    <div class="divide-y divide-[#E7ECF3] flex-1 overflow-y-auto max-h-[380px]">
        @forelse ($today_shifts as $shift)
            @php
                $empName = $shift->employee?->name ?? 'Karyawan';
                $initials = strtoupper(substr($empName, 0, 2));
                $startFormatted = $shift->start_time ? \Carbon\Carbon::parse($shift->start_time)->format('H.i') : '-';
                $endFormatted = $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('H.i') : '-';
            @endphp
            <div class="p-3.5 flex items-center justify-between hover:bg-[#F8F9FC]/60 transition-colors">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-[#062072] text-[#DCE8FF] flex items-center justify-center text-xs font-bold shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs font-semibold text-[#101828] truncate m-0">
                            {{ $empName }}
                        </h4>
                        <span class="text-[11px] tabular-nums text-[#6B7280] block mt-0.5">
                            {{ $startFormatted }} – {{ $endFormatted }}
                        </span>
                    </div>
                </div>
                <div class="shrink-0 text-right ml-2">
                    <x-badges.status :status="$shift->status" />
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-xs text-[#6B7280]">
                Belum ada jadwal staf hari ini.
            </div>
        @endforelse
    </div>
</x-cards.card>
