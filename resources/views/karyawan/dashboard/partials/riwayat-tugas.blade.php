<!-- Riwayat Tugas -->
<x-cards.card title="Riwayat Tugas" subtitle="Daftar layanan yang telah Anda selesaikan sebelumnya">
    @if ($historyTasks->isEmpty())
        <div class="py-8 text-center">
            <p class="text-xs text-[#667085] m-0">Belum ada riwayat tugas sebelumnya.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#101828]">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-3 py-2">Tanggal</th>
                        <th class="px-3 py-2">Layanan</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($historyTasks as $hist)
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <td class="px-3 py-2 text-[#667085] whitespace-nowrap">
                                {{ $hist['date'] }}
                            </td>
                            <td class="px-3 py-2 font-medium text-[#101828]">
                                {{ $hist['service_name'] }}
                            </td>
                            <td class="px-3 py-2">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ method_exists($hist['status'], 'badgeClass') ? $hist['status']->badgeClass() : 'bg-slate-100 text-slate-700' }}">
                                    {{ method_exists($hist['status'], 'label') ? $hist['status']->label() : ucfirst($hist['status']->value ?? (string) $hist['status']) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ $hist['detail_url'] }}" class="text-xs text-[#062072] hover:underline">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-cards.card>
