@extends('layouts.app', ['title' => 'Jadwal Shift | SIMAUL'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[#101828] tracking-tight m-0">
                {{ ($isSelfView ?? false) ? 'Jadwal Shift' : 'Jadwal Shift Karyawan' }}
            </h1>
            <p class="text-xs text-[#6B7280] mt-1 m-0">
                {{ ($isSelfView ?? false) ? 'Pantau jadwal shift kerja operasional Anda dan status kehadiran.' : 'Atur penugasan shift kerja harian staf, jam operasional cabang, dan kehadiran.' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if (! ($isSelfView ?? false))
                <x-buttons.btn variant="secondary" href="{{ route('employees.index') }}" class="gap-1.5 text-xs">
                    <x-icons.icon name="user" class="w-4 h-4 text-[#062072]" />
                    Daftar Karyawan
                </x-buttons.btn>
                <button type="button" data-modal-open="modal-create-shift" class="px-3.5 py-1.5 bg-[#062072] text-white rounded-lg text-xs font-semibold hover:bg-[#04184F] transition-colors inline-flex items-center gap-1.5 cursor-pointer shadow-2xs h-[34px]">
                    <x-icons.icon name="plus" class="w-4 h-4" />
                    Tambah Jadwal Shift
                </button>
            @endif
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif

    <!-- Filter & Search Bar -->
    @include('manager.shift.partials.filters')

    <!-- Shifts Table Card -->
    @include('manager.shift.partials.table')
</div>

<!-- Modal Tambah Shift Baru -->
@if (! ($isSelfView ?? false))
    @include('manager.shift.partials.modal')
@endif
@endsection
