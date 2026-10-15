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
        <!-- Kartu Metrik -->
        @include('karyawan.dashboard.partials.metrik')

        <div class="space-y-6">
            <!-- Tugas Layanan Hari Ini -->
            @include('karyawan.dashboard.partials.tugas-hari-ini')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Jadwal Shift Mendatang -->
                @include('karyawan.dashboard.partials.shift-mendatang')

                <!-- Riwayat Tugas -->
                @include('karyawan.dashboard.partials.riwayat-tugas')
            </div>
        </div>
    @endif
</div>
@endsection
