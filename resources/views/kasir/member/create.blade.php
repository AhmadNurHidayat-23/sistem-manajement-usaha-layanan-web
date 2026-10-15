@extends('layouts.app', ['title' => 'Daftarkan Member Baru'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Daftarkan Member Baru</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Pilih pelanggan untuk diaktifkan keanggotaan loyalitasnya dan berikan bonus poin perdana.</p>
        </div>
        <x-buttons.btn variant="outline" href="{{ route('members.index') }}" class="gap-1.5 text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </x-buttons.btn>
    </div>

    <!-- Alert Notifications -->
    @if ($errors->any())
        <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-700">
            <ul class="list-disc list-inside space-y-1 m-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <x-cards.card class="p-6">
        <form method="POST" action="{{ route('members.store') }}" class="space-y-5">
            @csrf

            <!-- Select Customer -->
            <div>
                <label for="user_id" class="block text-xs font-semibold text-[#101828] mb-1">
                    Pilih Akun Pelanggan <span class="text-rose-500">*</span>
                </label>
                @if ($customers->isEmpty())
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700">
                        Tidak ada akun pelanggan yang belum memiliki member. Semua pelanggan yang ada saat ini sudah terdaftar sebagai member.
                    </div>
                @else
                    <select id="user_id" name="user_id" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" {{ old('user_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->email }}{{ $c->phone ? ' &bull; ' . $c->phone : '' }})
                            </option>
                        @endforeach
                    </select>
                @endif
                <p class="text-[11px] text-[#667085] mt-1 m-0">Pilih pelanggan yang ingin didaftarkan ke program loyalitas.</p>
                @error('user_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Member Code (Optional) -->
            <div>
                <label for="member_code" class="block text-xs font-semibold text-[#101828] mb-1">
                    Nomor / Kode Kartu Member (Opsional)
                </label>
                <input type="text" id="member_code" name="member_code" value="{{ old('member_code') }}" placeholder="Kosongkan untuk generate otomatis (misal: MBR-A9B8C7)" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] uppercase font-semibold focus:outline-none focus:ring-2 focus:ring-[#062072]">
                <p class="text-[11px] text-[#667085] mt-1 m-0">Jika dikosongkan, sistem akan membuat kode unik 6 digit secara otomatis.</p>
                @error('member_code')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bonus Poin Info Box -->
            <div class="p-3 bg-[#F5F8FC] border border-[#DCE8FF] rounded-lg flex items-center gap-3">
                <div class="p-2 bg-[#DCE8FF] text-[#062072] rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-bold text-[#101828] block">Bonus 10 Poin Selamat Datang</span>
                    <span class="text-[11px] text-[#667085]">Setiap pendaftaran member baru langsung mendapatkan 10 poin loyalitas perdana.</span>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-[#E4E7EC] flex items-center justify-end gap-3">
                <x-buttons.btn variant="outline" href="{{ route('members.index') }}" class="text-xs">
                    Batal
                </x-buttons.btn>
                <x-buttons.btn variant="primary" type="submit" class="text-xs" :disabled="$customers->isEmpty()">
                    Simpan & Aktifkan Member
                </x-buttons.btn>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
