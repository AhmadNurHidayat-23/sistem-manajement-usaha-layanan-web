@extends('layouts.app', ['title' => 'Daftar Booking Appointment'])

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">Booking Appointment</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Kelola jadwal appointment layanan.</p>
        </div>
        @can('create', App\Models\Booking::class)
        <x-buttons.btn variant="primary" href="{{ route('bookings.create') }}" id="btn-buat-booking">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Booking
        </x-buttons.btn>
        @endcan
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters Card --}}
    <x-cards.card class="p-3.5 sm:px-5 sm:py-3.5">
        <form method="GET" action="{{ route('bookings.index') }}" class="flex flex-col lg:flex-row lg:items-end gap-3">
            <div class="w-full sm:w-44 shrink-0">
                <label class="block text-[13px] font-medium text-[#101828] mb-1">Tanggal Booking</label>
                <input type="date" name="date" value="{{ $filters['date'] ?? '' }}"
                    class="w-full h-10 px-3 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
            </div>
            @if(count($branches) > 0 && auth()->user()?->role?->name !== 'customer')
            <div class="w-full sm:w-48 shrink-0">
                <label class="block text-[13px] font-medium text-[#101828] mb-1">Cabang</label>
                <select name="branch_id" class="w-full h-10 px-3 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ ($filters['branch_id'] ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->display_name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[13px] font-medium text-[#101828] mb-1">Status Booking</label>
                <select name="status" class="w-full h-10 px-3 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                    <option value="in_progress" {{ ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' }}>Berlangsung</option>
                    <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                    <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
                    <span>Filter</span>
                </button>
                <a href="{{ route('bookings.index') }}" class="h-10 w-10 bg-white border border-[#E4E7EC] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg text-[13px] font-medium transition-colors flex items-center justify-center cursor-pointer shadow-2xs" title="Reset Filter">
                    <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
                </a>
            </div>
        </form>
    </x-cards.card>

    {{-- Table --}}
    <div class="space-y-4">
        <x-tables.table :headers="['Tanggal & Jam', 'Pelanggan', 'Cabang', 'Layanan', 'Pegawai', 'Status', 'Aksi']">
            @forelse($bookings as $booking)
            <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <span class="font-semibold text-[#101828]">{{ $booking->booking_date->format('d M Y') }}</span>
                    <span class="block text-[11px] text-[#667085]">{{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }} WIB</span>
                </td>
                <td class="px-3 py-2.5 text-[#101828] truncate max-w-[140px]">{{ $booking->user?->name ?? '-' }}</td>
                <td class="px-3 py-2.5 text-[#667085] truncate max-w-[120px]">{{ $booking->branch?->name ?? '-' }}</td>
                <td class="px-3 py-2.5 text-[#101828] truncate max-w-[140px]">{{ $booking->service?->name ?? '-' }}</td>
                <td class="px-3 py-2.5 text-[#667085] truncate max-w-[120px]">{{ $booking->employee?->name ?? '-' }}</td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <x-badges.status :status="$booking->status" />
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <div class="flex items-center gap-1.5">
                        <x-buttons.btn variant="outline" href="{{ route('bookings.show', $booking) }}" size="sm">Detail</x-buttons.btn>
                        @can('update', $booking)
                            @if(in_array($booking->status->value, ['pending', 'confirmed']))
                                <x-buttons.btn variant="secondary" href="{{ route('bookings.edit', $booking) }}" size="sm">Edit</x-buttons.btn>
                            @endif
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-10 text-center text-[#667085]">
                    <svg class="w-10 h-10 mx-auto mb-2 text-[#E4E7EC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Belum ada booking.
                </td>
            </tr>
            @endforelse
        </x-tables.table>
        @if($bookings->hasPages())
            <div class="mt-4">{{ $bookings->appends($filters)->links() }}</div>
        @endif
    </div>
</div>
@endsection
