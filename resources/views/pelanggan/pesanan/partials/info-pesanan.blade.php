<div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5 sm:p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-[#101828] m-0">Informasi Pesanan</h3>
        <span class="text-xs text-[#667085]">{{ $order->created_at->format('d M Y, H:i') }}</span>
    </div>
    <dl class="divide-y divide-[#E4E7EC]">
        @php
            $isWeight = $order->service?->isWeightBased();
            $qtyDisplay = '-';
            if ($isWeight) {
                $qtyDisplay = $order->estimated_quantity ? number_format($order->estimated_quantity, 2) . ' kg (perkiraan)' : 'Menunggu penimbangan petugas';
            } elseif ($order->estimated_quantity) {
                $unit = $order->service?->unit ?? 'sesi';
                $qtyDisplay = number_format($order->estimated_quantity, 0) . ' ' . $unit;
            }

            $rows = [
                ['Kode Order', '#' . $order->id],
                ['Cabang', $order->branch?->display_name ?? $order->branch?->name ?? '-'],
                ['Layanan', $order->service?->name ?? '-'],
                ['Kategori', $order->service?->category?->name ?? '-'],
                ['Kuantitas / Berat', $qtyDisplay],
                ['Antar-Jemput', $order->pickup_option ? 'Ya (Dijemput)' : 'Tidak (Drop-off di cabang)'],
                ['Est. Selesai', $order->estimated_finish_at ? $order->estimated_finish_at->format('d M Y, H:i') . ' WIB' : '-'],
                ['Catatan', $order->notes ?? '-'],
            ];
        @endphp
        @foreach($rows as [$label, $value])
        <div class="py-3 flex justify-between gap-4">
            <dt class="text-sm text-[#667085] shrink-0">{{ $label }}</dt>
            <dd class="text-sm font-medium text-[#101828] text-right">{{ $value }}</dd>
        </div>
        @endforeach
    </dl>
</div>
