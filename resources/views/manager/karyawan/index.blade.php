@extends('layouts.app', ['title' => 'Manajemen Karyawan'])

@section('content')
<div class="space-y-6">
    <!-- Header dan tombol aksi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Daftar Karyawan</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Kelola data staf, keahlian teknisi, jadwal shift, dan skema komisi.</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <x-buttons.btn variant="primary" href="{{ route('employees.create') }}" data-modal-open="modal-create-employee" class="gap-1.5 text-xs shadow-2xs w-full sm:w-auto justify-center">
                <x-icons.icon name="plus" class="w-4 h-4" />
                Tambah Karyawan
            </x-buttons.btn>
        </div>
    </div>

    <!-- Notifikasi flash -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif

    <!-- Ringkasan metrik karyawan -->
    @include('manager.karyawan.partials.metrics')

    <!-- Filter data karyawan -->
    @include('manager.karyawan.partials.filters')

    <!-- Tabel daftar karyawan -->
    @include('manager.karyawan.partials.table')

    <!-- Modal Tambah Karyawan Blur -->
    @include('manager.karyawan.partials.modal-create')

    <!-- Modal Edit Karyawan Blur -->
    @include('manager.karyawan.partials.modal-edit')
</div>
@endsection
