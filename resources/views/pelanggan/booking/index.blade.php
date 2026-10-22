@extends('layouts.pelanggan', ['title' => 'Janji Temu Saya'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Janji Temu Saya</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Kelola janji temu Anda di semua cabang SIMAUL</p>
        </div>
        <a href="{{ route('customer.booking.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-sm font-semibold transition-colors no-underline shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Janji Temu Baru
        </a>
    </div>

    {{-- Flash --}}
    @foreach(['success', 'error', 'info'] as $msg)
        @if(session($msg))
        <div class="{{ $msg === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : ($msg === 'error' ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-blue-50 border-blue-200 text-blue-700') }} border px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $msg === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
            </svg>
            {{ session($msg) }}
        </div>
        @endif
    @endforeach

    {{-- Filter --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        @php
            $statusTabs = [
                '' => 'Semua',
                'pending' => 'Menunggu',
                'confirmed' => 'Dikonfirmasi',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
            ];
            $currentStatus = $filters['status'] ?? '';
        @endphp
        @foreach($statusTabs as $val => $label)
            <a href="{{ route('customer.booking.index', $val ? ['status' => $val] : []) }}"
                class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-medium transition-colors no-underline whitespace-nowrap
                    {{ $currentStatus === $val ? 'bg-[#062072] text-white' : 'bg-white border border-[#E4E7EC] text-[#667085] hover:text-[#101828] hover:bg-[#F5F8FC]' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Bookings List --}}
    <div class="space-y-4">
        @forelse($bookings as $booking)
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
            <div class="bg-white rounded-xl border border-[#E4E7EC] shadow-xs hover:shadow-md transition-shadow">
                <div class="p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold text-[#101828]">Janji Temu #{{ $booking->id }}</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }} border">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                {{ $sc['label'] }}
                            </span>
                        </div>
                        <span class="text-xs text-[#667085]">Dibuat: {{ \Carbon\Carbon::parse($booking->created_at)->translatedFormat('d M Y, H:i') }} WIB</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-sm">
                        <div>
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block">Tanggal</span>
                            <span class="font-medium text-[#101828]">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block">Jam</span>
                            <span class="font-medium text-[#101828]">{{ $booking->booking_time }} WIB</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block">Layanan</span>
                            <span class="font-medium text-[#101828]">{{ $booking->service?->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block">Cabang</span>
                            <span class="font-medium text-[#101828]">{{ $booking->branch?->display_name ?? $booking->branch?->name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#E4E7EC] flex items-center justify-end">
                        <a href="{{ route('customer.booking.show', $booking) }}"
                            class="inline-flex items-center px-3 py-1.5 border border-[#E4E7EC] rounded-lg text-xs font-medium text-[#062072] hover:bg-[#DCE8FF]/30 transition-colors no-underline">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-[#E4E7EC] rounded-xl p-10 text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-[#E4E7EC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-sm font-bold text-[#101828] mb-1">Belum Ada Janji Temu</h3>
                <p class="text-xs text-[#667085] mb-4">Anda belum memiliki janji temu. Buat janji temu pertama Anda sekarang.</p>
                <a href="{{ route('customer.booking.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-sm font-semibold transition-colors no-underline">
                    Buat Janji Temu Pertama
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($bookings->hasPages())
        <div class="mt-4">{{ $bookings->appends($filters)->links() }}</div>
    @endif
</div>
@endsection
