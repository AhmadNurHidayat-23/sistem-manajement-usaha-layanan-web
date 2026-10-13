<x-cards.card class="p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-4 mb-4 border-b border-[#E4E7EC]">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">Grafik Tren Omzet 14 Hari Terakhir</h3>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Visualisasi tren pendapatan harian seluruh cabang outlet.</p>
        </div>
        <div class="flex items-center gap-4 text-xs">
            <span class="flex items-center gap-1.5 text-[#667085]">
                <span class="w-3 h-3 rounded-xs bg-[#062072] inline-block"></span> Omzet Penjualan
            </span>
            <span class="text-xs font-bold text-[#101828]">
                Puncak Harian: Rp {{ number_format($omzet_trend['max_omzet'], 0, ',', '.') }}
            </span>
        </div>
    </div>

    <div class="pt-4 overflow-x-auto">
        <div class="min-w-[600px]">
            <div class="h-44 flex items-end gap-2 border-b border-[#E4E7EC] pb-1 px-2">
                @foreach ($omzet_trend['data'] as $point)
                    <div class="flex-1 flex flex-col items-center h-full justify-end group relative">
                        <!-- Tooltip on Hover -->
                        <div class="absolute bottom-full mb-2 hidden group-hover:flex flex-col items-center z-20 pointer-events-none">
                            <div class="bg-[#04184F] text-white text-[10px] rounded-md py-1.5 px-2.5 whitespace-nowrap shadow-lg border border-[#0B3A9E]">
                                <p class="font-bold mb-0.5">{{ $point['label'] }} ({{ $point['day_name'] }})</p>
                                <p class="text-white font-semibold m-0">Rp {{ number_format($point['omzet'], 0, ',', '.') }}</p>
                                <p class="text-[#DCE8FF] text-[9px] m-0">{{ $point['transactions_count'] }} Transaksi</p>
                            </div>
                            <div class="w-2 h-2 bg-[#04184F] rotate-45 -mt-1"></div>
                        </div>

                        <!-- Bar Column -->
                        <div class="w-full max-w-[32px] rounded-t-md transition-all duration-300 {{ $point['omzet'] > 0 ? 'bg-[#062072] hover:bg-[#0B3A9E]' : 'bg-[#E4E7EC]' }}"
                             style="height: {{ max(4, $point['percentage']) }}%;">
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Labels Row -->
            <div class="flex gap-2 pt-2 px-2 text-[10px] text-[#667085] font-medium">
                @foreach ($omzet_trend['data'] as $point)
                    <div class="flex-1 text-center truncate" title="{{ $point['label'] }}">
                        {{ $point['label'] }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-cards.card>
