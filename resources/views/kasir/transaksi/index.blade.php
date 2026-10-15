@extends('layouts.app', ['title' => 'Riwayat Transaksi | SIMAUL'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h3 class="text-xl font-bold text-[#101828] m-0 flex items-center gap-2">
            <x-icons.icon name="receipt" class="w-6 h-6 text-[#062072]" />
            Riwayat Transaksi
        </h3>
        <p class="text-xs text-[#667085] mt-1 m-0">Daftar rekapan transaksi, pembayaran, dan pencetakan struk.</p>
    </div>

    <!-- Notifikasi flash -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif

    <!-- Filter transaksi -->
    @include('kasir.transaksi.partials.filters')

    <!-- Tabel riwayat transaksi -->
    @include('kasir.transaksi.partials.table')
</div>
@endsection
