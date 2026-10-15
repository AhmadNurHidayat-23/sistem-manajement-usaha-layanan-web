@extends('layouts.app', ['title' => 'Profil Karyawan — ' . $employee->name])

@section('content')
<div class="space-y-6">
    <!-- Header with Back & Edit Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('employees.index') }}" class="p-2 rounded-lg border border-[#E4E7EC] bg-white text-slate-600 hover:bg-[#F5F8FC] transition-colors" title="Kembali ke Daftar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-[#101828] m-0">{{ $employee->name }}</h2>
                    @if ($employee->status === 'active')
                        <x-badges.badge variant="success">Aktif</x-badges.badge>
                    @else
                        <x-badges.badge variant="neutral">Nonaktif</x-badges.badge>
                    @endif
                </div>
                <p class="text-xs text-[#667085] mt-0.5 m-0">
                    Usaha: <span class="font-semibold text-[#101828]">{{ $employee->branch->display_name }}</span>
                    &bull; Telp: <span class="font-medium text-[#101828]">{{ $employee->phone }}</span>
                    @if ($employee->user)
                        &bull; Akun: <span class="text-[#062072] font-medium">{{ $employee->user->email }}</span>
                    @endif
                </p>
            </div>
        </div>

        @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('employees.toggle-status', $employee) }}" class="inline m-0">
                    @csrf
                    <x-buttons.btn variant="outline" type="submit" class="text-xs">
                        {{ $employee->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                    </x-buttons.btn>
                </form>
                <x-buttons.btn variant="primary" href="{{ route('employees.edit', $employee) }}" class="gap-1.5 text-xs">
                    <x-icons.icon name="edit" class="w-4 h-4" />
                    Edit Profil
                </x-buttons.btn>
            </div>
        @endif
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif

    <!-- Performance KPI Summary Cards -->
    @include('manager.karyawan.partials.kpi')

    <!-- Skills & Specializations -->
    @include('manager.karyawan.partials.skills')

    <!-- Shift Management Section -->
    @include('manager.karyawan.partials.shifts')

    <!-- Riwayat komisi -->
    @include('manager.karyawan.partials.commissions')
</div>

<!-- Modals -->
@include('manager.karyawan.partials.modal-shift')
@include('manager.karyawan.partials.modal-commission')
@endsection
