@extends('layouts.app', ['title' => 'Dashboard Karyawan | SIMAUL'])

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Dashboard</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">
                Halo, <strong class="text-[#062072]">{{ $employee?->name ?? auth()->user()->name }}</strong>!
                @if ($employee)
                    &bull; Penugasan: <strong class="text-[#062072]">{{ $employee->branch->display_name }}</strong> &bull; Status: <span class="text-emerald-600 font-semibold">Aktif</span>
                @else
                    &bull; Selamat datang di sistem manajemen operasional.
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <x-buttons.btn variant="outline" href="{{ route('employees.my-shifts') }}" class="gap-1.5 text-xs">
                <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Jadwal Shift Saya
            </x-buttons.btn>
            <x-buttons.btn variant="primary" href="{{ route('employees.my-commissions') }}" class="gap-1.5 text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Estimasi & Rekap Komisi
            </x-buttons.btn>
        </div>
    </div>

    @if (! $employee)
        <x-cards.card class="p-8 text-center">
            <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-500 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-base font-bold text-[#101828] m-0">Profil Staf Belum Terhubung</h3>
            <p class="text-xs text-[#667085] mt-1 max-w-md mx-auto m-0">
                Akun Anda belum dikaitkan dengan profil karyawan aktif oleh Owner atau Manager cabang. Silakan hubungi pengelola cabang Anda.
            </p>
        </x-cards.card>
    @else
        <!-- Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Shift Today -->
            <x-cards.metric
                title="Shift Hari Ini"
                :value="$todayShift ? \Carbon\Carbon::parse($todayShift->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($todayShift->end_time)->format('H:i') : 'Libur'"
                :subtitle="$todayShift ? ucfirst($todayShift->status->value) . ' • ' . $todayShift->branch->display_name : 'Tidak ada jadwal aktif hari ini'"
                icon="clock"
                variant="primary"
            />

            <!-- Layanan Selesai -->
            <x-cards.metric
                title="Layanan Selesai"
                :value="$performance['completed_bookings'] . ' Tugas'"
                :subtitle="'Dari ' . $performance['total_bookings_assigned'] . ' total order ditugaskan'"
                icon="check"
                variant="primary"
            />

            <!-- Estimasi Komisi -->
            <x-cards.metric
                title="Estimasi Komisi (Pending)"
                :value="'Rp ' . number_format($performance['pending_commission'], 0, ',', '.')"
                :subtitle="'Cair: Rp ' . number_format($performance['paid_commission'], 0, ',', '.')"
                icon="money"
                variant="primary"
            />
        </div>


        <div class="space-y-6">
            <!-- Tugas Layanan Hari Ini -->
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Jadwal Shift Mendatang -->
                <x-cards.card title="Jadwal Shift Mendatang" subtitle="Agenda shift kerja Anda minggu ini">
                    @if ($upcomingShifts->isEmpty())
                        <div class="py-8 text-center">
                            <p class="text-xs text-[#667085] m-0">Belum ada jadwal shift mendatang yang direncanakan.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($upcomingShifts as $sh)
                                <div class="flex items-center justify-between p-3 rounded-lg border border-[#E4E7EC] bg-[#F5F8FC]/50 hover:bg-[#F5F8FC] transition-colors">
                                    <div>
                                        <span class="text-xs font-bold text-[#101828] block">
                                            {{ \Carbon\Carbon::parse($sh->shift_date)->translatedFormat('l, d F Y') }}
                                        </span>
                                        <span class="text-[11px] text-[#667085]">
                                            {{ $sh->branch->display_name }}
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-semibold text-[#062072] block">
                                            {{ \Carbon\Carbon::parse($sh->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sh->end_time)->format('H:i') }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700">
                                            {{ ucfirst($sh->status->value) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-cards.card>

                <!-- Riwayat Penugasan -->
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
            </div>
        </div>
    @endif
</div>
@endsection
