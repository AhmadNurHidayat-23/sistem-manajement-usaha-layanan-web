@extends('layouts.app', ['title' => __('dashboard.cashier.title') . ' | SIMAUL'])

@section('content')
<div class="space-y-6">
    <!-- Aksi utama dan navigasi cabang -->
    @include('kasir.dashboard.partials.header')

    <!-- Notifikasi flash -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif

    <!-- Metrik ringkasan KPI operasional kasir -->
    @include('kasir.dashboard.partials.metrics')

    <!-- Antrean siap dibayar -->
    @include('kasir.dashboard.partials.ready-to-pay')
</div>

<!-- Modal registrasi member cepat -->
@include('kasir.member.partials.modal-register')

<!-- Modal pembayaran langsung kasir -->
@include('kasir.pembayaran.partials.modal-pembayaran')

<!-- Modal input berat laundry kiloan -->
@include('kasir.dashboard.partials.modal-input-berat')

<!-- Modal terima pesanan drop-off -->
@include('kasir.dashboard.partials.modal-terima-pesanan')
@endsection
