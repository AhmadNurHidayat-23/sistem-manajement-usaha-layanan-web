<!-- Tabel Tugas Layanan Hari Ini -->
<x-cards.card title="Tugas Layanan Hari Ini" subtitle="Daftar janji temu dan pesanan drop-off yang ditugaskan kepada Anda hari ini">
    @if ($todayTasks->isEmpty())
        <div class="py-8 text-center">
            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-xs font-semibold text-[#101828] m-0">Tidak ada tugas hari ini</p>
            <p class="text-[11px] text-[#667085] mt-0.5 m-0">Belum ada penugasan janji temu atau order drop-off untuk Anda pada hari berjalan.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#101828]">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-3 py-2.5">Waktu</th>
                        <th class="px-3 py-2.5">Tipe</th>
                        <th class="px-3 py-2.5">Layanan & Usaha</th>
                        <th class="px-3 py-2.5">Pelanggan</th>
                        <th class="px-3 py-2.5">Status</th>
                        <th class="px-3 py-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($todayTasks as $task)
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <td class="px-3 py-2.5 font-medium whitespace-nowrap text-[#062072]">
                                {{ $task['time'] }}
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $task['type'] === 'Appointment' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                    {{ $task['type'] }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="font-semibold text-[#101828] block">{{ $task['service_name'] }}</span>
                                <span class="text-[10px] text-[#667085]">{{ $task['branch_name'] }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-[#667085]">
                                {{ $task['customer_name'] }}
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ method_exists($task['status'], 'badgeClass') ? $task['status']->badgeClass() : 'bg-slate-100 text-slate-700' }}">
                                    {{ method_exists($task['status'], 'label') ? $task['status']->label() : ucfirst($task['status']->value ?? (string) $task['status']) }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-right">
                                <a href="{{ $task['detail_url'] }}" class="inline-flex items-center text-xs font-semibold text-[#062072] hover:text-[#0B3A9E] hover:underline">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-cards.card>
