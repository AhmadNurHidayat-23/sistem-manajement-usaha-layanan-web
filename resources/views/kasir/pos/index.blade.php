@extends('layouts.app', ['title' => 'Katalog Layanan | SIMAUL'])

@section('content')
<div class="space-y-5" data-active-branch-id="{{ $activeBranch?->id }}" data-validate-promo-url="{{ route('promotions.validate') }}">
    <!-- Aksi utama dan navigasi cabang -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[#E4E7EC]">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span>Katalog Layanan</span>
            </h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Katalog layanan aktif cabang untuk melayani transaksi langsung (walk-in), pembayaran booking, dan pesanan drop-off.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if ($accessibleBranches->count() > 1)
                <!-- Pilihan cabang untuk Owner dan Manager -->
                <form method="GET" action="{{ route('pos.index') }}" class="flex items-center gap-2 m-0">
                    <label for="pos_branch_id" class="text-xs font-medium text-[#667085] whitespace-nowrap">Cabang:</label>
                    <select id="pos_branch_id" name="branch_id" class="branch-switch-select px-3 py-1.5 bg-white border border-[#E4E7EC] rounded-lg text-xs font-semibold text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        @foreach ($accessibleBranches as $b)
                            <option value="{{ $b->id }}" {{ $activeBranch?->id === $b->id ? 'selected' : '' }}>{{ $b->display_name }}</option>
                        @endforeach
                    </select>
                </form>
            @elseif ($activeBranch)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-[#E4E7EC] text-xs font-semibold text-[#101828]">
                    <x-icons.icon name="branch" class="w-4 h-4 text-[#062072]" />
                    {{ $activeBranch->display_name }}
                </span>
            @endif

            {{-- Tombol Pop-up Keranjang & Pembayaran --}}
            <button type="button" id="btn-open-cart-checkout" class="inline-flex items-center gap-2.5 px-4 py-2 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-bold rounded-lg shadow-sm transition-all cursor-pointer">
                <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <span>Keranjang & Pembayaran</span>
                <span id="pos-header-cart-count" class="px-2 py-0.5 rounded-full text-[11px] bg-white text-[#062072] font-black">0</span>
                <span id="pos-header-cart-total" class="font-extrabold border-l border-white/20 pl-2">Rp 0</span>
            </button>
        </div>
    </div>

    <!-- Notifikasi flash -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif

    <!-- Indikator Ringkas Status Shift Kasir -->
    @if ($activeShift)
        <div class="bg-white border border-[#E4E7EC] rounded-xl px-4 py-2.5 flex flex-wrap items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                    Shift Aktif
                </span>
                <span class="text-xs text-[#101828] font-medium">
                    Kasir: <strong class="font-semibold">{{ $activeShift->user?->name ?? 'Kasir' }}</strong> &bull; Sesi dibuka: <span class="text-[#667085]">{{ $activeShift->opened_at->format('H:i') }} WIB</span>
                </span>
            </div>
            <a href="{{ route('pos.shift.index', ['branch_id' => $activeBranch?->id]) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#062072] hover:text-[#0B3A9E] hover:underline">
                <x-icons.icon name="clock" class="w-3.5 h-3.5" />
                Kelola Shift Kasir
            </a>
        </div>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-200 text-amber-900">
                    Shift Belum Aktif
                </span>
                <p class="text-xs text-amber-800 m-0">
                    Buka sesi shift kasir terlebih dahulu untuk mencatat modal laci dan memproses transaksi penjualan.
                </p>
            </div>
            <a href="{{ route('pos.shift.index', ['branch_id' => $activeBranch?->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition-colors shadow-2xs">
                <x-icons.icon name="plus" class="w-3.5 h-3.5" />
                Buka Shift Sekarang
            </a>
        </div>
    @endif

    {{-- Tata Letak Katalog Layanan: Luas, Responsif & Mengambil Full Width --}}
    <div class="space-y-5">
        <!-- Pilihan sumber transaksi (Walk-in, Booking, Drop-off) -->
        @include('kasir.pos.partials.source-selector')

        <!-- Grid Katalog Layanan -->
        @include('kasir.pos.partials.catalog')
    </div>
</div>

<!-- Modal operasional pendaftaran member cepat -->
@include('kasir.member.partials.modal-register')

<!-- Modal terpadu keranjang & pembayaran kasir -->
@include('kasir.pembayaran.partials.modal-pembayaran')
@endsection
