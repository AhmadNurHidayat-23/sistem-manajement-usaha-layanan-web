<!-- Item Details -->
<div class="py-3.5 border-b border-dashed border-[#CBD5E1] space-y-2.5">
    <div class="flex justify-between text-[11px] font-bold text-[#667085] uppercase tracking-wider pb-0.5">
        <span>Layanan / Item</span>
        <span class="text-right">Subtotal</span>
    </div>

    @foreach ($transaction->details as $item)
        <div class="text-xs">
            <div class="flex justify-between items-start gap-2">
                <span class="font-medium text-[#101828] leading-snug">{{ $item->service?->name ?? 'Layanan' }}</span>
                <span class="font-semibold text-[#101828] text-right shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="text-[11px] text-[#667085] mt-0.5">
                {{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}
            </div>
        </div>
    @endforeach
</div>
