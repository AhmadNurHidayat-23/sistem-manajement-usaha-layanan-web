@extends('layouts.app', ['title' => 'Dashboard Kasir | SIMAUL'])

@section('content')
<div class="space-y-6">
    <!-- Aksi utama dan navigasi cabang -->
    @include('modules.kasir-pos.kasir.dashboard.partials.header')

    <!-- Notifikasi flash -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif

    <!-- Metrik ringkasan KPI operasional kasir -->
    @include('modules.kasir-pos.kasir.dashboard.partials.metrics')

    <!-- Antrean siap dibayar -->
    @include('modules.kasir-pos.kasir.dashboard.partials.ready-to-pay')
</div>

<!-- Modal registrasi member cepat -->
@include('modules.membership-promo.admin.member.partials.modal-register')
@endsection
