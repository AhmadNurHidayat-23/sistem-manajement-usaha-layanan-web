<x-cards.card class="overflow-hidden">
    <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">{{ __('dashboard.owner.employee_performance') }}</h3>
            <p class="text-xs text-[#667085] mt-0.5 m-0">{{ __('dashboard.owner.employee_performance_subtitle') }}</p>
        </div>
        <x-badges.badge variant="neutral" class="text-xs">
            {{ __('dashboard.owner.top_5_staff') }}
        </x-badges.badge>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold text-[11px] uppercase tracking-wider">
                    <th class="py-2 px-3">{{ __('dashboard.owner.staff_name') }}</th>
                    <th class="py-2 px-3">{{ __('dashboard.owner.branch') }}</th>
                    <th class="py-2 px-3 text-center">{{ __('dashboard.owner.transactions') }}</th>
                    <th class="py-2 px-3 text-right">{{ __('dashboard.owner.revenue_generated') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                @forelse ($top_employees as $emp)
                    <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                        <td class="py-2.5 px-3 font-bold text-[#101828]">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-[#062072] text-[#DCE8FF] flex items-center justify-center text-[10px] font-bold shrink-0">
                                    {{ strtoupper(substr($emp['name'], 0, 2)) }}
                                </div>
                                <span class="truncate max-w-[150px]">{{ $emp['name'] }}</span>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-[#667085] truncate max-w-[120px]">{{ $emp['branch_name'] }}</td>
                        <td class="py-2.5 px-3 text-center font-bold">{{ $emp['transaction_count'] }}</td>
                        <td class="py-2.5 px-3 text-right font-semibold text-[#101828] whitespace-nowrap">
                            Rp {{ number_format($emp['total_revenue'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-[#667085]">
                            {{ __('dashboard.owner.no_employee_data') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-cards.card>
