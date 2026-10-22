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
        {{-- Tampilan Mobile / Tablet Sempit: Susunan Kartu Karyawan Responsif --}}
        <div class="md:hidden divide-y divide-[#E4E7EC]">
            @foreach ($employees as $employee)
                <div class="p-4 space-y-3 hover:bg-[#F5F8FC]/50 transition-colors">
                    {{-- Header Kartu: Avatar, Nama, Telepon & Status --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if ($employee->avatar_url)
                                <img src="{{ $employee->avatar_url }}" alt="{{ $employee->name }}" class="w-10 h-10 rounded-full object-cover shrink-0 border border-[#E4E7EC]">
                            @else
                                <div class="w-10 h-10 rounded-full bg-[#DCE8FF] text-[#062072] flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <a href="{{ route('employees.show', $employee) }}" class="font-bold text-sm text-[#101828] hover:text-[#062072] block truncate leading-snug">
                                    {{ $employee->name }}
                                </a>
                                <span class="text-xs text-[#667085] block truncate">{{ $employee->phone }}</span>
                                @if ($employee->user)
                                    <span class="text-[11px] text-slate-500 flex items-center gap-1 truncate mt-0.5">
                                        <x-icons.icon name="check" class="w-3 h-3 text-emerald-500 shrink-0 inline" />
                                        <span class="truncate">{{ $employee->user->email }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="shrink-0">
                            @if ($employee->status === 'active')
                                <x-badges.badge variant="success">Aktif</x-badges.badge>
                            @else
                                <x-badges.badge variant="neutral">Nonaktif</x-badges.badge>
                            @endif
                        </div>
                    </div>

                    {{-- Informasi Cabang & Shift --}}
                    <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-[#F2F4F7]">
                        <div>
                            <span class="text-[10px] uppercase font-semibold text-[#667085] block">Cabang</span>
                            <span class="font-semibold text-[#101828] truncate block mt-0.5">{{ $employee->branch->display_name }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-semibold text-[#667085] block">Shift Hari Ini</span>
                            @php $todayShift = $employee->shifts->first(); @endphp
                            @if ($todayShift)
                                <span class="font-semibold text-[#101828] block mt-0.5">
                                    {{ \Carbon\Carbon::parse($todayShift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($todayShift->end_time)->format('H:i') }}
                                </span>
                                <span class="text-[10px] font-medium {{ $todayShift->status->value === 'present' ? 'text-emerald-700' : ($todayShift->status->value === 'late' ? 'text-amber-700' : 'text-slate-600') }}">
                                    {{ match($todayShift->status->value) { 'present' => 'Hadir', 'late' => 'Terlambat', 'absent' => 'Absen', 'off' => 'Libur', default => 'Terjadwal' } }}
                                </span>
                            @else
                                <span class="text-[#667085] italic block mt-0.5">Tidak ada shift</span>
                            @endif
                        </div>
                    </div>

                    {{-- Estimasi Komisi & Keahlian --}}
                    <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                        <div>
                            <span class="text-[10px] uppercase font-semibold text-[#667085] block">Estimasi Komisi</span>
                            <span class="font-bold text-[#062072] block mt-0.5">
                                Rp {{ number_format($employee->pending_commissions_sum ?? 0, 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] text-[#667085]">
                                Disetujui: Rp {{ number_format($employee->approved_commissions_sum ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-semibold text-[#667085] block">Keahlian</span>
                            @if ($employee->skills->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mt-0.5">
                                    @foreach ($employee->skills->take(2) as $skill)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#DCE8FF] text-[#062072]">
                                            {{ $skill->skill_name }}
                                        </span>
                                    @endforeach
                                    @if ($employee->skills->count() > 2)
                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                            +{{ $employee->skills->count() - 2 }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-[11px] text-[#667085] italic block mt-0.5">-</span>
                            @endif
                        </div>
                    </div>

                    {{-- Tombol Aksi Mobile --}}
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#F2F4F7]">
                        <a href="{{ route('employees.show', $employee) }}"
                           class="h-8 px-3 inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-[#062072] bg-[#EAF2FF] hover:bg-[#D8E6FD] border border-[#C2D8FC] rounded-lg transition-colors no-underline">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span>Detail</span>
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
                           class="h-8 px-3 inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-[#D0D5DD] rounded-lg transition-colors cursor-pointer no-underline">
                            <x-icons.icon name="edit" class="w-3.5 h-3.5 text-slate-600" />
                            <span>Edit</span>
                        </a>

                        <form method="POST" action="{{ route('employees.toggle-status', $employee) }}" class="inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status aktif karyawan ini?');">
                            @csrf
                            <button type="submit"
                                    class="h-8 px-2.5 inline-flex items-center justify-center gap-1 text-xs font-semibold {{ $employee->status === 'active' ? 'text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100' : 'text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100' }} rounded-lg transition-colors cursor-pointer"
                                    title="{{ $employee->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                @if ($employee->status === 'active')
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                    <span>Nonaktif</span>
                                @else
                                    <x-icons.icon name="check" class="w-3.5 h-3.5" />
                                    <span>Aktifkan</span>
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tampilan Desktop & Layar Lebar: Tabel Lengkap --}}
        <div class="hidden md:block overflow-x-auto">
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
        <div class="p-4 border-t border-[#E4E7EC]">
            {{ $employees->withQueryString()->links() }}
        </div>
    @endif
</x-cards.card>
