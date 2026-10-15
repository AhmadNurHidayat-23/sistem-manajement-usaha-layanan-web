@extends('layouts.app', ['title' => 'Detail Bahan: ' . $item->name])

@section('content')
<div class="space-y-6">
    <!-- Header dan navigasi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h2 class="text-xl font-bold text-[#101828] m-0">{{ $item->name }}</h2>
                <x-badges.status :status="$item->status" />
            </div>
            <p class="text-xs text-[#667085] mt-1 m-0 flex items-center gap-2">
                @if ($item->code)
                    <span class="font-semibold text-[#062072]">{{ $item->code }}</span>
                    <span>&bull;</span>
                @endif
                <span>Cabang: <strong>{{ $item->branch?->name }}</strong></span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <x-buttons.btn variant="outline" href="{{ route('inventori.index') }}" class="gap-1.5 text-xs">
                Daftar Bahan
            </x-buttons.btn>
            <x-buttons.btn variant="primary" href="{{ route('inventori.edit', $item) }}" class="gap-1.5 text-xs">
                <x-icons.icon name="edit" class="w-4 h-4" />
                Edit Bahan
            </x-buttons.btn>
        </div>
    </div>

    <!-- Notifikasi flash -->
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

    <!-- Ringkasan metrik persediaan bahan -->
    @include('manager.inventori.partials.show-stats')

    <!-- Form catat stok masuk dan keluar -->
    @include('manager.inventori.partials.show-forms')

    <!-- Riwayat mutasi stok bahan -->
    @include('manager.inventori.partials.show-mutations')
</div>
@endsection
