@php
    $serviceMode = $branch?->service_mode ?? 'appointment';
    $actionRoute = $serviceMode === 'drop_off' ? route('orders.index') : route('bookings.index');
    $actionText = $serviceMode === 'drop_off' ? 'Kelola Pesanan' : 'Kelola Booking';
@endphp

<x-cards.card class="overflow-hidden h-full flex flex-col">
    <div class="p-4 border-b border-[#E7ECF3] flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#101828] m-0">
                Aktivitas Layanan & Antrean Hari Ini
            </h3>
            <p class="text-xs text-[#6B7280] mt-0.5 m-0">
                Daftar reservasi dan pengerjaan layanan cabang hari ini.
            </p>
        </div>
        <a href="{{ $actionRoute }}" class="text-xs font-semibold text-[#062072] hover:underline flex items-center gap-1 shrink-0">
            {{ $actionText }}
            <x-icons.icon name="arrow-right" class="w-3.5 h-3.5" />
        </a>
    </div>

    <div class="overflow-x-auto flex-1">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#F8F9FC] border-b border-[#E7ECF3] text-[#6B7280] font-semibold text-[11px] uppercase tracking-wider">
                    <th class="py-2.5 px-3.5">Jam</th>
                    <th class="py-2.5 px-3.5">Pelanggan</th>
                    <th class="py-2.5 px-3.5">Layanan</th>
                    <th class="py-2.5 px-3.5">Staf / Teknisi</th>
                    <th class="py-2.5 px-3.5 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E7ECF3] text-[#101828]">
                @forelse ($today_activities as $act)
                    @php
                        $actTime = $act->time_label ?? (
                            $act->booking_time
                                ? \Carbon\Carbon::parse($act->booking_time)->format('H.i')
                                : ($act->created_at ? $act->created_at->timezone('Asia/Jakarta')->format('H.i') : '-')
                        );
                        $isDropOffOrder = isset($act->activity_type)
                            ? ($act->activity_type === 'drop_off')
                            : ($act instanceof \App\Models\Order);
                    @endphp
                    <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                        <td class="py-2.5 px-3.5 tabular-nums font-bold text-[#062072] whitespace-nowrap">
                            {{ $actTime }}
                        </td>
                        <td class="py-2.5 px-3.5 font-medium text-[#101828] truncate max-w-[150px]">
                            {{ $act->user?->name ?? 'Pelanggan Walk-in' }}
                        </td>
                        <td class="py-2.5 px-3.5 text-[#6B7280] truncate max-w-[160px]">
                            <div class="flex items-center gap-1.5">
                                <span class="truncate">{{ $act->service?->name ?? 'Layanan' }}</span>
                                @if ($serviceMode === 'both')
                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-medium shrink-0 {{ $isDropOffOrder ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $isDropOffOrder ? 'Drop-off' : 'Booking' }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-2.5 px-3.5 truncate max-w-[140px]">
                            @if ($act->employee)
                                <span class="text-[#101828] font-medium">{{ $act->employee->name }}</span>
                            @else
                                <span class="text-[#6B7280] italic">Belum Ditugaskan</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3.5 text-center whitespace-nowrap">
                            <x-badges.status :status="$act->status" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-[#6B7280]">
                            Belum ada aktivitas layanan atau antrean hari ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-cards.card>
