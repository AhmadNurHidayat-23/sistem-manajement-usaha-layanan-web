@extends('layouts.app', ['title' => 'Tambah Layanan Baru'])

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold text-[#101828] m-0">Tambah Layanan Baru</h3>
        <x-buttons.btn variant="outline" href="{{ route('catalog.services.index') }}">
            Kembali
        </x-buttons.btn>
    </div>

    <x-cards.card title="Form Detail Layanan" subtitle="Isi informasi dan spesifikasi layanan secara lengkap">
        <form method="POST" action="{{ route('catalog.services.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <x-forms.input name="name" label="Nama Layanan" placeholder="Contoh: Service Berkala Paket A" required />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="service_category_id" class="block text-sm font-medium text-[#101828] mb-1">
                        Kategori Layanan <span class="text-rose-500">*</span>
                    </label>
                    <select id="service_category_id" name="service_category_id" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('service_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('service_category_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="branch_id" class="block text-sm font-medium text-[#101828] mb-1">Cabang (Kosongkan jika berlaku semua)</label>
                    <select id="branch_id" name="branch_id" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="">Semua Cabang (Global)</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-forms.input type="number" name="price" label="Harga (Rp)" placeholder="150000" required min="0" step="1000" />
                <x-forms.input type="text" name="unit" label="Satuan Unit" placeholder="misal: sesi, kg, kendaraan, item" />
                <x-forms.input type="number" name="estimated_duration" label="Estimasi Durasi (Menit)" placeholder="45" min="1" />
                <div class="mb-4">
                    <label for="service_type" class="block text-sm font-medium text-[#101828] mb-1">
                        Tipe Layanan <span class="text-rose-500">*</span>
                    </label>
                    <select id="service_type" name="service_type" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        <option value="single" {{ old('service_type') === 'single' ? 'selected' : '' }}>Layanan Tunggal</option>
                        <option value="package" {{ old('service_type') === 'package' ? 'selected' : '' }}>Paket / Bundling</option>
                        <option value="express" {{ old('service_type') === 'express' ? 'selected' : '' }}>Layanan Express</option>
                    </select>
                    @error('service_type')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Pengaturan Komisi Karyawan (KRY-F-003) -->
            <div class="p-4 bg-[#F5F8FC] border border-[#E4E7EC] rounded-xl space-y-3">
                <div>
                    <h4 class="text-xs font-bold text-[#101828] uppercase tracking-wider m-0">Skema Komisi Karyawan</h4>
                    <p class="text-xs text-[#667085] mt-0.5 m-0">Pengaturan komisi untuk karyawan yang mengerjakan layanan ini. Kosongkan jika belum ditentukan.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="commission_type" class="block text-sm font-medium text-[#101828] mb-1">
                            Tipe Komisi
                        </label>
                        <select id="commission_type" name="commission_type" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                            <option value="">-- Belum Diatur --</option>
                            <option value="percentage" {{ old('commission_type') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                            <option value="fixed" {{ old('commission_type') === 'fixed' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                        </select>
                        @error('commission_type')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-forms.input type="number" step="0.01" min="0" name="commission_value" label="Nilai Komisi" placeholder="Contoh: 10 untuk 10%, atau 25000 untuk Rp 25.000" />
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="desc" class="block text-sm font-medium text-[#101828] mb-1">Deskripsi Layanan</label>
                <textarea id="desc" name="description" rows="3" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-[#101828] mb-1">Upload Foto Layanan</label>
                <input type="file" id="image" name="image" accept="image/*" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none">
                <p class="text-xs text-[#667085] mt-1">Format: JPG, PNG, WEBP. Maksimal 2MB.</p>
                @error('image')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-[#062072] border-[#E4E7EC] rounded">
                <label for="is_active" class="text-sm font-medium text-[#101828]">Status Layanan Aktif</label>
            </div>

            <div class="pt-4 border-t border-[#E4E7EC] flex justify-end gap-3">
                <x-buttons.btn variant="outline" href="{{ route('catalog.services.index') }}">
                    Batal
                </x-buttons.btn>
                <x-buttons.btn variant="primary" type="submit">
                    Simpan Layanan
                </x-buttons.btn>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
