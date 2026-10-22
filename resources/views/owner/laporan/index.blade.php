@php
    $isManager = auth()->user()?->role?->name === 'manager';
    $pageTitle = $isManager ? 'Laporan Keuangan Cabang | SIMAUL' : 'Laporan Keuangan | SIMAUL';
    $headerTitle = $isManager ? 'Laporan Keuangan Cabang' : 'Laporan Kinerja & Keuangan Bisnis';
    $headerSubtitle = $isManager
        ? 'Ringkasan omzet, transaksi, dan kinerja keuangan cabang.'
        : 'Analisis omzet, tren transaksi, metode pembayaran, performa layanan, serta rekapitulasi komisi karyawan.';

    $isCustomPeriod = ($report['period'] ?? null) === 'custom';
    $exportParams = array_filter([
        'period' => $report['period'] ?? null,
        'start_date' => $isCustomPeriod ? ($report['start_date'] ?? null) : null,
        'end_date' => $isCustomPeriod ? ($report['end_date'] ?? null) : null,
        'branch_id' => $report['branch_id'] ?? null,
    ], fn ($v) => $v !== null && $v !== '');
@endphp

@extends('layouts.app', ['title' => $pageTitle])

@section('content')
<div class="space-y-6">
    <!-- Header with Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[#101828] tracking-tight m-0">
                {{ $headerTitle }}
            </h1>
            <p class="text-xs text-[#6B7280] mt-1 m-0">
                {{ $headerSubtitle }}
            </p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <!-- Export PDF Button -->
            <a href="{{ route('reports.export.pdf', $exportParams) }}"
               class="flex-1 sm:flex-initial h-9 px-3.5 inline-flex items-center justify-center text-xs font-semibold rounded-lg border border-[#C2D8FC] bg-[#EAF2FF] text-[#062072] hover:bg-[#D5E5FD] transition-colors gap-1.5 shadow-2xs">
                <x-icons.icon name="printer" class="w-4 h-4 text-[#062072]" />
                <span>Ekspor PDF</span>
            </a>

            <!-- Export Excel (.xlsx) Button -->
            <a href="{{ route('reports.export.excel', $exportParams) }}"
               class="flex-1 sm:flex-initial h-9 px-3.5 inline-flex items-center justify-center text-xs font-semibold rounded-lg bg-[#062072] hover:bg-[#0B3A9E] text-white transition-colors gap-1.5 shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Ekspor Excel</span>
            </a>
        </div>
    </div>

    <!-- Filter periode & cabang -->
    @include('owner.laporan.partials.filters')

    <!-- 1. Ringkasan Omzet & Metrik Finansial -->
    @include('owner.laporan.partials.summary')

    <!-- 2. Grafik Tren Transaksi & Omzet Sederhana -->
    @include('owner.laporan.partials.charts')

    <!-- 3. Laporan Gabungan Seluruh Cabang (Owner Only) -->
    @include('owner.laporan.partials.branches')

    <!-- 4. Pendapatan per Layanan & Metode Pembayaran -->
    @include('owner.laporan.partials.services-payments')

    <!-- 5. Kinerja Penjualan Karyawan & Rekap Komisi -->
    @include('owner.laporan.partials.employees-commissions')

    <!-- 6. Penggunaan Promo & Voucher Diskon -->
    @include('owner.laporan.partials.promotions')
</div>
@endsection
