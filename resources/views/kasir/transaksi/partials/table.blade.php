<!-- Tabel riwayat transaksi -->
<x-cards.card>
    <div class="overflow-x-auto">
        <table class="w-full text-[13px] text-left">
            <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] font-semibold uppercase text-xs tracking-wider">
                <tr>
                    <th class="py-3 px-4">Kode TRX</th>
                    <th class="py-3 px-4">Tanggal / Waktu</th>
                    <th class="py-3 px-4">Cabang</th>
                    <th class="py-3 px-4">Pelanggan</th>
                    <th class="py-3 px-4">Metode Bayar</th>
                    <th class="py-3 px-4 text-right">Subtotal</th>
                    <th class="py-3 px-4 text-right">Diskon</th>
                    <th class="py-3 px-4 text-right">Total Akhir</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC]">
                @forelse ($transactions as $trx)
                    <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-[#062072] whitespace-nowrap">
                            <a href="{{ route('pos.transactions.show', $trx) }}" class="hover:underline">
                                {{ $trx->transaction_code }}
                            </a>
                        </td>
                        <td class="py-2.5 px-3 text-[#667085] whitespace-nowrap">
                            <div>{{ $trx->transaction_date->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-[#98A2B3]">{{ $trx->transaction_date->format('H:i') }} WIB</div>
                        </td>
                        <td class="py-2.5 px-3 font-medium text-[#101828] truncate max-w-[120px]">
                            {{ $trx->branch->display_name }}
                        </td>
                        <td class="py-2.5 px-3 truncate max-w-[130px]">
                            <div class="font-semibold text-[#101828] truncate">{{ $trx->customer_name ?: 'Pelanggan Umum' }}</div>
                            @if ($trx->customer_phone)
                                <div class="text-[10px] text-[#667085] truncate">{{ $trx->customer_phone }}</div>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 whitespace-nowrap">
                            @php
                                $actualMethod = $trx->actual_payment_method;
                                $methodLabel = $trx->payment_method_label;
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                @if($actualMethod === 'cash') bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($actualMethod === 'qris') bg-[#DCE8FF] text-[#062072] border border-[#0B3A9E]/20
                                @elseif($actualMethod === 'transfer') bg-[#DCE8FF] text-[#062072] border border-[#0B3A9E]/20
                                @elseif(in_array($actualMethod, ['e_wallet', 'e-wallet'], true)) bg-blue-50 text-blue-700 border border-blue-200
                                @elseif($actualMethod === 'virtual_account') bg-indigo-50 text-indigo-700 border border-indigo-200
                                @else bg-slate-100 text-slate-700 border border-slate-200 @endif">
                                {{ $methodLabel }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-right font-medium text-[#667085] whitespace-nowrap">
                            Rp {{ number_format($trx->subtotal, 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-medium text-emerald-600 whitespace-nowrap">
                            @if ($trx->discount > 0)
                                - Rp {{ number_format($trx->discount, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-right font-bold text-[#101828] whitespace-nowrap">
                            Rp {{ number_format($trx->total, 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                            @if ($trx->status === \App\Enums\StatusTransaksi::Paid)
                                <x-badges.badge variant="success">Lunas</x-badges.badge>
                            @elseif ($trx->status === \App\Enums\StatusTransaksi::Refunded)
                                <x-badges.badge variant="danger">Dikembalikan</x-badges.badge>
                            @else
                                <x-badges.badge :variant="$trx->status->badgeVariant()">{{ $trx->status->label() }}</x-badges.badge>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('pos.transactions.show', $trx) }}" class="h-7 px-2 inline-flex items-center justify-center rounded-lg border border-[#E4E7EC] text-[#062072] hover:bg-[#F5F8FC] transition-colors text-xs" title="Lihat Detail Transaksi">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('pos.transactions.receipt', $trx) }}" class="h-7 px-2 inline-flex items-center justify-center rounded-lg border border-[#E4E7EC] text-emerald-600 hover:bg-emerald-50 transition-colors text-xs" title="Cetak Struk">
                                    <x-icons.icon name="printer" class="w-3.5 h-3.5" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-[#667085]">
                            Belum ada transaksi yang selesai dibayar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($transactions->hasPages())
        <div class="mt-4 pt-3 border-t border-[#E4E7EC]">
            {{ $transactions->links() }}
        </div>
    @endif
</x-cards.card>
