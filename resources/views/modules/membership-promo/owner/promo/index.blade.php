@extends('layouts.app', ['title' => 'Promo & Voucher | SIMAUL'])

@section('content')
<div class="space-y-6">
    <!-- Header with Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[#101828] tracking-tight m-0">Daftar Promo & Voucher Diskon</h1>
            <p class="text-xs text-[#6B7280] mt-1 m-0">Kelola kupon promosi, diskon kasir, kuota penggunaan, dan masa berlaku voucher.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" data-modal-open="modal-create-promo" class="h-9 px-3.5 bg-[#062072] hover:bg-[#04184F] text-white text-xs font-semibold rounded-lg transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Promo Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif

    <!-- Metric Summary Cards -->
    @include('modules.membership-promo.owner.promo.partials.metrics')

    <!-- Filter Bar -->
    @include('modules.membership-promo.owner.promo.partials.filters')

    <!-- Promo Table Card -->
    @include('modules.membership-promo.owner.promo.partials.table')

    <!-- Modal Tambah Promo Blur -->
    @include('modules.membership-promo.owner.promo.partials.modal-create')

    <!-- Modal Edit Promo Blur -->
    @include('modules.membership-promo.owner.promo.partials.modal-edit')

    <!-- Modal Konfirmasi Aksi (Approve, Reject, Toggle) -->
    @include('modules.membership-promo.owner.promo.partials.modal-actions')
</div>
@endsection
