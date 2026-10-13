<!-- Shifts Table Card -->
<x-cards.card title="Daftar Jadwal Shift" subtitle="Total {{ $shifts->total() }} jadwal shift tercatat">
    @if ($shifts->isEmpty())
        <div class="py-12 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-sm font-semibold text-[#101828] m-0">Tidak ada jadwal shift</p>
            <p class="text-xs text-[#667085] mt-1 m-0">Belum ada data shift untuk kriteria yang dipilih.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] text-[#101828]">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] uppercase text-xs tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Karyawan</th>
                        <th class="px-4 py-3">Nama Usaha</th>
                        <th class="px-4 py-3">Jam Kerja</th>
                        <th class="px-4 py-3">Status Kehadiran</th>
                        @if (! ($isSelfView ?? false))
                            <th class="px-4 py-3 text-right">Update Kehadiran & Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($shifts as $shift)
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <!-- Date -->
                            <td class="px-4 py-3 font-semibold text-[#101828]">
                                {{ \Carbon\Carbon::parse($shift->shift_date)->translatedFormat('l, d M Y') }}
                            </td>

                            <!-- Employee -->
                            <td class="px-4 py-3">
                                @if ($isSelfView ?? false)
                                    <span class="font-bold text-[#101828]">{{ $shift->employee->name }}</span>
                                @else
                                    <a href="{{ route('employees.show', $shift->employee) }}" class="font-bold text-[#101828] hover:text-[#062072] hover:underline">
                                        {{ $shift->employee->name }}
                                    </a>
                                @endif
                                <span class="text-[10px] text-[#667085] block">{{ $shift->employee->phone }}</span>
                            </td>

                            <!-- Branch -->
                            <td class="px-4 py-3 text-[#667085]">
                                {{ $shift->branch->display_name }}
                            </td>

                            <!-- Times -->
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 font-semibold text-[#062072]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                </span>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-4 py-3">
                                @php
                                    $statusVariants = [
                                        'scheduled' => ['label' => 'Terjadwal', 'badge' => 'primary'],
                                        'present' => ['label' => 'Hadir', 'badge' => 'success'],
                                        'late' => ['label' => 'Terlambat', 'badge' => 'warning'],
                                        'absent' => ['label' => 'Tidak Hadir', 'badge' => 'danger'],
                                        'off' => ['label' => 'Libur', 'badge' => 'neutral'],
                                    ];
                                    $cStatus = $shift->status->value ?? 'scheduled';
                                    $badgeInfo = $statusVariants[$cStatus] ?? ['label' => $cStatus, 'badge' => 'neutral'];
                                @endphp
                                <x-badges.badge :variant="$badgeInfo['badge']">{{ $badgeInfo['label'] }}</x-badges.badge>
                            </td>

                            <!-- Actions -->
                            @if (! ($isSelfView ?? false))
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <form method="POST" action="{{ route('employees.shifts.status', $shift) }}" class="inline m-0">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="present">
                                            <button type="submit" title="Tandai Hadir" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded text-[10px] font-semibold transition-colors">
                                                Hadir
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('employees.shifts.status', $shift) }}" class="inline m-0">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="late">
                                            <button type="submit" title="Tandai Terlambat" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded text-[10px] font-semibold transition-colors">
                                                Telat
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('employees.shifts.status', $shift) }}" class="inline m-0">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="absent">
                                            <button type="submit" title="Tandai Absen" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-[10px] font-semibold transition-colors">
                                                Absen
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('employees.shifts.destroy', $shift) }}" class="inline m-0" onsubmit="return confirm('Hapus shift ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition-colors" title="Hapus Shift">
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

        <!-- Pagination -->
        <div class="pt-4 border-t border-[#E4E7EC]">
            {{ $shifts->withQueryString()->links() }}
        </div>
    @endif
</x-cards.card>
