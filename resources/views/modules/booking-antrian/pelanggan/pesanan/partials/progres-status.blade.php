<div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5">
    <h3 class="text-sm font-bold text-[#101828] mb-4">Status Pesanan</h3>

    @php
        $steps = ['pending', 'received', 'processing', 'ready', 'completed'];
        $currentIdx = array_search($order->status->value, $steps);
        $isCancelled = $order->status->value === 'cancelled';
    @endphp

    @if ($isCancelled)
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-lg text-center">
            <svg class="w-8 h-8 mx-auto mb-2 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <p class="text-sm font-semibold text-rose-700 m-0">Pesanan Dibatalkan</p>
        </div>
    @else
        <div class="space-y-0">
            @foreach($steps as $idx => $step)
                @php
                    $isPassed = $currentIdx !== false && $idx <= $currentIdx;
                    $isCurrent = $currentIdx !== false && $idx === $currentIdx;
                    $stepLabels = [
                        'pending' => 'Menunggu',
                        'received' => 'Diterima',
                        'processing' => 'Dikerjakan',
                        'ready' => 'Siap Diambil',
                        'completed' => 'Selesai',
                    ];
                @endphp
                <div class="flex items-start gap-3 {{ !$loop->last ? 'pb-5' : '' }} relative">
                    <!-- Connector Line -->
                    @if (!$loop->last)
                        <div class="absolute left-[11px] top-[24px] w-0.5 h-[calc(100%-12px)]
                            {{ $isPassed && $currentIdx > $idx ? 'bg-emerald-400' : 'bg-[#E4E7EC]' }}"></div>
                    @endif

                    <!-- Step Circle -->
                    <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 z-10
                        {{ $isPassed ? ($isCurrent ? 'bg-[#062072] ring-4 ring-[#DCE8FF]' : 'bg-emerald-500') : 'bg-[#E4E7EC]' }}">
                        @if ($isPassed && !$isCurrent)
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        @elseif ($isCurrent)
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                        @endif
                    </div>

                    <!-- Step Label -->
                    <div>
                        <p class="text-xs font-semibold m-0 {{ $isCurrent ? 'text-[#062072]' : ($isPassed ? 'text-emerald-700' : 'text-[#667085]') }}">
                            {{ $stepLabels[$step] }}
                        </p>
                        @if ($isCurrent)
                            <p class="text-[10px] text-[#667085] mt-0.5 m-0">Status saat ini</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
