<!-- Loyalty Point Transactions History Card -->
<x-cards.card title="Riwayat Poin Loyalitas" subtitle="Catatan penambahan dan penukaran poin member ini">
    @if ($membership->loyaltyTransactions->isEmpty())
        <div class="py-10 text-center bg-[#F5F8FC] rounded-lg">
            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-xs font-semibold text-[#101828] m-0">Belum ada riwayat transaksi poin</p>
            <p class="text-[11px] text-[#667085] mt-0.5 m-0">Poin otomatis bertambah ketika member melakukan transaksi servis.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#101828]">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Tipe Mutasi</th>
                        <th class="px-4 py-3">Poin</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-4 py-3">Transaksi Terkait</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($membership->loyaltyTransactions as $trx)
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <!-- Date -->
                            <td class="px-4 py-3 text-[#667085] whitespace-nowrap">
                                {{ $trx->created_at->format('d M Y H:i') }}
                            </td>

                            <!-- Type -->
                            <td class="px-4 py-3">
                                @php
                                    $typeBadges = [
                                        'earned' => ['label' => 'Poin Masuk', 'badge' => 'success'],
                                        'redeemed' => ['label' => 'Tukar Poin', 'badge' => 'danger'],
                                        'adjustment' => ['label' => 'Penyesuaian', 'badge' => 'primary'],
                                        'expired' => ['label' => 'Kedaluwarsa', 'badge' => 'neutral'],
                                    ];
                                    $tb = $typeBadges[$trx->type->value] ?? ['label' => $trx->type->value, 'badge' => 'neutral'];
                                @endphp
                                <x-badges.badge :variant="$tb['badge']">{{ $tb['label'] }}</x-badges.badge>
                            </td>

                            <!-- Points Value -->
                            <td class="px-4 py-3 font-semibold text-sm whitespace-nowrap">
                                @if ($trx->points > 0)
                                    <span class="text-emerald-600">+{{ number_format($trx->points) }}</span>
                                @else
                                    <span class="text-rose-600">{{ number_format($trx->points) }}</span>
                                @endif
                            </td>

                            <!-- Description -->
                            <td class="px-4 py-3 font-medium text-[#101828]">
                                {{ $trx->description }}
                            </td>

                            <!-- Transaction Code Link -->
                            <td class="px-4 py-3 text-[#667085]">
                                @if ($trx->transaction)
                                    <a href="{{ route('pos.transactions.show', $trx->transaction) }}" class="font-semibold text-[#062072] hover:underline">
                                        {{ $trx->transaction->transaction_code }}
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-cards.card>
