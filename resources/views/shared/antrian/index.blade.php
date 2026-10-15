@extends('layouts.app', ['title' => ($isHistory ? 'Riwayat Tugas' : 'Antrean & Tugas') . ' | SIMAUL'])

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    @include('shared.antrian.partials.header')

    {{-- Flash Alert --}}
    @if(session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif

    {{-- Filter Nav (Semua, Menunggu, Dikerjakan, Selesai) --}}
    @if(! $isHistory)
        @include('shared.antrian.partials.filter-tabs')
    @endif

    {{-- Daftar Tugas Terpadu --}}
    @include('shared.antrian.partials.task-list')
</div>
@endsection
