@extends('layouts.app', ['title' => 'Buat Booking Appointment'])

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">Buat Booking Appointment</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Pilih cabang, layanan, tanggal dan jam yang tersedia.</p>
        </div>
        <x-buttons.btn variant="outline" href="{{ route('bookings.index') }}">Kembali</x-buttons.btn>
    </div>

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <x-cards.card title="Form Booking" subtitle="Isi detail booking dengan lengkap">
        <form method="POST" action="{{ route('bookings.store') }}" class="space-y-4" id="form-booking" data-booking-form="true" data-old-time="{{ old('booking_time') }}" data-old-emp-id="{{ old('employee_id') }}">
            @csrf

            {{-- Cabang --}}
            <div class="mb-4">
                <label for="branch_id" class="block text-sm font-medium text-[#101828] mb-1">
                    Cabang <span class="text-rose-500">*</span>
                </label>
                <select id="branch_id" name="branch_id" required
                    class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">— Pilih Cabang —</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            data-mode="{{ $branch->service_mode }}"
                            {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->display_name }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Layanan Utama --}}
            <div class="mb-4">
                <label for="service_id" class="block text-sm font-medium text-[#101828] mb-1">
                    Layanan Utama <span class="text-rose-500">*</span>
                </label>
                <select id="service_id" name="service_id" required
                    class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">— Pilih Layanan —</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }} — Rp{{ number_format($service->price, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
                @error('service_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Tanggal & Jam --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="booking_date" class="block text-sm font-medium text-[#101828] mb-1">
                        Tanggal Booking <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="booking_date" name="booking_date"
                        value="{{ old('booking_date', now()->addDay()->toDateString()) }}"
                        min="{{ now()->toDateString() }}" required
                        class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    @error('booking_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="mb-4">
                    <label for="booking_time" class="block text-sm font-medium text-[#101828] mb-1">
                        Jam <span class="text-rose-500">*</span>
                    </label>
                    <select id="booking_time" name="booking_time" required
                        class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="">— Pilih jam setelah memilih cabang & tanggal —</option>
                    </select>
                    @error('booking_time')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Pegawai (opsional) --}}
            <div class="mb-4 hidden" id="employee-section">
                <label for="employee_id" class="block text-sm font-medium text-[#101828] mb-1">Pegawai (Opsional)</label>
                <select id="employee_id" name="employee_id"
                    class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">— Tidak ada preferensi —</option>
                </select>
                @error('employee_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Catatan --}}
            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium text-[#101828] mb-1">Catatan</label>
                <textarea id="notes" name="notes" rows="3"
                    placeholder="Permintaan khusus, alergi, dsb…"
                    class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072]">{{ old('notes') }}</textarea>
                @error('notes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="pt-4 border-t border-[#E4E7EC] flex justify-end gap-3">
                <x-buttons.btn variant="outline" href="{{ route('bookings.index') }}">Batal</x-buttons.btn>
                <x-buttons.btn variant="primary" type="submit" id="btn-simpan-booking">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Booking
                </x-buttons.btn>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
