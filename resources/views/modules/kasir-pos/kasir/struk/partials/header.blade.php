<!-- Branch / Company Header -->
<div class="text-center pb-4 border-b border-dashed border-[#CBD5E1]">
    <h2 class="text-base sm:text-lg font-black tracking-wider uppercase m-0 text-[#062072]">
        {{ $transaction->branch->display_name ?? 'SIMAUL' }}
    </h2>
    <p class="text-[11px] text-[#667085] mt-1 m-0 leading-relaxed">
        {{ $transaction->branch->address ?? 'Pusat Layanan Terpadu' }}
    </p>
    @if ($transaction->branch->phone)
        <p class="text-[11px] text-[#667085] m-0 mt-0.5">
            Telp/WA: {{ $transaction->branch->phone }}
        </p>
    @endif
</div>

<!-- Receipt Meta -->
<div class="py-3.5 border-b border-dashed border-[#CBD5E1] space-y-1.5 text-[11px]">
    <div class="flex justify-between items-start gap-2">
        <span class="text-[#667085] shrink-0">No. Struk</span>
        <span class="font-bold text-[#101828] text-right break-words">{{ $transaction->transaction_code }}</span>
    </div>
    <div class="flex justify-between items-start gap-2">
        <span class="text-[#667085] shrink-0">Waktu</span>
        <span class="text-[#101828] text-right">{{ $transaction->transaction_date ? $transaction->transaction_date->format('d/m/Y H:i') : '-' }}</span>
    </div>
    <div class="flex justify-between items-start gap-2">
        <span class="text-[#667085] shrink-0">Kasir</span>
        <span class="text-[#101828] text-right font-medium">{{ $transaction->cashier->name ?? 'Kasir' }}</span>
    </div>
    <div class="flex justify-between items-start gap-2">
        <span class="text-[#667085] shrink-0">Pelanggan</span>
        <span class="font-bold text-[#101828] text-right break-words">{{ $transaction->customer_name ?: 'Pelanggan Umum' }}</span>
    </div>
    @if ($transaction->customer_phone)
        <div class="flex justify-between items-start gap-2">
            <span class="text-[#667085] shrink-0">Telepon</span>
            <span class="text-[#101828] text-right">{{ $transaction->customer_phone }}</span>
        </div>
    @endif
    @if ($transaction->booking_id)
        <div class="flex justify-between items-start gap-2">
            <span class="text-[#667085] shrink-0">Ref Booking</span>
            <span class="font-medium text-[#101828] text-right">#{{ $transaction->booking_id }}</span>
        </div>
    @elseif ($transaction->order_id)
        <div class="flex justify-between items-start gap-2">
            <span class="text-[#667085] shrink-0">Ref Order</span>
            <span class="font-medium text-[#101828] text-right">#{{ $transaction->order_id }}</span>
        </div>
    @endif
</div>
