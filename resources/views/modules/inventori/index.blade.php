@extends('layouts.app', ['title' => 'Inventori Bahan | SIMAUL'])

@section('content')
<div class="space-y-6">
    <!-- Header with Action Shortcuts -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl sm:text-2xl font-bold text-[#101828] tracking-tight m-0">Inventori & Stok Bahan</h1>
                @if ($managerBranch)
                    <x-badges.badge variant="primary" class="text-xs">
                        {{ $managerBranch->display_name }}
                    </x-badges.badge>
                @endif
            </div>
            <p class="text-xs text-[#6B7280] mt-1 m-0">Kelola stok bahan, pantau ketersediaan, serta pencatatan stok masuk dan keluar.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-buttons.btn variant="secondary" href="{{ route('inventori.history') }}" class="gap-1.5 text-xs">
                <x-icons.icon name="history" class="w-4 h-4 text-[#062072]" />
                Riwayat Mutasi Stok
            </x-buttons.btn>
            <x-buttons.btn variant="primary" href="{{ route('inventori.create') }}" data-modal-open="modal-create-inventory" class="gap-1.5 text-xs">
                <x-icons.icon name="plus" class="w-4 h-4" />
                Tambah Bahan
            </x-buttons.btn>
        </div>
    </div>

    <!-- 1. Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-cards.metric
            title="Total Jenis Bahan"
            :value="number_format($metrics['total_items']) . ' Item'"
            subtitle="Katalog bahan terdaftar"
            icon="inventory"
            variant="primary"
        />

        <x-cards.metric
            title="Stok Tersedia"
            :value="number_format($metrics['available']) . ' Item'"
            subtitle="Stok aman di atas minimum"
            icon="box"
            variant="emerald"
        />

        <x-cards.metric
            title="Stok Menipis"
            :value="number_format($metrics['low_stock']) . ' Item'"
            subtitle="Perlu pengadaan ulang segera"
            icon="alert-triangle"
            variant="amber"
        />

        <x-cards.metric
            title="Stok Habis"
            :value="number_format($metrics['out_of_stock']) . ' Item'"
            subtitle="Stok kosong tidak dapat dipakai"
            icon="alert-triangle"
            variant="amber"
        />
    </div>

    <!-- 2. Filter & Pencarian -->
    @include('modules.inventori.partials.filters')

    <!-- 3. Daftar Bahan Table -->
    @include('modules.inventori.partials.table')

    <!-- Modal Tambah Bahan Blur -->
    @include('modules.inventori.partials.modal-create')

    <!-- Modal Edit Bahan Blur -->
    @include('modules.inventori.partials.modal-edit')
</div>
@endsection
