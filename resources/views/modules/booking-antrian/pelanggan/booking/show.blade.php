@extends('layouts.pelanggan', ['title' => 'Detail Appointment #' . $booking->id])

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    {{-- Header --}}
    <div>
        <a href="{{ route('customer.booking.index') }}" class="inline-flex items-center gap-1 text-xs text-[#667085] hover:text-[#062072] no-underline mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Appointment
        </a>
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-[#101828] m-0">Booking #{{ $booking->id }}</h2>
            @php
                $statusConfig = [
                    'pending'     => ['label' => 'Menunggu', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
                    'confirmed'   => ['label' => 'Dikonfirmasi', 'bg' => 'bg-[#DCE8FF]', 'text' => 'text-[#062072]', 'border' => 'border-[#0B3A9E]/20', 'dot' => 'bg-[#0B3A9E]'],
                    'in_progress' => ['label' => 'Sedang Berlangsung', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                    'completed'   => ['label' => 'Selesai', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                    'cancelled'   => ['label' => 'Dibatalkan', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'dot' => 'bg-rose-400'],
                ];
                $sc = $statusConfig[$booking->status->value] ?? ['label' => $booking->status->value, 'bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'];
            @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }} border">
                <span class="w-2 h-2 rounded-full {{ $sc['dot'] }}"></span>
                {{ $sc['label'] }}
            </span>
        </div>
    </div>

    {{-- Flash --}}
    @foreach(['success', 'error'] as $msg)
        @if(session($msg))
        <div class="{{ $msg === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700' }} border px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $msg === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
            </svg>
            {{ session($msg) }}
        </div>
        @endif
    @endforeach

    {{-- Detail Card --}}
    <div class="bg-white border border-[#E4E7EC] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 space-y-5">
            {{-- Jadwal --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-[10px] text-[#667085] uppercase tracking-wider block mb-1">Tanggal</span>
                    <span class="text-sm font-semibold text-[#101828]">{{ $booking->booking_date->format('l, d M Y') }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-[#667085] uppercase tracking-wider block mb-1">Waktu</span>
                    <span class="text-sm font-semibold text-[#101828]">{{ $booking->booking_time }}</span>
                </div>
            </div>

            <hr class="border-[#E4E7EC] m-0">

            {{-- Layanan --}}
            <div>
                <span class="text-[10px] text-[#667085] uppercase tracking-wider block mb-1">Layanan Utama</span>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-[#101828]">{{ $booking->service?->name ?? '-' }}</span>
                    @if($booking->service)
                        <span class="text-sm font-bold text-[#062072]">Rp {{ number_format($booking->service->price, 0, ',', '.') }}</span>
                    @endif
                </div>
                @if($booking->service?->category)
                    <span class="text-xs text-[#667085]">Kategori: {{ $booking->service->category->name }}</span>
                @endif
            </div>

            {{-- Extra Services --}}
            @if($booking->details && $booking->details->isNotEmpty())
            <div>
                <span class="text-[10px] text-[#667085] uppercase tracking-wider block mb-2">Layanan Tambahan</span>
                <div class="space-y-2">
                    @foreach($booking->details as $detail)
                        <div class="flex items-center justify-between p-2 rounded-lg bg-[#F5F8FC]">
                            <span class="text-sm text-[#101828]">{{ $detail->service?->name ?? '-' }}</span>
                            <span class="text-sm font-medium text-[#062072]">Rp {{ number_format($detail->price, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <hr class="border-[#E4E7EC] m-0">

            {{-- Cabang --}}
            <div>
                <span class="text-[10px] text-[#667085] uppercase tracking-wider block mb-1">Cabang</span>
                <span class="text-sm font-semibold text-[#101828]">{{ $booking->branch?->display_name ?? $booking->branch?->name ?? '-' }}</span>
                @if($booking->branch)
                    <p class="text-xs text-[#667085] m-0 mt-1">{{ $booking->branch->address }}</p>
                @endif
            </div>

            {{-- Petugas --}}
            @if($booking->employee)
            <div>
                <span class="text-[10px] text-[#667085] uppercase tracking-wider block mb-1">Petugas</span>
                <span class="text-sm font-semibold text-[#101828]">{{ $booking->employee->name }}</span>
            </div>
            @endif

            {{-- Catatan --}}
            @if($booking->notes)
            <div>
                <span class="text-[10px] text-[#667085] uppercase tracking-wider block mb-1">Catatan</span>
                <p class="text-sm text-[#101828] m-0">{{ $booking->notes }}</p>
            </div>
            @endif

            <hr class="border-[#E4E7EC] m-0">

            {{-- Timestamps --}}
            <div class="grid grid-cols-2 gap-4 text-xs text-[#667085]">
                <div>
                    <span class="block">Dibuat</span>
                    <span class="font-medium text-[#101828]">{{ $booking->created_at->format('d M Y, H:i') }}</span>
                </div>
                <div>
                    <span class="block">Terakhir Diperbarui</span>
                    <span class="font-medium text-[#101828]">{{ $booking->updated_at->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        @if(in_array($booking->status->value, ['pending', 'confirmed']))
        <div class="px-6 py-4 bg-[#F5F8FC] border-t border-[#E4E7EC] flex items-center justify-end">
            <form action="{{ route('customer.booking.cancel', $booking) }}" method="POST"
                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan appointment ini?')">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="px-4 py-2 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 rounded-lg text-xs font-medium transition-colors cursor-pointer">
                    Batalkan Appointment
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
