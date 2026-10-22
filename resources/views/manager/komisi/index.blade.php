@extends('layouts.app', ['title' => ($isSelfView ?? false) ? 'Komisi Saya | SIMAUL' : 'Pengelolaan Komisi Karyawan | SIMAUL'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[#101828] tracking-tight m-0">
                {{ ($isSelfView ?? false) ? 'Komisi Saya' : 'Pengelolaan Komisi Karyawan' }}
            </h1>
            <p class="text-xs text-[#667085] mt-1 m-0">
                {{ ($isSelfView ?? false) ? 'Pantau estimasi komisi layanan Anda, status persetujuan, dan riwayat pencairan.' : 'Pantau estimasi komisi staf, kelola skema komisi per layanan, dan setujui pencairan komisi.' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if (! ($isSelfView ?? false))
                <button type="button" data-modal-open="modal-create-commission" class="px-3.5 py-2 bg-[#062072] text-white rounded-lg text-xs font-semibold hover:bg-[#04184F] transition-colors inline-flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <x-icons.icon name="plus" class="w-4 h-4" />
                    <span>Catat Komisi Manual</span>
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

    {{-- Navigasi Tab (Opsi B: Riwayat Komisi vs Skema Komisi Layanan) --}}
    @if (! ($isSelfView ?? false))
        <div class="flex items-center gap-2 border-b border-[#E4E7EC] pb-3 overflow-x-auto">
            @php
                $tab = $activeTab ?? 'history';
            @endphp
            <a href="{{ route('employees.commissions.index', array_merge(request()->except(['page', 'tab']), ['tab' => 'history'])) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all shrink-0 {{ $tab !== 'schemes' ? 'bg-[#062072] text-white shadow-2xs' : 'bg-white border border-[#E4E7EC] text-[#475467] hover:bg-[#F5F8FC] hover:text-[#101828]' }}">
                <x-icons.icon name="money" class="w-3.5 h-3.5" />
                <span>Riwayat Komisi</span>
                <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $tab !== 'schemes' ? 'bg-white/20 text-white' : 'bg-[#F2F4F7] text-[#475467]' }}">
                    {{ $commissions->total() }}
                </span>
            </a>
            <a href="{{ route('employees.commissions.index', ['tab' => 'schemes']) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all shrink-0 {{ $tab === 'schemes' ? 'bg-[#062072] text-white shadow-2xs' : 'bg-white border border-[#E4E7EC] text-[#475467] hover:bg-[#F5F8FC] hover:text-[#101828]' }}">
                <x-icons.icon name="service" class="w-3.5 h-3.5" />
                <span>Skema Komisi Layanan</span>
                <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $tab === 'schemes' ? 'bg-white/20 text-white' : 'bg-[#F2F4F7] text-[#475467]' }}">
                    {{ $services->count() }}
                </span>
            </a>
        </div>
    @endif

    {{-- Konten Tab --}}
    @if (($activeTab ?? 'history') === 'schemes' && ! ($isSelfView ?? false))
        <!-- Tab 2: Skema Komisi Layanan -->
        @include('manager.komisi.partials.schemes')
    @else
        <!-- Tab 1: Riwayat Komisi -->
        <!-- KPI Metric Overview Cards -->
        @include('manager.komisi.partials.metrics')

        <!-- Filter & Search Bar -->
        @include('manager.komisi.partials.filters')

        <!-- Commissions Table / Card List -->
        @include('manager.komisi.partials.table')
    @endif
</div>

<!-- Modal Catat Komisi Manual -->
@if (! ($isSelfView ?? false))
    @include('manager.komisi.partials.modal')
@endif
@endsection

