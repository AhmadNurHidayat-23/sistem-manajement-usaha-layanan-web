@extends('layouts.app', ['title' => 'Tambah Promo / Voucher Baru'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Tambah Promo / Voucher Baru</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Buat kupon diskon baru untuk transaksi kasir dan booking pelanggan.</p>
        </div>
        <x-buttons.btn variant="outline" href="{{ route('promotions.index') }}" class="gap-1.5 text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </x-buttons.btn>
    </div>

    <!-- Alert Errors -->
    @if ($errors->any())
        <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-700">
            <p class="font-bold mb-1">Periksa kembali isian formulir:</p>
            <ul class="list-disc list-inside space-y-1 m-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <x-cards.card class="p-6">
        <form method="POST" action="{{ route('promotions.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Kode Promo -->
                <div>
                    <label for="code" class="block text-xs font-semibold text-[#101828] mb-1">
                        Kode Promo / Voucher <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" required placeholder="Contoh: HEMAT20, PROMOSERVIS" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] font-semibold uppercase focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <p class="text-[11px] text-[#667085] mt-1 m-0">Kode unik yang dimasukkan kasir/pelanggan saat checkout.</p>
                    @error('code')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Promo -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-[#101828] mb-1">
                        Nama / Judul Promo <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Diskon Servis Berkala 20%" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <p class="text-[11px] text-[#667085] mt-1 m-0">Deskripsi singkat penawaran diskon.</p>
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Cabang Berlaku -->
            <div>
                <label for="branch_id" class="block text-xs font-semibold text-[#101828] mb-1">
                    Cakupan Cabang
                </label>
                <select id="branch_id" name="branch_id" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    @if (auth()->user()?->role?->name === 'owner')
                        <option value="">Semua Cabang (Global)</option>
                    @endif
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->display_name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-[#667085] mt-1 m-0">Pilih cabang spesifik atau biarkan berlaku untuk seluruh cabang.</p>
                @error('branch_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Tipe Diskon -->
                <div>
                    <label for="discount_type" class="block text-xs font-semibold text-[#101828] mb-1">
                        Jenis Diskon <span class="text-rose-500">*</span>
                    </label>
                    <select id="discount_type" name="discount_type" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                        <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                    </select>
                    @error('discount_type')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nilai Diskon -->
                <div>
                    <label for="discount_value" class="block text-xs font-semibold text-[#101828] mb-1">
                        Nilai Diskon <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="discount_value" name="discount_value" value="{{ old('discount_value', 10) }}" step="0.01" min="0" required placeholder="Contoh: 20 untuk 20% atau 25000 untuk Rp 25.000" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <p class="text-[11px] text-[#667085] mt-1 m-0">Jika persentase: isi 1-100. Jika nominal tetap: isi angka rupiah.</p>
                    @error('discount_value')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Kuota -->
                <div>
                    <label for="quota" class="block text-xs font-semibold text-[#101828] mb-1">
                        Kuota Penggunaan
                    </label>
                    <input type="number" id="quota" name="quota" value="{{ old('quota') }}" min="1" placeholder="Kosongkan jika unlimited" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <p class="text-[11px] text-[#667085] mt-1 m-0">Batas maksimal kupon dapat dipakai.</p>
                    @error('quota')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Mulai -->
                <div>
                    <label for="start_date" class="block text-xs font-semibold text-[#101828] mb-1">
                        Tanggal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    @error('start_date')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label for="end_date" class="block text-xs font-semibold text-[#101828] mb-1">
                        Tanggal Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date', date('Y-m-d', strtotime('+30 days'))) }}" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    @error('end_date')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status -->
            @if (auth()->user()?->role?->name === 'owner')
                <div>
                    <label for="status" class="block text-xs font-semibold text-[#101828] mb-1">
                        Status Promo <span class="text-rose-500">*</span>
                    </label>
                    <select id="status" name="status" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif (Dapat langsung digunakan)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif (Draft / Ditunda)</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            @else
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                    <p class="font-semibold m-0">Pengajuan Promo Cabang:</p>
                    <p class="m-0 mt-0.5 text-amber-700">Promo yang diajukan oleh Manajer akan diajukan dengan status <strong>Menunggu Persetujuan (Pending)</strong> hingga disetujui oleh Owner.</p>
                </div>
            @endif

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-[#E4E7EC] flex items-center justify-end gap-3">
                <x-buttons.btn variant="outline" href="{{ route('promotions.index') }}" class="text-xs">
                    Batal
                </x-buttons.btn>
                <x-buttons.btn variant="primary" type="submit" class="text-xs">
                    {{ auth()->user()?->role?->name === 'owner' ? 'Simpan Promo' : 'Ajukan Promo' }}
                </x-buttons.btn>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
