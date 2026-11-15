@extends('layouts.pelanggan', ['title' => 'Detail Janji Temu'])

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    {{-- Header & Navigasi --}}
    <div>
        <a href="{{ route('customer.booking.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#667085] hover:text-[#062072] transition-colors no-underline mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Janji Temu
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-[#101828] m-0 tracking-tight">Janji Temu</h2>
                <p class="text-xs text-[#667085] mt-1 m-0">Detail reservasi jadwal layanan Anda di SIMAUL</p>
            </div>
            @php
                $statusConfig = [
                    'pending'     => ['label' => 'Menunggu Konfirmasi', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
                    'confirmed'   => ['label' => 'Dikonfirmasi', 'bg' => 'bg-[#DCE8FF]', 'text' => 'text-[#062072]', 'border' => 'border-[#0B3A9E]/20', 'dot' => 'bg-[#0B3A9E]'],
                    'in_progress' => ['label' => 'Sedang Berlangsung', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                    'completed'   => ['label' => 'Selesai', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                    'cancelled'   => ['label' => 'Dibatalkan', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'dot' => 'bg-rose-400'],
                ];
                $sc = $statusConfig[$booking->status->value] ?? ['label' => $booking->status->value, 'bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'];
            @endphp
            <div class="shrink-0">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }} border shadow-2xs">
                    <span class="w-2 h-2 rounded-full {{ $sc['dot'] }} shrink-0"></span>
                    {{ $sc['label'] }}
                </span>
            </div>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @foreach(['success', 'error', 'info'] as $msg)
        @if(session($msg))
        <div class="{{ $msg === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($msg === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-blue-50 border-blue-200 text-blue-800') }} border px-4 py-3 rounded-xl text-sm flex items-center gap-2.5 shadow-2xs">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $msg === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
            </svg>
            <span>{{ session($msg) }}</span>
        </div>
        @endif
    @endforeach

    {{-- Kartu Detail Utama Booking --}}
    @include('pelanggan.booking.partials.detail-booking')
</div>

{{-- Dialog Konfirmasi Pembatalan --}}
@include('pelanggan.booking.partials.modal-pembatalan')
@endsection
