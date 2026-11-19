{{-- Ulasan Pelanggan --}}
@if (isset($reviews) && $reviews->count())
<section id="ulasan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24">
    {{-- Header Section: Judul di kiri, Navigasi Manual di kanan --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8 sm:mb-12">
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-[#101828] dark:text-white tracking-tight m-0">
                {{ __('public.reviews.title') }}
            </h2>
            <p class="text-sm text-[#667085] dark:text-[#94A3B8] mt-1.5 m-0 max-w-xl">
                {{ __('public.reviews.subtitle') }}
            </p>
        </div>

        {{-- Navigasi Tombol Lingkaran Sederhana --}}
        <div class="flex items-center gap-2 shrink-0">
            <button type="button"
                    id="testimonial-prev-btn"
                    class="w-9 h-9 rounded-full bg-[#F3F4F6] hover:bg-[#E5E7EB] dark:bg-[#1E2D4A] dark:hover:bg-[#2A3E66] text-[#111827] dark:text-white flex items-center justify-center transition-colors cursor-pointer select-none"
                    title="{{ __('public.reviews.prev_review') }}"
                    aria-label="{{ __('public.reviews.prev_review') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button"
                    id="testimonial-next-btn"
                    class="w-9 h-9 rounded-full bg-[#F3F4F6] hover:bg-[#E5E7EB] dark:bg-[#1E2D4A] dark:hover:bg-[#2A3E66] text-[#111827] dark:text-white flex items-center justify-center transition-colors cursor-pointer select-none"
                    title="{{ __('public.reviews.next_review') }}"
                    aria-label="{{ __('public.reviews.next_review') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    {{-- Testimonial Carousel Track Wrapper (Layout terbuka tanpa card) --}}
    <div id="testimonial-wrapper" class="relative overflow-hidden group">
        <div id="testimonial-track"
             class="flex gap-8 sm:gap-12 lg:gap-16 overflow-x-auto no-scrollbar py-2"
             style="scrollbar-width: none; -ms-overflow-style: none;">
            {{-- Set 1: Data Ulasan Asli --}}
            @foreach ($reviews as $rev)
                <div class="testimonial-item shrink-0 w-[85vw] sm:w-[calc((100%-3rem)/2)] lg:w-[calc((100%-8rem)/3)] flex flex-col justify-between select-none">
                    <div>
                        {{-- Rating Bintang Minimalis --}}
                        <div class="flex items-center gap-1 text-amber-400 mb-4" aria-label="{{ __('public.reviews.rating_of_five', ['rating' => $rev->rating]) }}">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ $i <= $rev->rating ? 'fill-current' : 'text-neutral-200 dark:text-neutral-700' }}" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>

                        {{-- Teks Ulasan Editorial --}}
                        <p class="text-[13px] sm:text-[15px] text-[#101828] dark:text-[#F8FAFC] leading-[1.6] m-0 font-normal">
                            “{{ $rev->komentar }}”
                        </p>
                    </div>

                    {{-- Identitas Pelanggan & Layanan (Tanpa garis pemisah horizontal) --}}
                    <div class="mt-6 flex items-center gap-3">
                        @if ($rev->user?->avatar_url)
                            <img src="{{ $rev->user->avatar_url }}"
                                 alt="{{ $rev->user->name }}"
                                 class="w-10 h-10 rounded-full object-cover shrink-0 border border-[#E4E7EC] dark:border-[#1E2D4A]">
                        @else
                            <div class="w-10 h-10 rounded-full bg-[#062072] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                {{ strtoupper(substr($rev->user?->name ?? 'P', 0, 2)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h4 class="text-sm font-semibold text-[#101828] dark:text-white m-0 truncate leading-snug">
                                {{ $rev->user?->name ?? 'Pelanggan SIMAUL' }}
                            </h4>
                            <p class="text-xs text-[#667085] dark:text-[#94A3B8] m-0 truncate mt-0.5">
                                {{ $rev->service?->name ?? 'Layanan SIMAUL' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Set Duplikasi Visual untuk Infinite Loop Halus (Hanya visual klon) --}}
            @php
                $cloneRounds = $reviews->count() < 3 ? 3 : 2;
            @endphp
            @for ($r = 0; $r < $cloneRounds; $r++)
                @foreach ($reviews as $rev)
                    <div class="testimonial-item shrink-0 w-[85vw] sm:w-[calc((100%-3rem)/2)] lg:w-[calc((100%-8rem)/3)] flex flex-col justify-between select-none" aria-hidden="true">
                        <div>
                            <div class="flex items-center gap-1 text-amber-400 mb-4">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $rev->rating ? 'fill-current' : 'text-neutral-200 dark:text-neutral-700' }}" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>

                            <p class="text-[13px] sm:text-[15px] text-[#101828] dark:text-[#F8FAFC] leading-[1.6] m-0 font-normal">
                                “{{ $rev->komentar }}”
                            </p>
                        </div>

                        <div class="mt-6 flex items-center gap-3">
                            @if ($rev->user?->avatar_url)
                                <img src="{{ $rev->user->avatar_url }}"
                                     alt=""
                                     class="w-10 h-10 rounded-full object-cover shrink-0 border border-[#E4E7EC] dark:border-[#1E2D4A]">
                            @else
                                <div class="w-10 h-10 rounded-full bg-[#062072] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                    {{ strtoupper(substr($rev->user?->name ?? 'P', 0, 2)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <h4 class="text-sm font-semibold text-[#101828] dark:text-white m-0 truncate leading-snug">
                                    {{ $rev->user?->name ?? 'Pelanggan SIMAUL' }}
                                </h4>
                                <p class="text-xs text-[#667085] dark:text-[#94A3B8] m-0 truncate mt-0.5">
                                    {{ $rev->service?->name ?? 'Layanan SIMAUL' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endfor
        </div>
    </div>
</section>
@endif
