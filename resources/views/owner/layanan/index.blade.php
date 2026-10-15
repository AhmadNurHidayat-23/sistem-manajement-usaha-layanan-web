@extends('layouts.app', ['title' => 'Katalog Layanan'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">Katalog Layanan</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Kelola daftar layanan, paket bundling, dan harga operasional.</p>
        </div>
        <div class="flex items-center gap-3">
            <x-buttons.btn variant="outline" href="{{ route('catalog.categories.index') }}">
                Kelola Kategori
            </x-buttons.btn>
            <x-buttons.btn variant="primary" href="{{ route('catalog.services.create') }}" data-modal-open="modal-create-service" class="gap-1.5">
                <x-icons.icon name="plus" class="w-4 h-4" />
                Tambah Layanan
            </x-buttons.btn>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filter Toolbar Inline (Compact, Tanpa Card Besar) -->
    <form method="GET" action="{{ route('catalog.services.index') }}" class="flex flex-wrap items-center gap-2.5">
        <div class="flex-1 min-w-[200px]">
            <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Cari nama atau deskripsi..." class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
        </div>
        <div class="w-full sm:w-44 shrink-0">
            <select id="category_id" name="category_id" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-44 shrink-0">
            <select id="branch_id" name="branch_id" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Cabang</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->display_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button type="submit" class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
                <span>Filter</span>
            </button>
            <a href="{{ route('catalog.services.index') }}" class="h-10 w-10 bg-white border border-[#D0D5DD] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg text-[13px] font-medium transition-colors flex items-center justify-center cursor-pointer shadow-2xs" title="Reset Filter">
                <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
            </a>
        </div>
    </form>

    <!-- Services Table -->
    <div class="space-y-4">
        <x-tables.table :headers="['Nama Layanan', 'Kategori', 'Cabang', 'Harga', 'Durasi', 'Tipe', 'Status', 'Aksi']">
            @forelse ($services as $service)
            <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                <td class="px-3 py-2.5">
                    <div class="font-semibold text-[#101828] whitespace-nowrap">{{ $service->name }}</div>
                    <div class="text-[11px] text-[#667085] truncate max-w-xs">{{ $service->description ?? '-' }}</div>
                </td>
                <td class="px-3 py-2.5 text-[#101828] font-medium whitespace-nowrap">
                    {{ $service->category?->name ?? '-' }}
                </td>
                <td class="px-3 py-2.5 text-[#667085] whitespace-nowrap">
                    {{ $service->branch?->display_name ?? 'Semua Cabang' }}
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <div class="font-bold text-[#062072]">
                        Rp {{ number_format($service->price, 0, ',', '.') }}{{ $service->unit ? ' / ' . $service->unit : '' }}
                    </div>
                    @if ($service->hasCommissionConfigured())
                        <div class="text-[10px] text-emerald-700 font-medium">
                            Komisi: {{ $service->formatted_commission }}
                        </div>
                    @else
                        <div class="text-[10px] text-[#667085] italic">
                            Komisi: Belum diatur
                        </div>
                    @endif
                </td>
                <td class="px-3 py-2.5 text-[#667085] whitespace-nowrap">
                    {{ $service->estimated_duration ? $service->estimated_duration . ' Mnt' : '-' }}
                </td>
                <td class="px-3 py-2.5 capitalize text-xs whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 text-[11px]">
                        {{ $service->service_type }}
                    </span>
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if ($service->is_active)
                        <x-badges.badge variant="success">Aktif</x-badges.badge>
                    @else
                        <x-badges.badge variant="neutral">Non-Aktif</x-badges.badge>
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <div class="flex items-center gap-1.5">
                        <x-buttons.btn variant="outline" href="{{ route('catalog.services.show', $service) }}" size="sm">
                            Detail
                        </x-buttons.btn>
                        <a href="{{ route('catalog.services.edit', $service) }}"
                           data-action="edit-service"
                           data-payload="{{ json_encode([
                               'id' => $service->id,
                               'name' => $service->name,
                               'service_category_id' => $service->service_category_id,
                               'branch_id' => $service->branch_id,
                               'price' => $service->price,
                               'unit' => $service->unit,
                               'estimated_duration' => $service->estimated_duration,
                               'service_type' => $service->service_type,
                               'commission_type' => $service->commission_type?->value ?? $service->commission_type,
                               'commission_value' => $service->commission_value,
                               'description' => $service->description,
                               'is_active' => $service->is_active,
                               'image_url' => $service->image_url,
                           ]) }}"
                           class="inline-flex items-center justify-center font-medium rounded-lg transition-colors cursor-pointer text-xs h-8 px-3 bg-[#EAF2FF] text-[#062072] hover:bg-[#D5E5FF]">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('catalog.services.toggle-status', $service) }}" class="inline m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-2.5 py-1 text-xs rounded-lg font-medium border cursor-pointer h-7 inline-flex items-center {{ $service->is_active ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }}">
                                {{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-[#667085]">Belum ada data layanan yang sesuai.</td>
                </tr>
            @endforelse
        </x-tables.table>

        @if ($services->hasPages())
            <div class="pt-2">
                {{ $services->links() }}
            </div>
        @endif
    </div>

    @include('owner.layanan.partials.modal-create')
    @include('owner.layanan.partials.modal-edit')
</div>
@endsection
