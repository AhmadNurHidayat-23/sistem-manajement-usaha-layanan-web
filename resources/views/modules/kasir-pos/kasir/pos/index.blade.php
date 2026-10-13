@extends('layouts.app', ['title' => 'Terminal POS | SIMAUL'])

@section('content')
<div class="space-y-5" data-active-branch-id="{{ $activeBranch?->id }}" data-validate-promo-url="{{ route('promotions.validate') }}">
    <!-- Aksi utama dan status shift -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0 flex items-center gap-2">
                <x-icons.icon name="cart" class="w-6 h-6 text-[#062072]" />
                Terminal POS
            </h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Melayani transaksi langsung, pembayaran booking, dan pesanan drop-off.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
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
        </div>
    </div>

    <!-- Notifikasi flash -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm">
            <p class="font-semibold mb-1">Terjadi kesalahan validasi:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
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

    <!-- Tata letak terminal POS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Pilihan sumber transaksi dan katalog -->
        <div class="lg:col-span-7 space-y-4">
            @include('modules.kasir-pos.kasir.pos.partials.source-selector')
            @include('modules.kasir-pos.kasir.pos.partials.catalog')
        </div>

        <!-- Keranjang dan pembayaran -->
        <div class="lg:col-span-5">
            @include('modules.kasir-pos.kasir.pos.partials.cart')
        </div>
    </div>
</div>

<!-- Modal operasional pendaftaran member cepat -->
@include('modules.membership-promo.admin.member.partials.modal-register')
@endsection
