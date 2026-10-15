@extends('layouts.app', ['title' => 'Detail Usaha ' . $branch->display_name])

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">{{ $branch->display_name }}</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Detail konfigurasi operasional dan informasi cabang.</p>
        </div>
        <div class="flex items-center gap-3">
            <x-buttons.btn variant="outline" href="{{ route('branches.index') }}">
                Kembali
            </x-buttons.btn>
            <x-buttons.btn variant="secondary" href="{{ route('branches.edit', $branch) }}">
                Edit Cabang
            </x-buttons.btn>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Info Column -->
        <div class="md:col-span-2 space-y-6">
            <x-cards.card title="Informasi Cabang" subtitle="Data dasar lokasi dan kontak">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Nama Usaha</dt>
                        <dd class="mt-1 font-medium text-[#101828]">{{ $branch->display_name }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Status Cabang</dt>
                        <dd class="mt-1">
                            @if ($branch->is_active)
                                <x-badges.badge variant="success">Aktif Beroperasi</x-badges.badge>
                            @else
                                <x-badges.badge variant="neutral">Non-Aktif</x-badges.badge>
                            @endif
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Alamat Lengkap</dt>
                        <dd class="mt-1 font-medium text-[#101828]">{{ $branch->address }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Nomor Telepon</dt>
                        <dd class="mt-1 font-medium text-[#101828]">{{ $branch->phone ?? 'Tidak Diisi' }}</dd>
                    </div>
                </dl>
            </x-cards.card>

            <x-cards.card title="Konfigurasi Operasional" subtitle="Pengaturan jam operasional dan mode layanan">
                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Jam Buka - Tutup</dt>
                        <dd class="mt-1 font-medium text-[#101828]">
                            @if($branch->opening_time && $branch->closing_time)
                                {{ \Carbon\Carbon::parse($branch->opening_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($branch->closing_time)->format('H:i') }} WIB
                            @else
                                Belum diatur
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Mode Layanan</dt>
                        <dd class="mt-1 font-medium text-[#101828]">
                            @if ($branch->service_mode === 'both')
                                Appointment & Drop-off (Keduanya)
                            @elseif ($branch->service_mode === 'appointment')
                                Appointment Only
                            @elseif ($branch->service_mode === 'drop_off')
                                Drop-off Only
                            @else
                                {{ ucwords(str_replace('_', ' ', $branch->service_mode)) }}
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Kapasitas Maksimal / Slot</dt>
                        <dd class="mt-1 font-medium text-[#101828]">
                            {{ $branch->max_slot_capacity }} Slot Utama
                        </dd>
                    </div>
                </dl>
            </x-cards.card>
        </div>

        <!-- Summary Stats Column -->
        <div class="space-y-4">
            <x-cards.card class="p-4 sm:p-[18px]">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs sm:text-[13px] font-semibold text-[#667085] uppercase tracking-wider m-0">Total Staf Karyawan</p>
                        <h4 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#101828] mt-1.5 mb-0 tabular-nums">{{ $branch->employees_count ?? 0 }} Orang</h4>
                    </div>
                    <div class="w-10 sm:w-11 h-10 sm:h-11 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </x-cards.card>

            <x-cards.card class="p-4 sm:p-[18px]">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs sm:text-[13px] font-semibold text-[#667085] uppercase tracking-wider m-0">Layanan Tersedia</p>
                        <h4 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#101828] mt-1.5 mb-0 tabular-nums">{{ $branch->services_count ?? 0 }} Jenis</h4>
                    </div>
                    <div class="w-10 sm:w-11 h-10 sm:h-11 rounded-xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2H4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </x-cards.card>


            <x-cards.card>
                <form method="POST" action="{{ route('branches.toggle-status', $branch) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full py-2 px-4 rounded-lg font-medium text-sm text-center cursor-pointer {{ $branch->is_active ? 'bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200' }}">
                        {{ $branch->is_active ? 'Nonaktifkan Cabang Ini' : 'Aktifkan Cabang Ini' }}
                    </button>
                </form>
            </x-cards.card>
        </div>
    </div>
</div>
@endsection
