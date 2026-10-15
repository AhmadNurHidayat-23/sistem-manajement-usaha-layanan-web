@extends('layouts.app', ['title' => 'Dashboard Manajer | SIMAUL'])

@section('content')
<div class="space-y-6">
    <!-- Informasi cabang dan peringatan stok -->
    @include('manager.dashboard.partials.header')

    <!-- Statistik operasional harian -->
    @include('manager.dashboard.partials.statistik')

    <!-- Aktivitas layanan dan jadwal karyawan -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <div class="lg:col-span-2">
            @include('manager.dashboard.partials.aktivitas')
        </div>
        <div>
            @include('manager.dashboard.partials.jadwal')
        </div>
    </div>

    <!-- Transaksi terbaru cabang -->
    @include('manager.dashboard.partials.transaksi')
</div>
@endsection
