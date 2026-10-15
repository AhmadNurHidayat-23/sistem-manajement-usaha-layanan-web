<!-- Jadwal Shift Mendatang -->
<x-cards.card title="Jadwal Shift Mendatang" subtitle="Agenda shift kerja Anda minggu ini">
    @if ($upcomingShifts->isEmpty())
        <div class="py-8 text-center">
            <p class="text-xs text-[#667085] m-0">Belum ada jadwal shift mendatang yang direncanakan.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($upcomingShifts as $sh)
                <div class="flex items-center justify-between p-3 rounded-lg border border-[#E4E7EC] bg-[#F5F8FC]/50 hover:bg-[#F5F8FC] transition-colors">
                    <div>
                        <span class="text-xs font-bold text-[#101828] block">
                            {{ \Carbon\Carbon::parse($sh->shift_date)->translatedFormat('l, d F Y') }}
                        </span>
                        <span class="text-[11px] text-[#667085]">
                            {{ $sh->branch->display_name }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold text-[#062072] block">
                            {{ \Carbon\Carbon::parse($sh->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sh->end_time)->format('H:i') }}
                        </span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700">
                            {{ ucfirst($sh->status->value) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-cards.card>
