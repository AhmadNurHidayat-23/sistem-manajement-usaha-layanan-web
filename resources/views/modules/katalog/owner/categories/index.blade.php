@extends('layouts.app', ['title' => 'Kategori Layanan'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0">Kategori Layanan</h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Kelola kelompok dan klasifikasi layanan.</p>
        </div>
        <x-buttons.btn variant="primary" data-modal-open="modal-add-category" class="gap-1.5">
            <x-icons.icon name="plus" class="w-4 h-4" />
            Tambah Kategori
        </x-buttons.btn>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <x-tables.table :headers="['ID', 'Nama Kategori', 'Deskripsi', 'Status', 'Aksi']">
        @forelse ($categories as $category)
        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
            <td class="px-3 py-2.5 font-medium text-xs text-[#667085] whitespace-nowrap">#CAT-{{ $category->id }}</td>
            <td class="px-3 py-2.5 font-semibold text-[#101828] whitespace-nowrap">{{ $category->name }}</td>
            <td class="px-3 py-2.5 text-[#667085] max-w-xs truncate">{{ $category->description ?? '-' }}</td>
            <td class="px-3 py-2.5 whitespace-nowrap">
                @if ($category->is_active)
                    <x-badges.badge variant="success">Aktif</x-badges.badge>
                @else
                    <x-badges.badge variant="neutral">Non-Aktif</x-badges.badge>
                @endif
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap">
                <div class="flex items-center gap-1.5">
                    <form method="POST" action="{{ route('catalog.categories.toggle-status', $category) }}" class="inline m-0">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-2.5 py-1 text-xs rounded-lg font-medium border cursor-pointer h-7 inline-flex items-center {{ $category->is_active ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }}">
                            {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-[#667085]">Belum ada kategori layanan.</td>
            </tr>
        @endforelse
    </x-tables.table>

    <!-- Modal Add Category -->
    <x-modals.modal id="modal-add-category" title="Tambah Kategori Layanan">
        <form method="POST" action="{{ route('catalog.categories.store') }}" class="space-y-4">
            @csrf
            <x-forms.input name="name" label="Nama Kategori" placeholder="Contoh: Service Rutin, Paket Bundling" required />
            <div class="mb-4">
                <label for="cat_desc" class="block text-sm font-medium text-[#101828] mb-1">Deskripsi Kategori</label>
                <textarea id="cat_desc" name="description" rows="3" class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <x-buttons.btn variant="outline" type="button" data-modal-close="modal-add-category">
                    Batal
                </x-buttons.btn>
                <x-buttons.btn variant="primary" type="submit">
                    Simpan Kategori
                </x-buttons.btn>
            </div>
        </form>
    </x-modals.modal>
</div>
@endsection
