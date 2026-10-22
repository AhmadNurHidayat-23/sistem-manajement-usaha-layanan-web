@extends('layouts.pelanggan', ['title' => 'Detail Janji Temu #' . $booking->id])

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
                <h2 class="text-xl sm:text-2xl font-bold text-[#101828] m-0 tracking-tight">Janji Temu #{{ $booking->id }}</h2>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cancelForm = document.getElementById('form-cancel-booking');
    const confirmBtn = document.getElementById('btn-cancel-dialog-confirm');
    const backBtn = document.getElementById('btn-cancel-dialog-back');

    if (cancelForm && confirmBtn) {
        cancelForm.addEventListener('submit', function(e) {
            if (confirmBtn.disabled) {
                e.preventDefault();
                return;
            }
            confirmBtn.disabled = true;
            if (backBtn) backBtn.disabled = true;
            confirmBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Membatalkan...</span>
            `;
        });
    }
});
</script>
@endpush
