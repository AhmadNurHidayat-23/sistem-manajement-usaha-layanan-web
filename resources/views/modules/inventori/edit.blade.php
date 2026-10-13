@extends('layouts.app', ['title' => 'Edit Bahan Inventori'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Edit Bahan</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">{{ $item->name }} &bull; Cabang {{ $item->branch?->name }}</p>
        </div>
        <x-buttons.btn variant="outline" href="{{ route('inventori.show', $item) }}" class="gap-1.5 text-xs">
            Kembali
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
        <form method="POST" action="{{ route('inventori.update', $item) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Cabang (Readonly) -->
            <div>
                <label class="block text-xs font-semibold text-[#344054] mb-1">Cabang Outlet</label>
                <input
                    type="text"
                    readonly
                    value="{{ $item->branch?->name }}"
                    class="w-full text-xs rounded-lg border border-[#E4E7EC] bg-slate-50 px-3 py-2 text-[#667085] cursor-not-allowed"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Kode Bahan -->
                <div>
                    <label for="code" class="block text-xs font-semibold text-[#344054] mb-1">
                        Kode Bahan
                    </label>
                    <input
                        type="text"
                        name="code"
                        id="code"
                        value="{{ old('code', $item->code) }}"
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
                            <option value="{{ $u }}" {{ old('unit', $item->unit) === $u ? 'selected' : '' }}>{{ $u }}</option>
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
                    value="{{ old('name', $item->name) }}"
                    class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-[#062072] focus:ring-1 focus:ring-[#062072]"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Batas Stok Minimum -->
                <div>
                    <label for="minimum_stock" class="block text-xs font-semibold text-[#344054] mb-1">
                        Batas Stok Minimum <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="minimum_stock"
                        id="minimum_stock"
                        required
                        value="{{ old('minimum_stock', $item->minimum_stock) }}"
                        class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-[#062072] focus:ring-1 focus:ring-[#062072]"
                    >
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
                        value="{{ old('cost_per_unit', $item->cost_per_unit) }}"
                        class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-[#062072] focus:ring-1 focus:ring-[#062072]"
                    >
                </div>
            </div>

            <div class="p-3 bg-blue-50 border border-blue-200 text-[#062072] rounded-lg text-xs">
                <strong>Catatan:</strong> Stok saat ini adalah <strong>{{ rtrim(rtrim(number_format($item->current_stock, 2, ',', '.'), '0'), ',') }} {{ $item->unit }}</strong>. Untuk mengubah jumlah stok, gunakan menu <strong>Stok Masuk</strong> atau <strong>Stok Keluar</strong> pada halaman detail agar mutasi tercatat dalam riwayat.
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
                    class="w-full text-xs rounded-lg border border-[#D0D5DD] px-3 py-2 text-[#101828] focus:border-[#062072] focus:ring-1 focus:ring-[#062072]"
                >{{ old('description', $item->description) }}</textarea>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-[#E4E7EC] flex items-center justify-end gap-2">
                <a
                    href="{{ route('inventori.show', $item) }}"
                    class="px-4 py-2 border border-[#D0D5DD] text-[#344054] hover:bg-slate-50 text-xs font-semibold rounded-lg transition-colors"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="bg-[#062072] hover:bg-[#0B3A9E] text-white font-semibold text-xs px-5 py-2 rounded-lg transition-colors shadow-xs"
                >
                    Perbarui Bahan
                </button>
            </div>
        </form>
    </x-cards.card>
</div>
@endsection
