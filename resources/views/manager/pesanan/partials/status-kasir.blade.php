{{-- Status Perkembangan Pesanan Khusus Kasir (Read-only Stepper mirip portal Pelanggan) --}}
@php
    $steps = ['pending', 'received', 'processing', 'ready', 'completed'];
    $currentIdx = array_search($order->status->value, $steps);
    $isCancelled = $order->status->value === 'cancelled';
    $stepLabels = [
        'pending' => 'Menunggu',
        'received' => 'Diterima',
        'processing' => 'Dikerjakan',
        'ready' => 'Siap',
        'completed' => 'Selesai',
    ];
@endphp
<x-cards.card title="Status Pesanan">
    @if($isCancelled)
        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-lg text-center">
            <span class="text-xs font-bold text-rose-700">Pesanan Dibatalkan</span>
        </div>
    @else
        <div class="space-y-0 pb-1">
            @foreach($steps as $idx => $step)
                @php
                    $isPassed = $currentIdx !== false && $idx <= $currentIdx;
                    $isCurrent = $currentIdx !== false && $idx === $currentIdx;
                @endphp
                <div class="flex items-start gap-3 {{ !$loop->last ? 'pb-4' : '' }} relative">
                    @if (!$loop->last)
                        <div class="absolute left-[11px] top-[22px] w-0.5 h-[calc(100%-10px)] {{ $isPassed && $currentIdx > $idx ? 'bg-emerald-400' : 'bg-[#E4E7EC]' }}"></div>
                    @endif
                    <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 z-10 {{ $isPassed ? ($isCurrent ? 'bg-[#062072] ring-4 ring-[#DCE8FF]' : 'bg-emerald-500 text-white') : 'bg-[#E4E7EC]' }}">
                        @if ($isPassed && !$isCurrent)
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @elseif ($isCurrent)
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                        @endif
                    </div>
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
        <p class="text-[11px] text-[#667085] mt-2 pt-3 border-t border-[#E4E7EC] m-0">
            Status diperbarui melalui tindakan operasional Kasir dan Karyawan.
        </p>
    @endif
</x-cards.card>

<x-cards.card title="Pembayaran & Penyerahan">
    <dl class="divide-y divide-[#E4E7EC] text-xs">
        <div class="py-2.5 flex justify-between items-center gap-2">
            <dt class="text-[#667085]">Status Pembayaran</dt>
            <dd class="m-0">
                @if($order->isPaid())
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Lunas</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Belum Lunas</span>
                @endif
            </dd>
        </div>
        <div class="py-2.5 flex justify-between items-center gap-2">
            <dt class="text-[#667085]">Total Tagihan</dt>
            <dd class="font-bold text-[#062072] m-0">Rp {{ number_format($order->calculateBill(), 0, ',', '.') }}</dd>
        </div>
        <div class="py-2.5 flex justify-between items-center gap-2">
            <dt class="text-[#667085]">Metode Penyerahan</dt>
            <dd class="font-medium text-[#101828] m-0">
                {{ $order->delivery_method === 'delivery' ? 'Diantar ke Alamat' : 'Ambil di Toko' }}
            </dd>
        </div>
        <div class="py-2.5 flex justify-between items-center gap-2">
            <dt class="text-[#667085]">Status Penyerahan</dt>
            <dd class="m-0">
                @if($order->handover_status === 'handed_over')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Diserahkan</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">Belum Diserahkan</span>
                @endif
            </dd>
        </div>
    </dl>
</x-cards.card>
