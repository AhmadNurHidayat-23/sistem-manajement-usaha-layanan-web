@extends('layouts.app', ['title' => 'Keanggotaan & Loyalitas Saya'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Keanggotaan & Poin Loyalitas Saya</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Pantau kartu member digital, akumulasi poin, riwayat transaksi, dan tukar reward.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-buttons.btn variant="outline" href="{{ route('promotions.public') }}" class="gap-1.5 text-xs">
                <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                </svg>
                Lihat Voucher Promo
            </x-buttons.btn>
            <x-buttons.btn variant="outline" href="{{ route('members.packages') }}" class="gap-1.5 text-xs">
                <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                Tingkat Keanggotaan
            </x-buttons.btn>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif
    @if ($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg">
            <p class="font-bold mb-1">Gagal memproses permintaan:</p>
            <ul class="list-disc pl-5 m-0 space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! $membership)
        <!-- Customer Not Yet a Member CTA Card -->
        @include('modules.membership-promo.customer.member.partials.my-cta')
    @else
        <!-- Member Profile & Points Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Digital Member Card -->
            @include('modules.membership-promo.customer.member.partials.my-card')

            <!-- Right: Points Balance, Fast Redeem, & Stats -->
            @include('modules.membership-promo.customer.member.partials.my-rewards')
        </div>

        <!-- Riwayat poin -->
        @include('modules.membership-promo.customer.member.partials.my-history')
    @endif
</div>
@endsection
