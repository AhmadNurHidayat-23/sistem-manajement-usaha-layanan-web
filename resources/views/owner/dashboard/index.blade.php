@extends('layouts.app', ['title' => 'Dashboard Owner'])

@section('content')
<div class="space-y-6">
    <!-- Header Dashboard -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Dashboard Owner</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Rekapitulasi omzet, performa unit usaha, layanan terlaris, dan kinerja staf seluruh cabang.</p>
        </div>
    </div>

    <!-- Statistik utama -->
    @include('owner.dashboard.partials.statistik')

    <!-- Grafik tren penjualan -->
    @include('owner.dashboard.partials.grafik')

    <!-- Performa cabang dan layanan -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            @include('owner.dashboard.partials.cabang')
        </div>
        <div>
            @include('owner.dashboard.partials.layanan')
        </div>
    </div>

    <!-- Karyawan dan efektivitas promo -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('owner.dashboard.partials.karyawan')
        @include('owner.dashboard.partials.promo')
    </div>

    <!-- Transaksi kasir terbaru -->
    @include('owner.dashboard.partials.transaksi')
</div>
@endsection
