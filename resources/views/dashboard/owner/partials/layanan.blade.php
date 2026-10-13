<x-cards.card class="overflow-hidden h-full flex flex-col">
    <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">Layanan Terlaris</h3>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Top 5 servis dengan penjualan tertinggi.</p>
        </div>
    </div>

    <div class="divide-y divide-[#E4E7EC] flex-1">
        @forelse ($top_services as $srv)
            <div class="p-3.5 flex items-center justify-between hover:bg-[#F8F9FC]/60 transition-colors">
                <div class="min-w-0 pr-3">
                    <h4 class="text-xs font-bold text-[#101828] truncate m-0">{{ $srv['name'] }}</h4>
                    <span class="text-[10px] text-[#667085] block mt-0.5">{{ $srv['category_name'] }} &bull; {{ number_format($srv['total_qty']) }} order</span>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-xs font-semibold text-[#101828] block">
                        Rp {{ number_format($srv['total_revenue'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-xs text-[#667085]">
                Belum ada layanan yang terjual.
            </div>
        @endforelse
    </div>
</x-cards.card>
