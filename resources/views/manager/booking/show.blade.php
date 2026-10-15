@php
    $isEmployee = auth()->user()?->role?->name === 'employee';
@endphp
@extends('layouts.app', ['title' => $isEmployee ? 'Detail Tugas | SIMAUL' : 'Detail Booking #' . $booking->id . ' | SIMAUL'])

@section('content')
<div class="w-full space-y-5">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            @if($isEmployee)
                <h2 class="text-xl font-bold text-[#101828] m-0">Detail Tugas</h2>
                <p class="text-xs text-[#667085] mt-1 m-0">Pekerjaan yang ditugaskan kepada Anda &bull; Terakhir diperbarui {{ $booking->updated_at->diffForHumans() }}</p>
            @else
                <h3 class="text-xl font-bold text-[#101828] m-0">Detail Booking #{{ $booking->id }}</h3>
                <p class="text-xs text-[#667085] mt-1 m-0">Dibuat {{ $booking->created_at->diffForHumans() }}</p>
            @endif
        </div>
        <div class="flex items-center gap-2.5">
            @if($isEmployee)
                <a href="{{ route('queue.index') }}" class="inline-flex items-center justify-center px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-[#D0D5DD] text-[#344054] hover:bg-[#F5F8FC] hover:text-[#062072] transition-colors shadow-2xs">
                    Kembali ke Antrean
                </a>
            @else
                <x-buttons.btn variant="outline" href="{{ route('bookings.index') }}">
                    Kembali
                </x-buttons.btn>
            @endif
        </div>
    </div>

    {{-- Flash Alerts --}}
    @foreach(['success', 'error'] as $msg)
        @if(session($msg))
        <div class="{{ $msg === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700' }} border px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($msg === 'success')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @endif
            </svg>
            <span>{{ session($msg) }}</span>
        </div>
        @endif
    @endforeach

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Info Utama --}}
        <div class="md:col-span-2 space-y-4">
            <x-cards.card :title="$isEmployee ? 'Informasi Tugas' : 'Informasi Booking'">
                <dl class="divide-y divide-[#E4E7EC]">
                    @php
                        $rows = [
                            ['Pelanggan',   $booking->user?->name ?? '-'],
                            ['Nama Usaha',  $booking->branch?->display_name ?? '-'],
                            ['Jenis Pekerjaan', 'Appointment (Janji Temu)'],
                            ['Layanan',     $booking->service?->name ?? '-'],
                            ['Petugas',     $booking->employee?->name ?? 'Belum ditentukan'],
                            ['Tanggal',     $booking->booking_date->translatedFormat('d F Y')],
                            ['Jam',         \Carbon\Carbon::parse($booking->booking_time)->format('H:i') . ' WIB'],
                            ['Catatan',     $booking->notes ?? '-'],
                        ];
                    @endphp
                    @foreach($rows as [$label, $value])
                    <div class="py-3 flex justify-between gap-4">
                        <dt class="text-sm text-[#667085] shrink-0">{{ $label }}</dt>
                        <dd class="text-sm font-medium text-[#101828] text-right">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </x-cards.card>

            {{-- Detail Layanan Tambahan --}}
            @if($booking->details->count())
            <x-cards.card title="Layanan Tambahan">
                <ul class="divide-y divide-[#E4E7EC]">
                    @foreach($booking->details as $detail)
                    <li class="py-2 flex justify-between text-sm">
                        <span class="text-[#101828]">{{ $detail->service?->name }}</span>
                        <span class="text-[#667085]">Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                    </li>
                    @endforeach
                </ul>
            </x-cards.card>
            @endif
        </div>

        {{-- Status & Aksi --}}
        <div class="space-y-4">
            @if($isEmployee)
                {{-- Status Card Khusus Karyawan (Tanpa Dropdown) --}}
                @php
                    $stepIndex = match($booking->status->value) {
                        'confirmed' => 1,
                        'in_progress' => 2,
                        'completed' => 3,
                        default => 0,
                    };
                @endphp
                <x-cards.card title="Status Pengerjaan">
                    {{-- Progres Visual Sederhana Tiga Tahapan SRS --}}
                    <div class="mb-5 pb-4 border-b border-[#E4E7EC]">
                        <p class="text-[11px] font-semibold text-[#667085] uppercase tracking-wider mb-2">Progres Pengerjaan</p>
                        <div class="grid grid-cols-3 gap-1.5 text-center text-xs">
                            <div class="py-2 px-1 rounded-lg {{ $stepIndex >= 1 ? 'bg-[#DCE8FF] text-[#062072] font-bold border border-[#062072]/20' : 'bg-[#F2F4F7] text-[#98A2B3]' }}">
                                1. Menunggu
                            </div>
                            <div class="py-2 px-1 rounded-lg {{ $stepIndex >= 2 ? 'bg-[#DCE8FF] text-[#062072] font-bold border border-[#062072]/20' : 'bg-[#F2F4F7] text-[#98A2B3]' }}">
                                2. Dikerjakan
                            </div>
                            <div class="py-2 px-1 rounded-lg {{ $stepIndex === 3 ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-300' : 'bg-[#F2F4F7] text-[#98A2B3]' }}">
                                3. Selesai
                            </div>
                        </div>
                    </div>

                    {{-- Aksi Kontekstual Berdasarkan Status --}}
                    @if($booking->status->value === 'confirmed')
                        <div class="space-y-3">
                            <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-xs text-amber-800">
                                Tugas ini telah siap dan menunggu untuk mulai dikerjakan.
                            </div>
                            <form method="POST" action="{{ route('bookings.update-status', $booking) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit" class="w-full py-2.5 px-4 bg-[#062072] hover:bg-[#04184F] text-white text-xs font-bold rounded-lg transition-colors cursor-pointer shadow-2xs">
                                    Mulai Pengerjaan
                                </button>
                            </form>
                        </div>
                    @elseif($booking->status->value === 'in_progress')
                        <div class="space-y-3">
                            <div class="p-3 bg-blue-50 rounded-lg border border-blue-200 text-xs text-blue-800">
                                Layanan sedang Anda kerjakan. Tandai selesai jika tuntas.
                            </div>
                            <form method="POST" action="{{ route('bookings.update-status', $booking) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors cursor-pointer shadow-2xs">
                                    Tandai Selesai
                                </button>
                            </form>
                        </div>
                    @elseif($booking->status->value === 'completed')
                        <div class="p-3.5 bg-emerald-50 rounded-lg border border-emerald-200 text-xs text-emerald-800 font-medium text-center">
                            Layanan telah diselesaikan. Status pengerjaan bersifat read-only.
                        </div>
                    @elseif($booking->status->value === 'pending')
                        <div class="p-3.5 bg-slate-100 rounded-lg border border-slate-200 text-xs text-slate-700">
                            <strong>Status: Menunggu Konfirmasi.</strong><br>
                            Tugas belum dapat dimulai karena masih memerlukan konfirmasi pihak berwenang.
                        </div>
                    @else
                        <div class="p-3.5 bg-rose-50 rounded-lg border border-rose-200 text-xs text-rose-800">
                            Status: {{ $booking->status->label() }}
                        </div>
                    @endif
                </x-cards.card>
            @else
                {{-- Status Card untuk Owner / Manager --}}
                @php
                    $statusConfig = [
                        'pending'     => ['label' => 'Pending',     'class' => 'bg-amber-100 text-amber-800', 'dot' => 'bg-amber-500'],
                        'confirmed'   => ['label' => 'Dikonfirmasi','class' => 'bg-[#DCE8FF] text-[#062072]', 'dot' => 'bg-[#0B3A9E]'],
                        'in_progress' => ['label' => 'Berlangsung', 'class' => 'bg-[#DCE8FF] text-[#062072]', 'dot' => 'bg-[#0B3A9E]'],
                        'completed'   => ['label' => 'Selesai',     'class' => 'bg-emerald-100 text-emerald-800','dot' => 'bg-emerald-500'],
                        'cancelled'   => ['label' => 'Dibatalkan',  'class' => 'bg-rose-100 text-rose-800',  'dot' => 'bg-rose-400'],
                    ];
                    $sc = $statusConfig[$booking->status->value] ?? ['label' => $booking->status->value, 'class' => 'bg-slate-100 text-slate-700', 'dot' => 'bg-slate-400'];
                @endphp
                <x-cards.card title="Status">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-block w-2 h-2 rounded-full {{ $sc['dot'] }}"></span>
                        <span class="text-sm font-semibold {{ $sc['class'] }} px-2 py-0.5 rounded-full">{{ $sc['label'] }}</span>
                    </div>

                    <form method="POST" action="{{ route('bookings.update-status', $booking) }}" class="space-y-2">
                        @csrf @method('PATCH')
                        <select name="status" id="status-select"
                            class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#062072]">
                            @php
                                $statusOptions = [
                                    'pending'     => 'Menunggu Konfirmasi',
                                    'confirmed'   => 'Menunggu Dikerjakan',
                                    'in_progress' => 'Sedang Dikerjakan',
                                    'completed'   => 'Selesai',
                                ];
                            @endphp
                            @foreach($statusOptions as $val => $label)
                                <option value="{{ $val }}" {{ $booking->status->value === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-buttons.btn variant="primary" type="submit" class="w-full justify-center">
                            Update Status
                        </x-buttons.btn>
                    </form>
                </x-cards.card>

                @canany(['update', 'cancel'], $booking)
                <x-cards.card title="Aksi Administratif">
                    <div class="space-y-2">
                        @if(in_array($booking->status->value, ['pending', 'confirmed']))
                            @can('update', $booking)
                            <x-buttons.btn variant="secondary" href="{{ route('bookings.edit', $booking) }}" class="w-full justify-center">
                                Edit Booking
                            </x-buttons.btn>
                            @endcan
                            @can('cancel', $booking)
                            <form method="POST" action="{{ route('bookings.cancel', $booking) }}">
                                @csrf @method('PATCH')
                                <button type="submit" id="btn-batalkan-booking"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                    Batalkan Booking
                                </button>
                            </form>
                            @endcan
                        @endif
                    </div>
                </x-cards.card>
                @endcanany
            @endif
        </div>
    </div>
</div>
@endsection
