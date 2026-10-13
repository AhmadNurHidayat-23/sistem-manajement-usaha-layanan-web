@extends('layouts.public', ['title' => 'Katalog Layanan Publik'])

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-[#062072] to-[#0B3A9E] rounded-xl text-white p-6 sm:p-8">
        <h2 class="text-2xl font-bold text-white m-0">Katalog Layanan & Paket</h2>
        <p class="text-sm text-[#DCE8FF] mt-1 m-0">Temukan layanan service dan perawatan terbaik dengan filter mudah.</p>
    </div>

    <!-- Search & Filter Card -->
    <x-cards.card class="p-4 sm:p-5">
        <form method="GET" action="{{ route('public.catalog') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 items-end">
            <div>
                <label for="q" class="block text-[13px] font-medium text-[#101828] mb-1.5">Cari Layanan</label>
                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Ketik kata kunci..." class="w-full h-11 px-3.5 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072]">
            </div>

            <div>
                <label for="category_id" class="block text-[13px] font-medium text-[#101828] mb-1.5">Kategori</label>
                <select id="category_id" name="category_id" class="w-full h-11 px-3.5 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="branch_id" class="block text-[13px] font-medium text-[#101828] mb-1.5">Cabang</label>
                <select id="branch_id" name="branch_id" class="w-full h-11 px-3.5 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->display_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 h-11 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-2 cursor-pointer shadow-2xs">
                    <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
                    <span>Cari Layanan</span>
                </button>
                <a href="{{ route('public.catalog') }}" class="h-11 px-3.5 bg-white border border-[#E4E7EC] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg text-[13px] font-medium transition-colors flex items-center justify-center cursor-pointer shadow-2xs" title="Reset Filter">
                    <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
                </a>
            </div>
        </form>
    </x-cards.card>

    <!-- Services Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($services as $service)
            <x-cards.card class="flex flex-col justify-between hover:shadow-md transition-shadow">
                <div>
                    @if ($service->image_url)
                        <img src="{{ $service->image_url }}" alt="{{ $service->name }}" class="w-full h-40 object-cover rounded-lg mb-4 border border-[#E4E7EC]">
                    @else
                        <div class="w-full h-40 bg-[#F5F8FC] border border-[#E4E7EC] rounded-lg mb-4 flex items-center justify-center text-[#667085] text-xs">
                            Tidak Ada Foto
                        </div>
                    @endif

                    <div class="flex items-center gap-2 mb-2">
                        <x-badges.badge variant="primary">{{ $service->category?->name ?? 'Umum' }}</x-badges.badge>
                        @if ($service->service_type === 'package')
                            <x-badges.badge variant="warning">Paket Bundling</x-badges.badge>
                        @endif
                    </div>

                    <h4 class="text-lg font-bold text-[#101828] mb-1">{{ $service->name }}</h4>
                    <p class="text-xs text-[#667085] line-clamp-2 mb-3">{{ $service->description ?? 'Layanan terpercaya kualitas terjamin.' }}</p>

                    <div class="text-xs text-[#667085] space-y-1 mb-4">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Durasi: {{ $service->estimated_duration ? $service->estimated_duration . ' Menit' : '-' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span>Cabang: {{ $service->branch?->display_name ?? $service->branch?->name ?? 'Semua Cabang' }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-[#E4E7EC] flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-[#667085] uppercase tracking-wider block">Harga Layanan</span>
                        <span class="text-lg font-bold text-[#062072]">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                    </div>
                    <x-buttons.btn variant="secondary" href="#" class="!px-3 !py-1.5 text-xs">
                        Pesan Layanan
                    </x-buttons.btn>
                </div>
            </x-cards.card>
        @empty
            <div class="col-span-full bg-white border border-[#E4E7EC] rounded-xl p-8 text-center text-[#667085]">
                Belum ada layanan publik yang sesuai dengan pencarian Anda.
            </div>
        @endforelse
    </div>

    @if ($services->hasPages())
        <div class="mt-6">
            {{ $services->links() }}
        </div>
    @endif
</div>
@endsection
