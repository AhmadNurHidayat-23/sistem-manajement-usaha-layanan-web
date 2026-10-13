@extends('layouts.app', ['title' => ($isHistory ? 'Riwayat Tugas' : 'Antrean & Tugas') . ' | SIMAUL'])

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    @include('modules.booking-antrian.karyawan.partials.header')

    {{-- Flash Alert --}}
    @if(session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif

    {{-- Filter Nav (Semua, Menunggu, Dikerjakan, Selesai) --}}
    @if(! $isHistory)
        @include('modules.booking-antrian.karyawan.partials.filter-tabs')
    @endif

    {{-- Daftar Tugas Terpadu --}}
    @include('modules.booking-antrian.karyawan.partials.task-list')
</div>
@endsection
