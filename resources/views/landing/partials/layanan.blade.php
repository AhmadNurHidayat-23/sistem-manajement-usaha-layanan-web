{{-- Katalog Layanan --}}
<section id="layanan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24 space-y-8 sm:space-y-11">
    @php
        $isAuto = fn ($s) => $s->branch_id === 3
            || str_contains(strtolower($s->branch?->name ?? ''), 'auto')
            || str_contains(strtolower($s->name), 'mobil')
            || str_contains(strtolower($s->name), 'motor')
            || str_contains(strtolower($s->name), 'helm')
            || str_contains(strtolower($s->name), 'coating')
            || str_contains(strtolower($s->name), 'detailing');

        $isBarber = fn ($s) => $s->branch_id === 1
            || str_contains(strtolower($s->branch?->name ?? ''), 'barber')
            || str_contains(strtolower($s->name), 'rambut')
            || str_contains(strtolower($s->name), 'jenggot')
            || str_contains(strtolower($s->name), 'grooming');

        $isLaundry = fn ($s) => !$isAuto($s) && !$isBarber($s) && (
            $s->branch_id === 2
            || str_contains(strtolower($s->branch?->name ?? ''), 'laundry')
            || str_contains(strtolower($s->name), 'cuci')
            || str_contains(strtolower($s->name), 'laundry')
            || str_contains(strtolower($s->name), 'sepatu')
            || str_contains(strtolower($s->name), 'karpet')
            || str_contains(strtolower($s->name), 'jas')
        );

        $categoryConfigs = [
            [
                'title' => 'Barbershop & Grooming',
                'slug' => 'barbershop',
                'filter' => $isBarber,
            ],
            [
                'title' => 'Laundry & Shoe Care',
                'slug' => 'laundry',
                'filter' => $isLaundry,
            ],
            [
                'title' => 'Auto Care & Detailing',
                'slug' => 'auto-care',
                'filter' => $isAuto,
            ],
        ];

        $assignedIds = [];
        $sections = [];

        foreach ($categoryConfigs as $cfg) {
            $matched = $services->filter($cfg['filter'])->values();
            if ($matched->isNotEmpty()) {
                $sections[] = [
                    'title' => $cfg['title'],
                    'slug' => $cfg['slug'],
                    'services' => $matched,
                ];
                $assignedIds = array_merge($assignedIds, $matched->pluck('id')->all());
            }
        }

        $remainingServices = $services->reject(fn ($s) => in_array($s->id, $assignedIds))->values();
        if ($remainingServices->isNotEmpty()) {
            if (! empty($sections)) {
                $sections[count($sections) - 1]['services'] = $sections[count($sections) - 1]['services']->concat($remainingServices);
            } else {
                $sections[] = [
                    'title' => 'Barbershop & Grooming',
                    'slug' => 'barbershop',
                    'services' => $remainingServices,
                ];
            }
        }
    @endphp

    @forelse ($sections as $section)
        <div class="nike-category-section space-y-5" data-category="{{ $section['slug'] }}">
            {{-- Header Section: Title on Left, Prev/Next buttons on Right --}}
            <div class="flex items-center justify-between gap-4">
                <h3 class="text-[18px] sm:text-[22px] lg:text-[24px] font-medium tracking-tight text-[#101828] dark:text-white m-0">
                    {{ $section['title'] }}
                </h3>
                <div class="flex items-center gap-2">
                    <button type="button"
                        class="nike-carousel-prev w-10 h-10 rounded-full bg-[#F3F4F6] hover:bg-[#E5E7EB] dark:bg-[#1E2D4A] dark:hover:bg-[#2A3E66] text-[#111827] dark:text-white flex items-center justify-center transition-colors cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                        aria-label="{{ __('public.catalog.prev_service') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button"
                        class="nike-carousel-next w-10 h-10 rounded-full bg-[#F3F4F6] hover:bg-[#E5E7EB] dark:bg-[#1E2D4A] dark:hover:bg-[#2A3E66] text-[#111827] dark:text-white flex items-center justify-center transition-colors cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                        aria-label="{{ __('public.catalog.next_service') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Carousel Track --}}
            <div class="nike-carousel-track-wrapper overflow-x-auto scroll-smooth snap-x snap-mandatory no-scrollbar" style="scrollbar-width: none; -ms-overflow-style: none;">
                <div class="flex gap-4 sm:gap-5 pb-0">
                    @foreach ($section['services'] as $service)
                        @php
                            $unitLabel = $service->unit ?: ($service->service_type === 'appointment' ? 'sesi' : 'layanan');
                            $primaryPhoto = $service->images->map(fn ($img) => $img->image_url)->filter()->first() ?? $service->image_url;
                        @endphp
                        <div class="nike-service-card snap-start shrink-0 w-[82vw] sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-2.5rem)/3)] flex flex-col group">
                            {{-- Photo Card with 4px corner radius (Single Image) --}}
                            @if ($primaryPhoto)
                                <div class="service-carousel relative w-full h-[400px] sm:h-[420px] lg:h-[460px] rounded-[4px] overflow-hidden bg-[#F3F4F6] dark:bg-[#1E2D4A] select-none" data-carousel-id="{{ $service->id }}">
                                    <a href="{{ route('customer.layanan.show', $service) }}" class="block w-full h-full">
                                        <img src="{{ $primaryPhoto }}" alt="{{ $service->name }}"
                                            loading="lazy"
                                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105 rounded-[4px]">
                                    </a>
                                </div>
                            @endif

                            {{-- Information Beneath Photo --}}
                            <div class="mt-3.5 space-y-1">
                                {{-- Row 1: Name + [Pesan] button --}}
                                <div class="flex items-center justify-between gap-3">
                                    <a href="{{ route('customer.layanan.show', $service) }}"
                                        class="text-base font-semibold text-[#111827] dark:text-white hover:text-[#062072] dark:hover:text-[#93C5FD] transition-colors truncate no-underline">
                                        {{ $service->name }}
                                    </a>
                                    <a href="{{ route('customer.pesanan.create', ['service_id' => $service->id, 'branch_id' => $service->branch_id]) }}"
                                        class="shrink-0 bg-[#E5E7EB] hover:bg-[#D1D5DB] dark:bg-[#1F2937] dark:hover:bg-[#374151] text-[#111827] dark:text-[#F9FAFB] text-xs font-semibold px-3.5 py-1.5 rounded-[4px] transition-colors no-underline focus:outline-none focus:ring-2 focus:ring-[#062072]">
                                        {{ __('public.catalog.order_now') }}
                                    </a>
                                </div>

                                {{-- Row 2: Category text --}}
                                <p class="text-xs text-[#6B7280] dark:text-[#9CA3AF] m-0 truncate">
                                    {{ $service->category?->name ?? 'Layanan' }}
                                </p>

                                {{-- Row 3: Price in bold --}}
                                <p class="text-sm font-bold text-[#111827] dark:text-white m-0 pt-0.5">
                                    Rp {{ number_format($service->price, 0, ',', '.') }}
                                    <span class="text-xs font-normal text-[#6B7280] dark:text-[#9CA3AF]">/ {{ $unitLabel }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="border border-[#E4E7EC] dark:border-[#1E2D4A] rounded-xl p-8 text-center text-[#667085] dark:text-[#94A3B8] bg-white dark:bg-[#111C35]">
            <p class="text-sm m-0">{{ __('public.catalog.empty') }}</p>
        </div>
    @endforelse
</section>
