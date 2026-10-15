@extends('layouts.app', ['title' => 'Riwayat Perubahan Stok Bahan'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#101828] m-0">Riwayat Perubahan Stok</h2>
            <p class="text-xs text-[#667085] mt-1 m-0">Catatan kronologis seluruh mutasi stok masuk dan stok keluar bahan.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-buttons.btn variant="outline" href="{{ route('inventori.index') }}" class="gap-1.5 text-xs">
                Daftar Bahan
            </x-buttons.btn>
        </div>
    </div>

    <!-- Filter Card -->
    <x-cards.card class="p-3.5 sm:px-5 sm:py-3.5">
        <form method="GET" action="{{ route('inventori.history') }}" class="flex flex-col lg:flex-row lg:items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-[13px] font-medium text-[#101828] mb-1">Cari Nama / Kode Bahan</label>
                <input
                    type="text"
                    name="search"
                    id="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Contoh: Deterjen, SFT-001..."
                    class="w-full h-10 px-3 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072]"
                >
            </div>

            @if ($branches->isNotEmpty())
                <div class="w-full sm:w-48 shrink-0">
                    <label for="branch_id" class="block text-[13px] font-medium text-[#101828] mb-1">Cabang Outlet</label>
                    <select
                        name="branch_id"
                        id="branch_id"
                        class="w-full h-10 px-3 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]"
                    >
                        <option value="">Semua Cabang</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ (string) ($filters['branch_id'] ?? '') === (string) $branch->id ? 'selected' : '' }}>
                                {{ $branch->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="w-full sm:w-40 shrink-0">
                <label for="type" class="block text-[13px] font-medium text-[#101828] mb-1">Tipe Mutasi</label>
                <select
                    name="type"
                    id="type"
                    class="w-full h-10 px-3 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]"
                >
                    <option value="">Semua Tipe</option>
                    <option value="in" {{ ($filters['type'] ?? '') === 'in' ? 'selected' : '' }}>Masuk (Stok Masuk)</option>
                    <option value="out" {{ ($filters['type'] ?? '') === 'out' ? 'selected' : '' }}>Keluar (Stok Keluar)</option>
                </select>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button
                    type="submit"
                    class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
                >
                    <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
                    <span>Filter</span>
                </button>
                <a
                    href="{{ route('inventori.history') }}"
                    class="h-10 w-10 bg-white border border-[#E4E7EC] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg text-[13px] font-medium transition-colors flex items-center justify-center cursor-pointer shadow-2xs"
                    title="Reset Filter"
                >
                    <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
                </a>
            </div>
        </form>
    </x-cards.card>

    <!-- Table Card -->
    <x-cards.card class="overflow-hidden">
        <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-[#101828] m-0">Log Mutasi Stok Terdaftar</h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Menampilkan {{ $mutations->total() }} log mutasi.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Bahan</th>
                        @if ($branches->isNotEmpty())
                            <th class="py-3 px-4">Cabang</th>
                        @endif
                        <th class="py-3 px-4 text-center">Tipe</th>
                        <th class="py-3 px-4 text-right">Jumlah</th>
                        <th class="py-3 px-4 text-center">Sebelum &rarr; Sesudah</th>
                        <th class="py-3 px-4">Referensi</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                    @forelse ($mutations as $m)
                        <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                            <td class="py-3 px-4 text-[#667085] whitespace-nowrap">
                                {{ $m->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-[#101828]">
                                @if ($m->inventoryItem)
                                    <a href="{{ route('inventori.show', $m->inventoryItem) }}" class="text-[#062072] hover:underline">
                                        {{ $m->inventoryItem->name }}
                                    </a>
                                    @if ($m->inventoryItem->code)
                                        <span class="text-[11px] text-[#667085] font-medium block">[{{ $m->inventoryItem->code }}]</span>
                                    @endif
                                @else
                                    <span class="text-[#667085]">Item dihapus</span>
                                @endif
                            </td>
                            @if ($branches->isNotEmpty())
                                <td class="py-3 px-4 text-[#667085]">
                                    {{ $m->branch?->name ?? '-' }}
                                </td>
                            @endif
                            <td class="py-3 px-4 text-center">
                                <x-badges.status :status="$m->type" />
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-sm whitespace-nowrap">
                                @if ($m->type->value === 'in')
                                    <span class="text-emerald-600">+{{ rtrim(rtrim(number_format($m->quantity, 2, ',', '.'), '0'), ',') }}</span>
                                @else
                                    <span class="text-rose-600">-{{ rtrim(rtrim(number_format($m->quantity, 2, ',', '.'), '0'), ',') }}</span>
                                @endif
                                <span class="text-[11px] text-[#667085] font-normal">{{ $m->inventoryItem?->unit ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4 text-center text-xs whitespace-nowrap font-medium">
                                <span class="text-[#667085]">{{ rtrim(rtrim(number_format($m->stock_before, 2, ',', '.'), '0'), ',') }}</span>
                                <span class="mx-1 text-[#062072] font-bold">&rarr;</span>
                                <span class="font-bold text-[#101828]">{{ rtrim(rtrim(number_format($m->stock_after, 2, ',', '.'), '0'), ',') }}</span>
                            </td>
                            <td class="py-3 px-4 text-[#667085] capitalize">
                                {{ str_replace('_', ' ', $m->reference_type ?? '-') }}
                            </td>
                            <td class="py-3 px-4 font-medium text-[#101828]">
                                {{ $m->user?->name ?? 'Sistem' }}
                            </td>
                            <td class="py-3 px-4 text-[#667085] max-w-xs truncate">
                                {{ $m->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $branches->isNotEmpty() ? 9 : 8 }}" class="py-8 text-center text-[#667085]">
                                Belum ada riwayat mutasi stok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($mutations->hasPages())
            <div class="p-4 border-t border-[#E4E7EC]">
                {{ $mutations->links() }}
            </div>
        @endif
    </x-cards.card>
</div>
@endsection
