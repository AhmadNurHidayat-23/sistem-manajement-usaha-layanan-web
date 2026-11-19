@extends('layouts.app', ['title' => 'Detail Bahan: ' . $item->name])

@section('content')
<div class="space-y-6">
    <!-- Navigasi & Breadcrumb -->
    <div>
        <nav class="flex items-center gap-2 text-xs text-[#667085] dark:text-[#94A3B8] mb-2" aria-label="Breadcrumb">
            <a href="{{ route('inventori.index') }}" class="hover:text-[#062072] dark:hover:text-[#93C5FD] transition-colors">Inventori</a>
            <span class="text-[#D0D5DD] dark:text-[#334155]">/</span>
            <span class="font-semibold text-[#101828] dark:text-white">Detail Inventori</span>
        </nav>

        <!-- Header dan Tombol Aksi -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold text-[#101828] dark:text-white m-0 tracking-tight">{{ $item->name }}</h1>
                    <x-badges.status :status="$item->status" />
                </div>
                <p class="text-xs text-[#667085] dark:text-[#94A3B8] mt-1.5 m-0 flex items-center gap-2 flex-wrap">
                    @if ($item->code)
                        <span class="font-semibold text-[#062072] dark:text-[#93C5FD] bg-blue-50 dark:bg-blue-950/50 px-2 py-0.5 rounded-md border border-blue-200 dark:border-blue-800">
                            {{ $item->code }}
                        </span>
                        <span class="text-[#D0D5DD] dark:text-[#334155]">&bull;</span>
                    @endif
                    <span>Cabang: <strong class="text-[#101828] dark:text-white">{{ $item->branch?->name ?? '-' }}</strong></span>
                </p>
            </div>

            <!-- Tombol Aksi di Header -->
            <div class="flex items-center gap-2 flex-wrap">
                <x-buttons.btn variant="outline" href="{{ route('inventori.index') }}" class="gap-1.5 text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Daftar Bahan
                </x-buttons.btn>

                <x-buttons.btn variant="outline" href="{{ route('inventori.edit', $item) }}" class="gap-1.5 text-xs">
                    <x-icons.icon name="edit" class="w-4 h-4" />
                    Edit Bahan
                </x-buttons.btn>

                <button
                    type="button"
                    onclick="openModalTambahStok()"
                    class="h-9 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs"
                    title="Tambah Stok Bahan"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Stok</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Notifikasi Flash Message -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
            <p class="font-bold mb-1">Gagal memproses transaksi stok:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 4 Card Ringkasan Metrik Persediaan Bahan -->
    @include('manager.inventori.partials.show-stats')

    <!-- Tabel Riwayat Stok Bahan (Langsung di Bawah Metrik) -->
    @include('manager.inventori.partials.show-mutations')

    <!-- Modal Tambah Stok Pop-up -->
    @include('manager.inventori.partials.modal-tambah-stok')
</div>
@endsection
