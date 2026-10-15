@if($order->details->count())
<div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs p-5 sm:p-6">
    <h3 class="text-sm font-bold text-[#101828] mb-3">Layanan Tambahan</h3>
    <ul class="divide-y divide-[#E4E7EC]">
        @foreach($order->details as $detail)
        <li class="py-2 flex justify-between text-sm">
            <span class="text-[#101828]">{{ $detail->service?->name }}</span>
            <span class="text-[#667085]">Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
        </li>
        @endforeach
    </ul>
</div>
@endif
