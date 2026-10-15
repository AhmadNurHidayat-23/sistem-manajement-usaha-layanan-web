<!-- Shift Management Section -->
<x-cards.card class="p-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h4 class="text-sm font-bold text-[#101828] m-0">Jadwal Shift Kerja Terbaru</h4>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Catatan jadwal kehadiran dan jam kerja di cabang.</p>
        </div>
        @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
            <button type="button" onclick="document.getElementById('modal-add-shift').classList.remove('hidden')" class="px-3 py-1.5 bg-[#062072] text-white rounded-lg text-xs font-semibold hover:bg-[#0B3A9E] transition-colors inline-flex items-center gap-1.5">
                <x-icons.icon name="plus" class="w-3.5 h-3.5" />
                + Jadwalkan Shift
            </button>
        @endif
    </div>

    @if ($performance['recent_shifts']->isEmpty())
        <div class="py-8 text-center bg-[#F5F8FC] rounded-lg">
            <p class="text-xs text-[#667085] m-0">Belum ada jadwal shift yang tercatat untuk staf ini.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#101828]">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-2.5">Tanggal</th>
                        <th class="px-4 py-2.5">Jam Kerja</th>
                        <th class="px-4 py-2.5">Cabang</th>
                        <th class="px-4 py-2.5">Status Kehadiran</th>
                        @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
                            <th class="px-4 py-2.5 text-right">Update Status / Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($performance['recent_shifts'] as $shift)
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <td class="px-4 py-2.5 font-medium">
                                {{ \Carbon\Carbon::parse($shift->shift_date)->translatedFormat('l, d M Y') }}
                            </td>
                            <td class="px-4 py-2.5 font-semibold text-[#062072]">
                                {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                            </td>
                            <td class="px-4 py-2.5 text-[#667085]">
                                {{ $shift->branch->display_name }}
                            </td>
                            <td class="px-4 py-2.5">
                                @php
                                    $variants = [
                                        'scheduled' => ['label' => 'Terjadwal', 'badge' => 'neutral'],
                                        'present' => ['label' => 'Hadir', 'badge' => 'success'],
                                        'late' => ['label' => 'Terlambat', 'badge' => 'warning'],
                                        'absent' => ['label' => 'Absen', 'badge' => 'danger'],
                                        'off' => ['label' => 'Libur', 'badge' => 'neutral'],
                                    ];
                                    $sStatus = $shift->status->value ?? 'scheduled';
                                    $v = $variants[$sStatus] ?? ['label' => $sStatus, 'badge' => 'neutral'];
                                @endphp
                                <x-badges.badge :variant="$v['badge']">{{ $v['label'] }}</x-badges.badge>
                            </td>
                            @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
                                <td class="px-4 py-2.5 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <form method="POST" action="{{ route('employees.shifts.update-status', $shift) }}" class="inline m-0">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="present">
                                            <button type="submit" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded text-[10px] font-semibold transition-colors">
                                                Hadir
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('employees.shifts.update-status', $shift) }}" class="inline m-0">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="late">
                                            <button type="submit" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded text-[10px] font-semibold transition-colors">
                                                Telat
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('employees.shifts.update-status', $shift) }}" class="inline m-0">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="absent">
                                            <button type="submit" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-[10px] font-semibold transition-colors">
                                                Absen
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('employees.shifts.destroy', $shift) }}" class="inline m-0" onsubmit="return confirm('Hapus jadwal shift ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition-colors" title="Hapus">
                                                <x-icons.icon name="trash" class="w-3.5 h-3.5" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-cards.card>
