<x-cards.card class="overflow-hidden">
    <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">Transaksi Kasir Terbaru di Cabang</h3>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Data transaksi pembayaran di cabang outlet Anda.</p>
        </div>
        <a href="{{ route('pos.transactions.index') }}" class="text-xs font-semibold text-[#062072] hover:underline flex items-center gap-1">
            Semua Transaksi Cabang
            <x-icons.icon name="arrow-right" class="w-3.5 h-3.5" />
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold text-[11px] uppercase tracking-wider">
                    <th class="py-2 px-3">Kode Transaksi</th>
                    <th class="py-2 px-3">Kasir</th>
                    <th class="py-2 px-3 text-center">Metode Bayar</th>
                    <th class="py-2 px-3 text-right">Total Transaksi</th>
                    <th class="py-2 px-3">Waktu Transaksi</th>
                    <th class="py-2 px-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                @forelse ($recent_transactions as $trx)
                    <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                        <td class="py-2.5 px-3 tabular-nums font-bold text-[#062072] whitespace-nowrap">
                            <a href="{{ route('pos.transactions.show', $trx) }}" class="hover:underline">
                                {{ $trx->transaction_code }}
                            </a>
                        </td>
                        <td class="py-2.5 px-3 font-medium text-[#101828] truncate max-w-[140px]">{{ $trx->cashier?->name ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                {{ $trx->payments->first()?->payment_method ?? 'Cash' }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-right font-bold text-emerald-600 whitespace-nowrap">
                            Rp {{ number_format($trx->total, 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3 text-[#667085] tabular-nums text-[11px] whitespace-nowrap">
                            {{ $trx->transaction_date?->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                            <x-badges.status status="paid" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-[#667085]">
                            Belum ada transaksi kasir pada cabang ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-cards.card>
