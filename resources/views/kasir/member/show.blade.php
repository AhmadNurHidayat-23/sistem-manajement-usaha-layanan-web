@extends('layouts.app', ['title' => 'Kartu Member — ' . $membership->member_code])

@section('content')
<div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('members.index') }}" class="p-2 rounded-lg border border-[#E4E7EC] bg-white text-slate-600 hover:bg-[#F5F8FC] transition-colors" title="Kembali ke Daftar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-[#101828] m-0">{{ $membership->user->name }}</h2>
                    @php
                        $statusVariants = [
                            'active' => ['label' => 'Aktif', 'badge' => 'success'],
                            'inactive' => ['label' => 'Nonaktif', 'badge' => 'neutral'],
                            'suspended' => ['label' => 'Suspended', 'badge' => 'danger'],
                        ];
                        $st = $statusVariants[$membership->status->value] ?? ['label' => $membership->status->value, 'badge' => 'neutral'];
                    @endphp
                    <x-badges.badge :variant="$st['badge']">{{ $st['label'] }}</x-badges.badge>
                </div>
                <p class="text-xs text-[#667085] mt-0.5 m-0">
                    Kode Member: <span class="font-semibold text-[#062072]">{{ $membership->member_code }}</span>
                    &bull; Bergabung: {{ \Carbon\Carbon::parse($membership->joined_at)->translatedFormat('d F Y') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="document.getElementById('modal-redeem').classList.remove('hidden')" class="px-3 py-2 bg-[#062072] text-white rounded-lg text-xs font-semibold hover:bg-[#0B3A9E] transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Tukar Poin Reward
            </button>
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
        <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-700">
            <ul class="list-disc list-inside space-y-1 m-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Top Grid: Digital Loyalty Card & Quick Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @include('kasir.member.partials.show-card')
        @include('kasir.member.partials.show-status')
    </div>

    <!-- Loyalty Point Transactions History Card -->
    @include('kasir.member.partials.show-history')
</div>

<!-- Modal Tukar Poin -->
@include('kasir.member.partials.modal-redeem')
@endsection
