<!-- 4. Pendapatan per Layanan & Metode Pembayaran (Two-Column) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Pendapatan per Layanan (2 Cols) -->
    <div class="lg:col-span-2">
        <x-cards.card class="overflow-hidden h-full flex flex-col">
            <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-[#101828] m-0">Pendapatan per Layanan</h3>
                    <p class="text-xs text-[#667085] mt-0.5 m-0">Produk servis terlaris dihitung dari rincian transaksi.</p>
                </div>
                <span class="text-xs text-[#667085] font-medium">
                    Total: {{ count($report['services']) }} Layanan
                </span>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold text-[11px] uppercase tracking-wider">
                            <th class="py-2 px-3">Nama Layanan</th>
                            <th class="py-2 px-3">Kategori</th>
                            <th class="py-2 px-3 text-center">Terjual</th>
                            <th class="py-2 px-3 text-right">Total Pendapatan</th>
                            <th class="py-2 px-3 text-center">Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                        @forelse ($report['services'] as $srv)
                            <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                                <td class="py-2.5 px-3 font-bold text-[#101828] whitespace-nowrap">{{ $srv['service_name'] }}</td>
                                <td class="py-2.5 px-3 text-[#667085] whitespace-nowrap">{{ $srv['category_name'] }}</td>
                                <td class="py-2.5 px-3 text-center font-bold">{{ number_format($srv['total_qty']) }} unit</td>
                                <td class="py-2.5 px-3 text-right font-semibold text-[#101828] whitespace-nowrap">
                                    Rp {{ number_format($srv['total_revenue'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#DCE8FF] text-[#062072]">
                                        {{ $srv['percentage'] }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-[#667085]">
                                    Belum ada penjualan layanan pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-cards.card>
    </div>

    <!-- Metode Pembayaran (1 Col) -->
    <div>
        <x-cards.card class="p-5 h-full flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-[#101828] m-0">Metode Pembayaran</h3>
                <p class="text-xs text-[#667085] mt-0.5 mb-4">Komposisi kanal pembayaran yang digunakan pelanggan.</p>

                <div class="space-y-4">
                    @forelse ($report['payment_methods'] as $pm)
                        <div class="p-3 rounded-lg border border-[#E4E7EC] bg-[#F8F9FC]/50">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="font-bold text-[#101828]">{{ $pm['method_label'] }}</span>
                                <span class="font-bold text-[#062072]">Rp {{ number_format($pm['total_amount'], 0, ',', '.') }}</span>
                            </div>
                            <div class="w-full bg-[#E4E7EC] rounded-full h-2 overflow-hidden mb-1">
                                <div class="bg-[#062072] h-2 rounded-full" style="width: {{ $pm['percentage'] }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-[#667085]">
                                <span>{{ $pm['count'] }} kali transaksi</span>
                                <span class="font-semibold">{{ $pm['percentage'] }}% dari omzet</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-[#667085] text-center py-6">Belum ada transaksi pembayaran.</p>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-[#E4E7EC] text-xs text-[#667085] flex items-center justify-between">
                <span>Total Pembayaran Masuk:</span>
                <strong class="text-[#101828]">Rp {{ number_format($report['summary']['total_omzet'], 0, ',', '.') }}</strong>
            </div>
        </x-cards.card>
    </div>
</div>
