<!-- Riwayat Tugas -->
<x-cards.card :title="__('dashboard.employee.task_history')" :subtitle="__('dashboard.employee.task_history_subtitle')">
    @if ($historyTasks->isEmpty())
        <div class="py-8 text-center">
            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-xs font-semibold text-[#101828] m-0">{{ __('dashboard.employee.no_task_history') }}</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#101828]">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-3 py-2.5">{{ __('dashboard.employee.date') }}</th>
                        <th class="px-3 py-2.5">{{ __('dashboard.employee.service_business') }}</th>
                        <th class="px-3 py-2.5">{{ __('dashboard.employee.customer') }}</th>
                        <th class="px-3 py-2.5">{{ __('dashboard.common.status') }}</th>
                        <th class="px-3 py-2.5 text-right">{{ __('dashboard.common.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($historyTasks as $hist)
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <td class="px-3 py-2.5 text-[#667085] whitespace-nowrap">
                                <span class="font-medium text-[#101828] block">{{ $hist['date'] }}</span>
                                <span class="text-[10px] text-[#667085]">{{ $hist['time'] }}</span>
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="font-semibold text-[#101828] block">{{ $hist['service_name'] }}</span>
                                <span class="text-[10px] text-[#667085]">{{ $hist['branch_name'] }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-[#667085] whitespace-nowrap">
                                {{ $hist['customer_name'] }}
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ method_exists($hist['status'], 'badgeClass') ? $hist['status']->badgeClass() : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ method_exists($hist['status'], 'label') ? $hist['status']->label() : ucfirst($hist['status']->value ?? (string) $hist['status']) }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                <a href="{{ $hist['detail_url'] }}" class="inline-flex items-center px-2 py-1 text-xs font-semibold text-[#062072] hover:text-[#0B3A9E] hover:underline">
                                    {{ __('dashboard.common.detail') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-cards.card>
