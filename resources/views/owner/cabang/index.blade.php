@extends('layouts.app', ['title' => 'Manajemen Cabang'])

@section('content')
<div class="space-y-6">
    <!-- Action Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">Daftar Cabang</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Kelola informasi lokasi cabang, jam operasional, dan mode layanan.</p>
        </div>
        @if (auth()->user()?->role?->name === 'owner')
            <x-buttons.btn variant="primary" href="{{ route('branches.create') }}" data-modal-open="modal-create-branch">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Cabang
            </x-buttons.btn>
        @endif
    </div>

    <!-- Alert Flash -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif

    <!-- Data Table -->
    <div class="space-y-4">
        <x-tables.table>
            <x-slot:headerSlot>
                <th scope="col" class="px-4 py-3 text-left min-w-[180px] whitespace-nowrap">Nama Cabang</th>
                <th scope="col" class="px-4 py-3 text-left min-w-[240px]">Alamat</th>
                <th scope="col" class="px-4 py-3 text-left min-w-[130px] whitespace-nowrap">Telepon</th>
                <th scope="col" class="px-4 py-3 text-left min-w-[150px] whitespace-nowrap">Jam Buka-Tutup</th>
                <th scope="col" class="px-4 py-3 text-left min-w-[170px] whitespace-nowrap">Mode Layanan</th>
                <th scope="col" class="px-4 py-3 text-center min-w-[110px] whitespace-nowrap">Kapasitas Slot</th>
                <th scope="col" class="px-4 py-3 text-center min-w-[90px] whitespace-nowrap">Status</th>
                <th scope="col" class="px-4 py-3 text-center min-w-[210px] whitespace-nowrap">Aksi</th>
            </x-slot:headerSlot>

            @php
                $modeLabels = [
                    'both' => 'Appointment & Drop-off',
                    'appointment' => 'Appointment',
                    'drop_off' => 'Drop-off',
                ];
            @endphp

            @forelse ($branches as $branch)
                <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                    <td class="px-4 py-3 font-semibold text-[#101828] whitespace-nowrap">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-md bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0 font-bold text-[11px]">
                                {{ strtoupper(substr($branch->display_name, 0, 2)) }}
                            </div>
                            <span class="truncate max-w-[180px]">{{ $branch->display_name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-[#667085] max-w-xs xl:max-w-sm truncate" title="{{ $branch->address }}">
                        {{ $branch->address }}
                    </td>
                    <td class="px-4 py-3 text-[#101828] whitespace-nowrap">
                        {{ $branch->phone ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-[#667085] whitespace-nowrap">
                        @if($branch->opening_time && $branch->closing_time)
                            <span class="text-[#101828] font-medium">
                                {{ \Carbon\Carbon::parse($branch->opening_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($branch->closing_time)->format('H:i') }}
                            </span>
                            <span class="text-[#667085] text-xs ml-0.5">WIB</span>
                        @else
                            <span class="text-[#667085]">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#F5F8FC] border border-[#E4E7EC] text-xs font-medium text-[#101828]">
                            {{ $modeLabels[$branch->service_mode] ?? ucwords(str_replace('_', ' ', $branch->service_mode)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-md bg-[#F5F8FC] border border-[#E4E7EC] text-xs font-semibold text-[#101828]">
                            {{ $branch->max_slot_capacity }} Slot
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        @if ($branch->is_active)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                Nonaktif
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('branches.show', $branch) }}"
                               class="h-7.5 px-2.5 inline-flex items-center justify-center rounded-lg border border-[#C2D8FC] bg-[#EAF2FF] text-[#062072] hover:bg-[#D5E5FD] text-xs font-semibold transition-colors shadow-2xs">
                                Detail
                            </a>

                            <a href="{{ route('branches.edit', $branch) }}"
                               data-action="edit-branch"
                               data-payload="{{ json_encode([
                                   'id' => $branch->id,
                                   'name' => $branch->name,
                                   'address' => $branch->address,
                                   'phone' => $branch->phone,
                                   'max_slot_capacity' => $branch->max_slot_capacity,
                                   'opening_time' => $branch->opening_time,
                                   'closing_time' => $branch->closing_time,
                                   'service_mode' => $branch->service_mode,
                                   'operating_days' => $branch->operating_days ?? [],
                                   'is_active' => (bool) $branch->is_active,
                               ]) }}"
                               class="h-7.5 px-2.5 inline-flex items-center justify-center rounded-lg bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-semibold transition-colors shadow-2xs">
                                Edit
                            </a>

                            <form method="POST" action="{{ route('branches.toggle-status', $branch) }}" class="inline m-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="h-7.5 px-2.5 inline-flex items-center justify-center rounded-lg text-xs font-medium border cursor-pointer transition-colors shadow-2xs {{ $branch->is_active ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }}">
                                    {{ $branch->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-10 sm:py-12 text-center text-[#667085]">
                        <svg class="w-9 h-9 mx-auto mb-2 text-[#D0D5DD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <p class="text-xs sm:text-sm font-semibold text-[#101828] mb-0.5">Belum ada cabang terdaftar</p>
                        <p class="text-[11px] text-[#667085] m-0">Gunakan tombol "Tambah Cabang" untuk mendaftarkan outlet baru.</p>
                    </td>
                </tr>
            @endforelse
        </x-tables.table>

        @if ($branches->hasPages())
            <div class="pt-2">
                {{ $branches->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Cabang Blur -->
    @include('owner.cabang.partials.modal-create')

    <!-- Modal Edit Cabang Blur -->
    @include('owner.cabang.partials.modal-edit')
</div>
@endsection
