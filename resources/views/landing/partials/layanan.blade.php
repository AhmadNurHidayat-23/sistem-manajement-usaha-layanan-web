{{-- Katalog Layanan --}}
<section id="layanan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-[#062072] block mb-2">KATALOG LAYANAN</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-[#101828] tracking-tight m-0">Katalog Layanan</h2>
            <p class="text-sm text-[#667085] mt-1.5 m-0 max-w-xl">
                Jelajahi seluruh layanan resmi dari berbagai cabang dengan tarif transparan dan penanganan berstandar profesional.
            </p>
        </div>
        <a href="{{ route('customer.pesanan.create', isset($currentBranch) && $currentBranch ? ['branch_id' => $currentBranch->id] : []) }}"
            class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#062072] hover:text-[#04184F] no-underline shrink-0">
            <span>Buat Pesanan Baru</span>
        </a>
    </div>

    {{-- Filter Cabang --}}
    @if (isset($availableBranches) && $availableBranches->count() > 1)
        <div class="mb-6 p-4 rounded-xl bg-white border border-[#E4E7EC] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 flex-wrap text-xs">
                <span class="text-[#667085] font-medium mr-1">Filter Cabang:</span>
                <a href="{{ route('customer.home') }}#layanan"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors no-underline {{ !isset($currentBranch) || !$currentBranch ? 'bg-[#062072] text-white shadow-2xs' : 'bg-[#F5F8FC] text-[#101828] hover:bg-neutral-100 border border-[#E4E7EC]' }}">
                    Semua Cabang
                </a>
                @foreach ($availableBranches as $branchOption)
                    <a href="{{ route('customer.home', ['branch_id' => $branchOption->id]) }}#layanan"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors no-underline {{ (isset($currentBranch) && $currentBranch && $currentBranch->id === $branchOption->id) ? 'bg-[#062072] text-white shadow-2xs' : 'bg-[#F5F8FC] text-[#101828] hover:bg-neutral-100 border border-[#E4E7EC]' }}">
                        {{ $branchOption->display_name }}
                    </a>
                @endforeach
            </div>

            <div class="text-[11px] text-[#667085] flex items-center gap-2.5 shrink-0">
                <span class="inline-flex items-center gap-1.5 text-[#062072] font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Appointment
                </span>
                <span class="text-[#D0D5DD]">&bull;</span>
                <span class="inline-flex items-center gap-1.5 text-[#062072] font-medium">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Drop-off
                </span>
            </div>
        </div>
    @endif

    {{-- Grid Layanan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($services as $service)
            @php
                $unitLabel = $service->unit ?: ($service->service_type === 'appointment' ? 'sesi' : 'layanan');

                $serviceModeDisplay = match($service->service_type) {
                    'appointment' => 'Appointment',
                    'drop_off' => 'Drop-off',
                    'both' => 'Appointment & Drop-off',
                    default => ucfirst(str_replace('_', ' ', $service->service_type)),
                };
            @endphp
            <div class="bg-white rounded-xl border border-[#E4E7EC] overflow-hidden flex flex-col justify-between hover:border-[#062072]/40 transition-colors shadow-2xs group h-full">
                
                {{-- Foto / Multi-Image Carousel Layanan (1 Service = 1 Card = 3 Gambar) --}}
                @php
                    $serviceImages = $service->images && $service->images->isNotEmpty() ? $service->images : collect();
                @endphp
                <div class="service-carousel relative h-48 w-full bg-[#F5F8FC] border-b border-[#E4E7EC] overflow-hidden select-none" data-carousel-id="{{ $service->id }}">
                    @if ($serviceImages->isNotEmpty())
                        <div class="carousel-slides relative w-full h-full">
                            @foreach ($serviceImages as $img)
                                <div class="carousel-slide absolute inset-0 w-full h-full transition-opacity duration-300 {{ $loop->first ? 'opacity-100 z-1 pointer-events-auto' : 'opacity-0 z-0 pointer-events-none' }}" data-index="{{ $loop->index }}">
                                    <img src="{{ $img->image_url }}" alt="{{ $service->name }} - Foto {{ $loop->iteration }}"
                                        loading="lazy"
                                        class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>

                        @if ($serviceImages->count() > 1)
                            <!-- Tombol Prev (Kiri) -->
                            <button type="button" class="carousel-btn-prev absolute left-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-white/90 hover:bg-white text-[#062072] border border-[#E4E7EC] shadow-sm flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100 z-10 cursor-pointer" aria-label="Foto Sebelumnya" onclick="prevServiceSlide({{ $service->id }})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>

                            <!-- Tombol Next (Kanan) -->
                            <button type="button" class="carousel-btn-next absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-white/90 hover:bg-white text-[#062072] border border-[#E4E7EC] shadow-sm flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100 z-10 cursor-pointer" aria-label="Foto Berikutnya" onclick="nextServiceSlide({{ $service->id }})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>

                            <!-- Dot Indicators -->
                            <div class="carousel-dots absolute bottom-2 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-10 bg-[#04184F]/60 backdrop-blur-xs px-2 py-0.5 rounded-full">
                                @foreach ($serviceImages as $img)
                                    <button type="button" class="carousel-dot w-1.5 h-1.5 rounded-full transition-all cursor-pointer {{ $loop->first ? 'bg-white w-3' : 'bg-white/50' }}" onclick="goToServiceSlide({{ $service->id }}, {{ $loop->index }})" aria-label="Slide {{ $loop->iteration }}"></button>
                                @endforeach
                            </div>
                        @endif
                    @elseif ($service->image_url)
                        <img src="{{ $service->image_url }}" alt="{{ $service->name }}" loading="lazy" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-[#667085]/40 gap-1">
                            <svg class="w-9 h-9 text-[#062072]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                            <span class="text-[10px] text-[#667085]/60 font-medium">Dokumentasi Layanan</span>
                        </div>
                    @endif
                </div>

                {{-- Detail Kartu --}}
                <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="text-[11px] font-semibold text-[#062072] bg-[#DCE8FF]/60 px-2 py-0.5 rounded">
                                {{ $service->category?->name ?? 'Layanan' }}
                            </span>
                            @if ($service->branch)
                                <span class="text-[11px] text-[#667085] truncate max-w-[150px] flex items-center gap-1" title="{{ $service->branch->display_name }}">
                                    <svg class="w-3 h-3 text-[#98A2B3] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span>{{ $service->branch->display_name }}</span>
                                </span>
                            @endif
                        </div>

                        <h3 class="text-base font-semibold text-[#101828] group-hover:text-[#062072] transition-colors m-0 leading-snug">
                            {{ $service->name }}
                        </h3>

                        <p class="text-xs text-[#667085] line-clamp-2 leading-relaxed m-0">
                            {{ $service->description ?? 'Layanan profesional berstandar operasional terjamin dengan peralatan modern dan staf ahli.' }}
                        </p>
                    </div>

                    {{-- Estimasi & Mode --}}
                    <div class="pt-3 border-t border-[#F2F4F7] text-xs text-[#667085] space-y-1">
                        @if ($service->estimated_duration)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#062072] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Estimasi: ~{{ $service->estimated_duration }} menit</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-1.5 text-[11px] text-[#667085]">
                            <span class="w-1.5 h-1.5 rounded-full {{ $service->service_type === 'appointment' ? 'bg-emerald-500' : ($service->service_type === 'drop_off' ? 'bg-blue-500' : 'bg-indigo-500') }}"></span>
                            <span>Mode: <strong class="text-[#344054] font-medium">{{ $serviceModeDisplay }}</strong></span>
                        </div>
                    </div>

                    {{-- Harga & Aksi --}}
                    <div class="pt-3 border-t border-[#E4E7EC] flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[10px] text-[#667085] uppercase tracking-wider block font-medium">Harga</span>
                            <p class="text-sm font-bold text-[#101828] m-0">
                                Rp {{ number_format($service->price, 0, ',', '.') }}
                                <span class="text-xs font-normal text-[#667085]">/ {{ $unitLabel }}</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('customer.layanan.show', $service) }}"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium text-[#101828] hover:bg-[#F5F8FC] border border-[#E4E7EC] no-underline transition-colors">
                                Detail
                            </a>
                            <a href="{{ route('customer.pesanan.create', ['service_id' => $service->id, 'branch_id' => $service->branch_id]) }}"
                                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-[#062072] hover:bg-[#04184F] no-underline transition-colors">
                                Pesan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full border border-[#E4E7EC] rounded-xl p-8 text-center text-[#667085] bg-white">
                <p class="text-sm m-0">Belum ada layanan yang tersedia di cabang ini saat ini.</p>
            </div>
        @endforelse
    </div>

</section>
