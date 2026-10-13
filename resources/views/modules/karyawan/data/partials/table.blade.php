<!-- Tabel daftar karyawan -->
<x-cards.card title="Daftar Karyawan Terdaftar" subtitle="Total {{ $employees->total() }} karyawan ditemukan">
    @if ($employees->isEmpty())
        <div class="py-12 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <x-icons.icon name="employee" class="w-6 h-6" />
            </div>
            <p class="text-sm font-semibold text-[#101828] m-0">Tidak ada data karyawan</p>
            <p class="text-xs text-[#667085] mt-1 m-0">Silakan tambahkan data karyawan baru atau sesuaikan filter pencarian.</p>
            <div class="mt-4">
                <x-buttons.btn variant="primary" href="{{ route('employees.create') }}" class="gap-1.5 text-xs">
                    <x-icons.icon name="plus" class="w-4 h-4" />
                    Tambah Karyawan Baru
                </x-buttons.btn>
            </div>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] text-[#101828]">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] uppercase text-xs tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3">Karyawan</th>
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Skill / Keahlian</th>
                        <th class="px-4 py-3">Shift Hari Ini</th>
                        <th class="px-4 py-3">Estimasi Komisi</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($employees as $employee)
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <td class="px-3 py-2.5">
                                <div class="flex items-center gap-2.5">
                                    @if ($employee->avatar_url)
                                        <img src="{{ $employee->avatar_url }}" alt="{{ $employee->name }}" class="w-7 h-7 rounded-full object-cover shrink-0 border border-[#E4E7EC]">
                                    @else
                                        <div class="w-7 h-7 rounded-full bg-[#DCE8FF] text-[#062072] flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($employee->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('employees.show', $employee) }}" class="font-bold text-[#101828] hover:text-[#062072] hover:underline block leading-tight">
                                            {{ $employee->name }}
                                        </a>
                                        <span class="text-[11px] text-[#667085] block">{{ $employee->phone }}</span>
                                        @if ($employee->user)
                                            <span class="text-[10px] text-slate-500 flex items-center gap-1">
                                                <x-icons.icon name="check" class="w-3 h-3 text-emerald-500 inline" />
                                                {{ $employee->user->email }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <span class="font-semibold text-[#101828]">{{ $employee->branch->display_name }}</span>
                            </td>

                            <td class="px-3 py-2.5">
                                @if ($employee->skills->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach ($employee->skills->take(3) as $skill)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#DCE8FF] text-[#062072]">
                                                {{ $skill->skill_name }}
                                            </span>
                                        @endforeach
                                        @if ($employee->skills->count() > 3)
                                            <span class="inline-flex items-center px-1 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                                +{{ $employee->skills->count() - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[11px] text-[#667085] italic">Belum ada skill</span>
                                @endif
                            </td>

                            <td class="px-3 py-2.5 whitespace-nowrap">
                                @php
                                    $todayShift = $employee->shifts->first();
                                @endphp
                                @if ($todayShift)
                                    <div class="text-[11px]">
                                        <span class="font-semibold text-[#101828]">
                                            {{ \Carbon\Carbon::parse($todayShift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($todayShift->end_time)->format('H:i') }}
                                        </span>
                                        <div class="mt-0.5">
                                            @if ($todayShift->status->value === 'present')
                                                <span class="text-[10px] text-emerald-700 font-medium">Hadir</span>
                                            @elseif ($todayShift->status->value === 'late')
                                                <span class="text-[10px] text-amber-700 font-medium">Terlambat</span>
                                            @elseif ($todayShift->status->value === 'absent')
                                                <span class="text-[10px] text-rose-700 font-medium">Absen</span>
                                            @elseif ($todayShift->status->value === 'off')
                                                <span class="text-[10px] text-slate-500 font-medium">Libur</span>
                                            @else
                                                <span class="text-[10px] text-blue-700 font-medium">Terjadwal</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-[11px] text-[#667085]">Tidak ada shift</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-[13px]">
                                    <span class="font-semibold text-[#101828]">
                                        Rp {{ number_format($employee->pending_commissions_sum ?? 0, 0, ',', '.') }}
                                    </span>
                                    <span class="text-xs text-[#667085] block">
                                        Disetujui: Rp {{ number_format($employee->approved_commissions_sum ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-3 py-2.5 whitespace-nowrap">
                                @if ($employee->status === 'active')
                                    <x-badges.badge variant="success">Aktif</x-badges.badge>
                                @else
                                    <x-badges.badge variant="neutral">Nonaktif</x-badges.badge>
                                @endif
                            </td>

                            <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('employees.show', $employee) }}" class="h-7 w-7 inline-flex items-center justify-center text-[#062072] hover:bg-[#DCE8FF] rounded-lg transition-colors" title="Lihat Profil & Kinerja">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>

                                    <a href="{{ route('employees.edit', $employee) }}"
                                       data-action="edit-employee"
                                       data-payload="{{ json_encode([
                                           'id' => $employee->id,
                                           'name' => $employee->name,
                                           'phone' => $employee->phone,
                                           'branch_id' => $employee->branch_id,
                                           'status' => $employee->status,
                                           'user_id' => $employee->user_id,
                                           'skills' => $employee->skills->pluck('skill_name')->implode(', '),
                                       ]) }}"
                                       class="h-7 w-7 inline-flex items-center justify-center text-slate-600 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer" title="Edit Data">
                                        <x-icons.icon name="edit" class="w-3.5 h-3.5" />
                                    </a>

                                    <form method="POST" action="{{ route('employees.toggle-status', $employee) }}" class="inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status aktif karyawan ini?');">
                                        @csrf
                                        <button type="submit" class="h-7 w-7 inline-flex items-center justify-center {{ $employee->status === 'active' ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }} rounded-lg transition-colors cursor-pointer" title="{{ $employee->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            @if ($employee->status === 'active')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                </svg>
                                            @else
                                                <x-icons.icon name="check" class="w-3.5 h-3.5" />
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginasi tabel -->
        <div class="pt-4 border-t border-[#E4E7EC]">
            {{ $employees->withQueryString()->links() }}
        </div>
    @endif
</x-cards.card>
