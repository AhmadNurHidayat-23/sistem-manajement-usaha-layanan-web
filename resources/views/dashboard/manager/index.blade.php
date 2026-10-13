@extends('layouts.app', ['title' => 'Dashboard Manajer | SIMAUL'])

@section('content')
<div class="space-y-6">
    <!-- Informasi cabang dan peringatan stok -->
    @include('dashboard.manager.partials.header')

    <!-- Statistik operasional harian -->
    @include('dashboard.manager.partials.statistik')

    <!-- Aktivitas layanan dan jadwal karyawan -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <div class="lg:col-span-2">
            @include('dashboard.manager.partials.aktivitas')
        </div>
        <div>
            @include('dashboard.manager.partials.jadwal')
        </div>
    </div>

    <!-- Transaksi terbaru cabang -->
    @include('dashboard.manager.partials.transaksi')
</div>
@endsection
