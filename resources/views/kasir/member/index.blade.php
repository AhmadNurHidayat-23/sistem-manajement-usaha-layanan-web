@php
    $isCashier = (auth()->user()?->role?->name === 'cashier');
    $pageTitle = $isCashier ? 'Member | SIMAUL' : 'Manajemen Member & Loyalitas';
@endphp
@extends('layouts.app', ['title' => $pageTitle])

@section('content')
<div class="space-y-6">
    <!-- Header dan tombol aksi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">{{ $isCashier ? 'Member' : 'Daftar Member & Poin Loyalitas' }}</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Kelola keanggotaan pelanggan, pantau saldo poin loyalitas, dan penukaran reward.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-buttons.btn variant="outline" href="{{ route('members.packages') }}" class="gap-1.5 text-xs">
                <x-icons.icon name="ticket" class="w-4 h-4 text-[#062072]" />
                Paket Berlangganan
            </x-buttons.btn>
            <x-buttons.btn variant="primary" href="{{ route('members.create') }}" data-modal-open="modal-register-member" class="gap-1.5 text-xs">
                <x-icons.icon name="plus" class="w-4 h-4" />
                Daftarkan Member Baru
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

    <!-- Ringkasan metrik member -->
    @include('kasir.member.partials.metrics')

    <!-- Filter data member -->
    @include('kasir.member.partials.filters')

    <!-- Tabel daftar member -->
    @include('kasir.member.partials.table')

    @include('kasir.member.partials.modal-register')
</div>
@endsection
