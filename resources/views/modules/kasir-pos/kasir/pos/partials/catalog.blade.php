<!-- Service Catalog Grid -->
<div class="bg-white border border-[#E4E7EC] rounded-xl p-4 shadow-sm space-y-3">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h4 class="text-sm font-bold text-[#101828] m-0 flex items-center gap-2">
            <svg class="w-4 h-4 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            Katalog Layanan
        </h4>

        <!-- Search Input -->
        <div class="relative w-full sm:w-64">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#667085]">
                <x-icons.icon name="search" class="w-4 h-4 text-[#667085]" />
            </span>
            <input type="text" id="service-search" oninput="filterServices()" placeholder="Cari nama layanan..." class="w-full pl-9 pr-3 py-1.5 bg-[#F5F8FC] border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#062072]">
        </div>
    </div>

    <!-- Category Filters -->
    @php
        $categories = $services->pluck('category')->filter()->unique('id');
    @endphp
    @if ($categories->isNotEmpty())
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
            <button type="button" onclick="filterCategory('all')" class="cat-btn px-2.5 py-1 rounded-md font-semibold bg-[#062072] text-white shrink-0" data-cat="all">
                Semua Layanan
            </button>
            @foreach ($categories as $cat)
                <button type="button" onclick="filterCategory('{{ $cat->id }}')" class="cat-btn px-2.5 py-1 rounded-md font-medium text-[#667085] hover:bg-[#F5F8FC] hover:text-[#101828] border border-transparent shrink-0" data-cat="{{ $cat->id }}">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>
    @endif

    <!-- Services Grid -->
    <div id="services-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-[500px] overflow-y-auto pr-1">
        @forelse ($services as $srv)
            <div class="service-card group p-3 rounded-lg border border-[#E4E7EC] bg-white hover:border-[#062072] hover:shadow-sm transition-all flex flex-col justify-between"
                 data-id="{{ $srv->id }}"
                 data-name="{{ strtolower($srv->name) }}"
                 data-cat="{{ $srv->service_category_id ?? 'none' }}"
                 data-price="{{ $srv->price }}">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-blue-50 text-[#062072]">
                            {{ $srv->category?->name ?? 'Layanan' }}
                        </span>
                        @if ($srv->estimated_duration)
                            <span class="text-[10px] text-[#667085]">{{ $srv->estimated_duration }}m</span>
                        @endif
                    </div>
                    <h5 class="text-xs font-bold text-[#101828] line-clamp-2 m-0">{{ $srv->name }}</h5>
                </div>

                <div class="mt-3 pt-2 border-t border-[#F5F8FC] flex items-center justify-between">
                    <span class="text-xs font-bold text-[#062072]">
                        Rp {{ number_format($srv->price, 0, ',', '.') }}
                    </span>
                    <button type="button" onclick="addItem({{ $srv->id }}, '{{ addslashes($srv->name) }}', {{ $srv->price }})"
                            class="p-1.5 rounded-md bg-[#DCE8FF] text-[#062072] hover:bg-[#062072] hover:text-white transition-colors"
                            title="Tambah ke Keranjang">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-8 text-center text-xs text-[#667085]">
                Belum ada katalog layanan aktif untuk cabang ini.
            </div>
        @endforelse
    </div>
</div>
