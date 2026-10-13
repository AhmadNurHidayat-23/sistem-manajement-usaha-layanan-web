@extends('layouts.app', ['title' => 'Tambah Bahan Inventori'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Tambah Bahan Baru</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Daftarkan bahan atau barang habis pakai ke inventori cabang.</p>
        </div>
        <x-buttons.btn variant="outline" href="{{ route('inventori.index') }}" class="gap-1.5 text-xs">
            Batal
        </x-buttons.btn>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
            <p class="font-bold mb-1">Terdapat kesalahan pada isian form:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <x-cards.card class="p-6">
        <form method="POST" action="{{ route('inventori.store') }}" class="space-y-4">
            @csrf

            <!-- Cabang Selection (Owner only) / Readonly (Manager) -->
            @if ($branches->isNotEmpty())
                <div>
                    <label for="branch_id" class="block text-xs font-semibold text-[#344054] mb-1">
                        Cabang Outlet <span class="text-rose-600">*</span>
                    </label>
                    <select
                        name="branch_id"
                        id="branch_id"
                        required
                        class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-[#062072] focus:ring-1 focus:ring-[#062072]"
                    >
                        <option value="">Pilih Cabang</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @elseif ($managerBranch)
                <div>
                    <label class="block text-xs font-semibold text-[#344054] mb-1">Cabang Outlet</label>
                    <input
                        type="text"
                        readonly
                        value="{{ $managerBranch->display_name }}"
                        class="w-full text-xs rounded-lg border border-[#E4E7EC] bg-slate-50 px-3 py-2 text-[#667085] cursor-not-allowed"
                    >
                    <input type="hidden" name="branch_id" value="{{ $managerBranch->id }}">
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Kode Bahan -->
                <div>
                    <label for="code" class="block text-xs font-semibold text-[#344054] mb-1">
                        Kode Bahan <span class="text-[#667085] font-normal">(Opsional)</span>
                    </label>
                    <input
                        type="text"
                        name="code"
                        id="code"
                        value="{{ old('code') }}"
                        placeholder="Contoh: BHN-001"
                        class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-[#062072] focus:ring-1 focus:ring-[#062072]"
                    >
                </div>

                <!-- Satuan Unit -->
                <div>
                    <label for="unit" class="block text-xs font-semibold text-[#344054] mb-1">
                        Satuan Unit <span class="text-rose-600">*</span>
                    </label>
                    <select
                        name="unit"
                        id="unit"
                        required
                        class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-[#062072] focus:ring-1 focus:ring-[#062072]"
                    >
                        @php
                            $units = ['pcs', 'botol', 'ml', 'liter', 'gram', 'kg', 'box', 'sachet', 'tube', 'lembar'];
                        @endphp
                        @foreach ($units as $u)
                            <option value="{{ $u }}" {{ old('unit', 'pcs') === $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Nama Bahan -->
            <div>
                <label for="name" class="block text-xs font-semibold text-[#344054] mb-1">
                    Nama Bahan <span class="text-rose-600">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    required
                    value="{{ old('name') }}"
                    placeholder="Contoh: Shampoo Herbal Anti Dandruff 500ml"
                    class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-[#062072] focus:ring-1 focus:ring-[#062072]"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Stok Awal -->
                <div>
                    <label for="initial_stock" class="block text-xs font-semibold text-[#344054] mb-1">
                        Stok Awal
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="initial_stock"
                        id="initial_stock"
                        value="{{ old('initial_stock', 0) }}"
                        class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-[#062072] focus:ring-1 focus:ring-[#062072]"
                    >
                    <p class="text-[10px] text-[#667085] mt-1 m-0">Akan otomatis dicatat sebagai mutasi awal.</p>
                </div>

                <!-- Batas Stok Minimum -->
                <div>
                    <label for="minimum_stock" class="block text-xs font-semibold text-[#344054] mb-1">
                        Batas Minimum <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="minimum_stock"
                        id="minimum_stock"
                        required
                        value="{{ old('minimum_stock', 5) }}"
                        class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-[#062072] focus:ring-1 focus:ring-[#062072]"
                    >
                    <p class="text-[10px] text-[#667085] mt-1 m-0">Peringatan jika stok &le; angka ini.</p>
                </div>

                <!-- Biaya per Satuan -->
                <div>
                    <label for="cost_per_unit" class="block text-xs font-semibold text-[#344054] mb-1">
                        Harga Modal / Unit (Rp)
                    </label>
                    <input
                        type="number"
                        step="100"
                        min="0"
                        name="cost_per_unit"
                        id="cost_per_unit"
                        value="{{ old('cost_per_unit', 0) }}"
                        placeholder="0"
                        class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-[#062072] focus:ring-1 focus:ring-[#062072]"
                    >
                    <p class="text-[10px] text-[#667085] mt-1 m-0">Estimasi modal pembelian.</p>
                </div>
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="description" class="block text-xs font-semibold text-[#344054] mb-1">
                    Deskripsi / Catatan Tambahan <span class="text-[#667085] font-normal">(Opsional)</span>
                </label>
                <textarea
                    name="description"
                    id="description"
                    rows="3"
                    placeholder="Contoh: Digunakan untuk layanan Hair Care treatment dan Spa."
                    class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-[#062072] focus:ring-1 focus:ring-[#062072]"
                >{{ old('description') }}</textarea>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-[#E4E7EC] flex items-center justify-end gap-2">
                <a
                    href="{{ route('inventori.index') }}"
                    class="px-4 py-2 border border-[#D0D5DD] text-[#344054] hover:bg-slate-50 text-xs font-semibold rounded-lg transition-colors"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="bg-[#062072] hover:bg-[#0B3A9E] text-white font-semibold text-xs px-5 py-2 rounded-lg transition-colors shadow-xs"
                >
                    Simpan Bahan
                </button>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
