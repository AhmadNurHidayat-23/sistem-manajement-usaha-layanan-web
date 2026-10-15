@extends('layouts.app', ['title' => ($isSelfView ?? false) ? 'Komisi Saya | SIMAUL' : 'Rekap & Estimasi Komisi Karyawan | SIMAUL'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">
                {{ ($isSelfView ?? false) ? 'Komisi Saya' : 'Rekap & Estimasi Komisi Karyawan' }}
            </h2>
            <p class="text-xs text-[#667085] mt-1 m-0">
                {{ ($isSelfView ?? false) ? 'Pantau estimasi komisi layanan Anda, status persetujuan, dan riwayat pencairan.' : 'Pantau estimasi komisi staf, skema komisi per layanan, dan persetujuan pencairan komisi.' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if (! ($isSelfView ?? false))
                <button type="button" onclick="document.getElementById('modal-create-commission').classList.remove('hidden')" class="px-3.5 py-2 bg-[#062072] text-white rounded-lg text-xs font-semibold hover:bg-[#0B3A9E] transition-colors inline-flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <x-icons.icon name="plus" class="w-4 h-4" />
                    Catat Komisi Baru
                </button>
            @endif
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif

    <!-- KPI Metric Overview Cards -->
    @include('manager.komisi.partials.metrics')

    <!-- Filter & Search Bar -->
    @include('manager.komisi.partials.filters')

    <!-- Commissions Table Card -->
    @include('manager.komisi.partials.table')
</div>

<!-- Modal Catat Komisi Baru -->
@if (! ($isSelfView ?? false))
    @include('manager.komisi.partials.modal')
@endif
@endsection
