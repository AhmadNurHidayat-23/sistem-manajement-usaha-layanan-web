<x-cards.card class="overflow-hidden">
    <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">{{ __('dashboard.owner.recent_transactions') }}</h3>
            <p class="text-xs text-[#667085] mt-0.5 m-0">{{ __('dashboard.owner.recent_transactions_subtitle') }}</p>
        </div>
        <a href="{{ route('pos.transactions.index') }}" class="text-xs font-semibold text-[#062072] hover:underline">
            {{ __('dashboard.owner.all_transactions') }}
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold text-[11px] uppercase tracking-wider">
                    <th class="py-2 px-3">{{ __('dashboard.owner.trx_code') }}</th>
                    <th class="py-2 px-3">{{ __('dashboard.owner.branch') }}</th>
                    <th class="py-2 px-3">{{ __('dashboard.owner.cashier_on_duty') }}</th>
                    <th class="py-2 px-3 text-center">{{ __('dashboard.owner.payment_method') }}</th>
                    <th class="py-2 px-3 text-left">{{ __('dashboard.owner.total_amount') }}</th>
                    <th class="py-2 px-3">{{ __('dashboard.owner.trx_time') }}</th>
                    <th class="py-2 px-3 text-center">{{ __('dashboard.common.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                @forelse ($recent_transactions as $trx)
                    <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                        <td class="py-2.5 px-3 font-semibold text-[#062072] whitespace-nowrap">
                            <a href="{{ route('pos.transactions.show', $trx) }}" class="hover:underline">
                                {{ $trx->transaction_code }}
                            </a>
                        </td>
                        <td class="py-2.5 px-3 font-medium text-[#101828] truncate max-w-[150px]">{{ $trx->branch?->display_name ?? $trx->branch?->name ?? __('dashboard.owner.branch') }}</td>
                        <td class="py-2.5 px-3 text-[#667085] truncate max-w-[120px]">{{ $trx->cashier?->name ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                {{ $trx->payments->first()?->payment_method ?? 'Cash' }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-left font-semibold text-emerald-600 whitespace-nowrap">
                            Rp {{ number_format($trx->total, 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3 text-[#667085] text-xs whitespace-nowrap">
                            {{ $trx->transaction_date?->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                            <x-badges.status status="paid" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-[#667085]">
                            {{ __('dashboard.owner.no_transactions') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-cards.card>
