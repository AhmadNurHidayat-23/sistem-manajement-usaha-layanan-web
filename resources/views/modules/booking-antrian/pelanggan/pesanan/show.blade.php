@extends('layouts.pelanggan', ['title' => 'Detail Pesanan'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-[#667085]">
        <a href="{{ route('customer.pesanan.index') }}" class="hover:text-[#062072] no-underline text-[#667085]">Pesanan Saya</a>
        <svg class="w-3 h-3 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-[#101828] font-medium">Detail Pesanan</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-[#101828] m-0">Detail Pesanan</h1>
            <p class="text-xs text-[#667085] mt-1 m-0">Informasi lengkap rincian dan progres pengerjaan pesanan Anda</p>
        </div>
    </div>

    <!-- Flash Notifications -->
    @foreach(['success', 'error', 'info'] as $msg)
        @if(session($msg))
        <div class="{{ $msg === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : ($msg === 'error' ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-blue-50 border-blue-200 text-blue-700') }} border px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session($msg) }}
        </div>
        @endif
    @endforeach

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Informasi Pesanan, Layanan Tambahan, Pembayaran -->
        <div class="lg:col-span-2 space-y-5">
            @include('modules.booking-antrian.pelanggan.pesanan.partials.info-pesanan')
            @include('modules.booking-antrian.pelanggan.pesanan.partials.layanan-tambahan')
            @include('modules.booking-antrian.pelanggan.pesanan.partials.info-pembayaran')
        </div>

        <!-- Kolom Kanan: Status Progres & Tombol Aksi -->
        <div class="space-y-5">
            @include('modules.booking-antrian.pelanggan.pesanan.partials.progres-status')
            @include('modules.booking-antrian.pelanggan.pesanan.partials.actions')
        </div>
    </div>
</div>
@endsection
