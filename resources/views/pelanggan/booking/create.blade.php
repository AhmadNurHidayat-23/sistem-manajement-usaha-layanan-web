@extends('layouts.pelanggan', ['title' => 'Buat Janji Temu'])

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    {{-- Header --}}
    <div>
        <a href="{{ route('customer.booking.index') }}" class="inline-flex items-center gap-1 text-xs text-[#667085] hover:text-[#062072] no-underline mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Janji Temu
        </a>
        <h2 class="text-xl font-bold text-[#101828] m-0">Buat Janji Temu</h2>
        <p class="text-xs text-[#667085] mt-1 m-0">Pilih cabang, layanan, tanggal, dan waktu untuk membuat janji temu</p>
    </div>

    {{-- Flash --}}
    @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm">
        <ul class="list-disc list-inside m-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('customer.booking.store') }}" method="POST" id="bookingForm"
        data-branch-data-url="{{ route('customer.booking.branch-data') }}"
        data-available-slots-url="{{ route('customer.booking.available-slots') }}"
        data-operating-days="{{ json_encode($selectedBranch?->operating_days ?? null) }}"
        class="bg-white border border-[#E4E7EC] rounded-2xl shadow-sm">
        @csrf

        <div class="p-6 space-y-6">
            {{-- Cabang --}}
            <div>
                <label for="branch_id" class="block text-sm font-semibold text-[#101828] mb-2">Pilih Cabang</label>
                <select name="branch_id" id="branch_id"
                    class="w-full px-4 py-3 rounded-xl border border-[#E4E7EC] text-sm text-[#101828] focus:ring-2 focus:ring-[#062072]/20 focus:border-[#062072] outline-none transition-all bg-white">
                    <option value="">— Pilih cabang —</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (old('branch_id', $selectedBranchId) == $branch->id) ? 'selected' : '' }}>
                            {{ $branch->display_name }} ({{ $branch->operating_days_label }} &bull; {{ $branch->operating_hours_label }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Layanan --}}
            <div id="service-section" class="{{ empty($selectedBranchId) ? 'hidden' : '' }}">
                <label for="service_id" class="block text-sm font-semibold text-[#101828] mb-2">Pilih Layanan</label>
                <select name="service_id" id="service_id"
                    class="w-full px-4 py-3 rounded-xl border border-[#E4E7EC] text-sm text-[#101828] focus:ring-2 focus:ring-[#062072]/20 focus:border-[#062072] outline-none transition-all bg-white">
                    <option value="">— Pilih layanan —</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ old('service_id', $selectedServiceId) == $service->id ? 'selected' : '' }}
                            data-duration="{{ $service->estimated_duration ?? 0 }}"
                            data-price="{{ $service->price }}">
                            {{ $service->name }} — Rp {{ number_format($service->price, 0, ',', '.') }} — {{ $service->formatted_duration }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Petugas (opsional) --}}
            <div id="employee-section" class="{{ empty($selectedBranchId) ? 'hidden' : '' }}">
                <label for="employee_id" class="block text-sm font-semibold text-[#101828] mb-2">Petugas <span class="text-xs text-[#667085] font-normal">(opsional)</span></label>
                <select name="employee_id" id="employee_id" data-clearable="true"
                    class="w-full px-4 py-3 rounded-xl border border-[#E4E7EC] text-sm text-[#101828] focus:ring-2 focus:ring-[#062072]/20 focus:border-[#062072] outline-none transition-all bg-white cursor-pointer">
                    <option value="" disabled {{ old('employee_id') ? '' : 'selected' }} hidden data-placeholder="true">Pilih Petugas</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tanggal --}}
            <div id="date-section" class="{{ empty($selectedBranchId) ? 'hidden' : '' }}">
                <label for="booking_date" class="block text-sm font-semibold text-[#101828] mb-2">Tanggal</label>
                <input type="date" name="booking_date" id="booking_date"
                    value="{{ old('booking_date') }}"
                    min="{{ date('Y-m-d') }}"
                    class="w-full px-4 py-3 rounded-xl border border-[#E4E7EC] text-sm text-[#101828] focus:ring-2 focus:ring-[#062072]/20 focus:border-[#062072] outline-none transition-all bg-white">
                <p id="date-warning" class="hidden text-xs text-rose-600 mt-1 m-0"></p>
            </div>

            {{-- Jam --}}
            <div id="time-section" class="{{ empty($selectedBranchId) ? 'hidden' : '' }} space-y-3">
                <div class="flex items-center justify-between">
                    <label for="booking_time" class="block text-sm font-semibold text-[#101828]">Waktu Booking Tersedia</label>
                    <span id="selected-slot-preview" class="hidden text-xs font-semibold text-[#062072] bg-[#DCE8FF]/60 px-2.5 py-1 rounded-full border border-[#062072]/20">
                        Dipilih: <span id="selected-slot-time"></span> WIB
                    </span>
                </div>

                {{-- Keterangan Warna Status (Legend) --}}
                <div class="flex flex-wrap items-center justify-between gap-2.5 p-3 bg-[#F8F9FC] border border-[#E4E7EC] rounded-xl text-xs">
                    <div class="flex items-center gap-1.5 text-[#475467] font-medium">
                        <svg class="w-3.5 h-3.5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Status Jam:</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                        <div class="flex items-center gap-1.5" title="Jam tersedia untuk dipesan">
                            <span class="w-3 h-3 rounded bg-white border border-[#D0D5DD] shadow-xs inline-block"></span>
                            <span class="text-[#344054]">Tersedia</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Jam yang sedang Anda pilih">
                            <span class="w-3 h-3 rounded bg-[#062072] border border-[#062072] inline-block"></span>
                            <span class="text-[#062072] font-semibold">Dipilih</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Kapasitas slot penuh, tidak dapat dipilih">
                            <span class="w-3 h-3 rounded bg-rose-50 border border-rose-300 inline-block"></span>
                            <span class="text-rose-700 font-medium">Penuh</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Petugas libur atau tidak bertugas pada jam ini">
                            <span class="w-3 h-3 rounded bg-amber-50 border border-amber-300 inline-block"></span>
                            <span class="text-amber-800 font-medium">Petugas Libur</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Waktu telah lewat atau di luar jam operasional">
                            <span class="w-3 h-3 rounded bg-slate-100 border border-slate-300 inline-block"></span>
                            <span class="text-slate-500">Lewat / Tutup</span>
                        </div>
                    </div>
                </div>

                {{-- Alert Petugas Libur / Tidak Memiliki Shift --}}
                <div id="employee-off-warning" class="hidden p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs sm:text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold text-amber-900 m-0">Petugas Libur / Tidak Bertugas</p>
                        <p id="employee-off-message" class="mt-0.5 mb-0 text-amber-800">Petugas yang dipilih tidak memiliki jadwal kerja pada tanggal ini. Silakan pilih tanggal lain atau petugas lainnya.</p>
                    </div>
                </div>

                <div id="slots-loading" class="hidden text-xs text-[#667085] py-4 text-center">
                    <svg class="animate-spin w-4 h-4 inline mr-1 text-[#062072]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memeriksa ketersediaan jam janji temu...
                </div>

                <p id="no-date-prompt" class="{{ old('booking_date') ? 'hidden' : '' }} text-xs text-[#667085] mt-2 py-3 px-4 bg-slate-50 border border-slate-200 rounded-xl m-0 text-center">
                    Silakan pilih tanggal booking terlebih dahulu untuk melihat ketersediaan jam.
                </p>

                <div id="slots-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-2.5"></div>
                <p id="no-slots" class="hidden text-xs text-[#667085] mt-2 py-3 px-4 bg-slate-50 border border-slate-200 rounded-xl m-0 text-center">
                    Tidak ada slot waktu yang tersedia pada tanggal ini. Silakan pilih tanggal atau petugas lain.
                </p>
                <input type="hidden" name="booking_time" id="booking_time_input" value="{{ old('booking_time') }}">
            </div>

            {{-- Catatan --}}
            <div id="notes-section" class="{{ empty($selectedBranchId) ? 'hidden' : '' }}">
                <label for="notes" class="block text-sm font-semibold text-[#101828] mb-2">Catatan <span class="text-xs text-[#667085] font-normal">(opsional)</span></label>
                <textarea name="notes" id="notes" rows="3" maxlength="1000"
                    class="w-full px-4 py-3 rounded-xl border border-[#E4E7EC] text-sm text-[#101828] focus:ring-2 focus:ring-[#062072]/20 focus:border-[#062072] outline-none transition-all bg-white resize-none"
                    placeholder="Catatan tambahan untuk petugas...">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Submit --}}
        <div class="px-6 py-4 bg-[#F5F8FC] border-t border-[#E4E7EC] rounded-b-2xl flex items-center justify-end gap-3">
            <a href="{{ route('customer.booking.index') }}"
                class="px-4 py-2.5 text-sm font-medium text-[#667085] hover:text-[#101828] transition-colors no-underline">
                Batal
            </a>
            <button type="submit" id="submitBtn"
                class="px-6 py-2.5 bg-[#062072] hover:bg-[#04184F] text-white rounded-xl text-sm font-semibold transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                disabled>
                Buat Janji Temu
            </button>
        </div>
    </form>
</div>
@endsection
