<x-cards.card class="p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 mb-4 border-b border-[#E4E7EC]">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">Grafik Tren Omzet 14 Hari Terakhir</h3>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Visualisasi tren pendapatan harian seluruh cabang outlet.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs">
            <span class="flex items-center gap-1.5 text-[#667085]">
                <span class="w-3 h-3 rounded-xs bg-[#062072] inline-block"></span> Omzet Penjualan
            </span>
            <span class="text-xs font-semibold text-[#101828] bg-[#F5F8FC] px-2.5 py-1 rounded-lg border border-[#E4E7EC]">
                Puncak Harian: <strong class="text-[#062072]">Rp {{ number_format($omzet_trend['max_omzet'], 0, ',', '.') }}</strong>
            </span>
        </div>
    </div>

    <!-- Scroll hint untuk layar kecil -->
    <div class="sm:hidden flex items-center justify-between text-[11px] text-[#667085] pb-2 px-1">
        <span>&larr; Geser grafik ke samping &rarr;</span>
        <span class="font-semibold text-[#062072]">14 Hari Terakhir</span>
    </div>

    <div class="pt-2 overflow-x-auto">
        <div class="min-w-[620px]">
            <div class="flex items-stretch gap-2 h-48">
                <!-- Kolom Sumbu Y (Nilai Skala Nominal) -->
                <div class="w-20 shrink-0 flex flex-col justify-between text-right pr-2 text-[10px] font-semibold text-[#667085] select-none py-1">
                    <span class="truncate" title="Rp {{ number_format($omzet_trend['max_omzet'], 0, ',', '.') }}">
                        Rp {{ number_format($omzet_trend['max_omzet'], 0, ',', '.') }}
                    </span>
                    <span class="truncate" title="Rp {{ number_format($omzet_trend['max_omzet'] / 2, 0, ',', '.') }}">
                        Rp {{ number_format(round($omzet_trend['max_omzet'] / 2), 0, ',', '.') }}
                    </span>
                    <span>Rp 0</span>
                </div>

                <!-- Area Grafik Batang dengan Garis Grid Horizontal -->
                <div class="flex-1 relative flex items-end gap-2 border-b border-[#E4E7EC] pb-1 px-2">
                    <!-- Garis Grid 100% -->
                    <div class="absolute inset-x-0 top-1 border-t border-dashed border-[#E4E7EC] pointer-events-none"></div>
                    <!-- Garis Grid 50% -->
                    <div class="absolute inset-x-0 top-1/2 border-t border-dashed border-[#E4E7EC] pointer-events-none"></div>

                    @foreach ($omzet_trend['data'] as $point)
                        <div class="flex-1 flex flex-col items-center h-full justify-end group relative cursor-pointer focus:outline-none"
                             tabindex="0"
                             role="button"
                             aria-label="{{ $point['label'] }}: Rp {{ number_format($point['omzet'], 0, ',', '.') }} ({{ $point['transactions_count'] }} Transaksi)">
                            
                            <!-- Tooltip on Hover / Focus / Active -->
                            <div class="absolute bottom-full mb-2 hidden group-hover:flex group-focus:flex group-active:flex flex-col items-center z-30 pointer-events-none transition-all duration-150">
                                <div class="bg-[#04184F] text-white text-[11px] rounded-lg py-2 px-3 whitespace-nowrap shadow-xl border border-[#0B3A9E]">
                                    <p class="font-bold text-white mb-0.5">{{ $point['label'] }} ({{ $point['day_name'] }})</p>
                                    <p class="text-emerald-400 font-bold m-0">Rp {{ number_format($point['omzet'], 0, ',', '.') }}</p>
                                    <p class="text-[#DCE8FF] text-[10px] mt-0.5 m-0 font-medium">{{ number_format($point['transactions_count']) }} Transaksi</p>
                                </div>
                                <div class="w-2 h-2 bg-[#04184F] rotate-45 -mt-1 border-r border-b border-[#0B3A9E]"></div>
                            </div>

                            <!-- Bar Column -->
                            <div class="w-full max-w-[32px] rounded-t-md transition-all duration-200 {{ $point['omzet'] > 0 ? 'bg-[#062072] group-hover:bg-[#0B3A9E] group-focus:bg-[#0B3A9E]' : 'bg-[#E4E7EC]' }}"
                                 style="height: {{ max(4, $point['percentage']) }}%;">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Labels Row (Sumbu X Tanggal) -->
            <div class="flex gap-2 pt-2.5 pl-22 pr-2 text-[10px] text-[#667085] font-medium">
                @foreach ($omzet_trend['data'] as $point)
                    <div class="flex-1 text-center truncate" title="{{ $point['label'] }}">
                        {{ $point['label'] }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-cards.card>
