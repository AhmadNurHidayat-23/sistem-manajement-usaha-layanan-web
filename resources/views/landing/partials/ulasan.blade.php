{{-- Ulasan --}}
@if (isset($reviews) && $reviews->count())
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mb-8">
        <span class="text-xs font-semibold uppercase tracking-wider text-[#062072] block mb-2">ULASAN PELANGGAN</span>
        <h2 class="text-2xl sm:text-3xl font-bold text-[#101828] tracking-tight m-0">
            Apa Kata Mereka?
        </h2>
        <p class="text-sm text-[#667085] mt-1.5 m-0">
            Penilaian langsung dari pelanggan yang telah menggunakan layanan kami di berbagai cabang.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($reviews as $rev)
            <div class="bg-white rounded-xl border border-[#E4E7EC] p-6 flex flex-col justify-between h-full hover:border-[#062072]/30 transition-colors">
                <div>
                    {{-- Rating Bintang --}}
                    <div class="flex items-center gap-1 text-amber-500 mb-3">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= $rev->rating ? 'fill-current' : 'text-neutral-200' }}" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <p class="text-sm text-[#101828] leading-relaxed m-0 mb-4 font-normal">
                        "{{ $rev->komentar ?? 'Pelayanan sangat memuaskan, ramah, profesional, dan selesai tepat waktu.' }}"
                    </p>
                </div>

                <div class="pt-4 border-t border-[#E4E7EC] flex items-center justify-between text-xs">
                    <div>
                        <p class="font-semibold text-[#101828] m-0">{{ $rev->user?->name ?? 'Pelanggan' }}</p>
                        <p class="text-[11px] text-[#667085] m-0">{{ $rev->service?->name ?? 'Layanan SIMAUL' }}</p>
                    </div>
                    <span class="text-[11px] text-[#667085]">{{ $rev->created_at->format('d M Y') }}</span>
                </div>
            </div>
        @endforeach

        {{-- 1 Card Ulasan Tambahan --}}
        <div class="bg-white rounded-xl border border-[#E4E7EC] p-6 flex flex-col justify-between h-full hover:border-[#062072]/30 transition-colors">
            <div>
                {{-- Rating Bintang --}}
                <div class="flex items-center gap-1 text-amber-500 mb-3">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <p class="text-sm text-[#101828] leading-relaxed m-0 mb-4 font-normal">
                    "Pengerjaan laundry dan perawatan pakaian formal sangat rapi, wangi tahan lama, serta pengerjaan tepat waktu."
                </p>
            </div>

            <div class="pt-4 border-t border-[#E4E7EC] flex items-center justify-between text-xs">
                <div>
                    <p class="font-semibold text-[#101828] m-0">Hendri Gunawan</p>
                    <p class="text-[11px] text-[#667085] m-0">Dry Clean & Formal Care</p>
                </div>
                <span class="text-[11px] text-[#667085]">04 Oct 2026</span>
            </div>
        </div>
    </div>
</section>
@endif
