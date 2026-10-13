{{-- Hero --}}
<section class="relative bg-[#04184F] text-white pt-24 sm:pt-28 lg:pt-32 pb-16 sm:pb-20 lg:pb-24 min-h-[calc(100vh-1.25rem)] overflow-hidden rounded-b-[2.5rem] sm:rounded-b-[3.5rem] lg:rounded-b-[4.5rem]">
    {{-- Aksen Garis Halus --}}
    <div class="absolute inset-0 pointer-events-none opacity-20">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:32px_32px] opacity-10"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Komposisi Asimetris: Kiri Dominan (65%), Kanan Mendukung (35%), Gap Rapat --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 xl:gap-10 items-start lg:items-end">
            {{-- Sisi Kiri: Label, Headline Besar, CTA (Dominan ~65%) --}}
            <div class="lg:col-span-8 flex flex-col items-start text-left">
                {{-- Label Kecil --}}
                <div class="mb-4 sm:mb-5">
                    <span class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold tracking-[0.2em] uppercase text-[#DCE8FF]/90">
                        SIMAUL
                        @if (isset($currentBranch) && $currentBranch)
                            <span class="text-white/40">•</span>
                            <span class="text-white/80 font-normal tracking-normal">{{ $currentBranch->display_name }}</span>
                        @endif
                    </span>
                </div>

                {{-- Headline Besar Dominan Rata Kiri (Tetap 4 Baris, Diperbesar Lebih Kuat) --}}
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-[82px] xl:text-[92px] font-extrabold tracking-tight text-white leading-[0.93] sm:leading-[0.91] mb-6 sm:mb-8 text-left font-sans max-w-3xl xl:max-w-4xl">
                    BAGAIMANA JIKA<br>SEMUA LAYANAN<br>JADI LEBIH<br>MUDAH?
                </h1>

                {{-- CTA Hero --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 w-full sm:w-auto">
                    <a href="#layanan"
                        class="inline-flex items-center justify-center px-7 py-3.5 rounded-xl bg-white hover:bg-neutral-100 text-[#062072] text-sm font-semibold transition-all no-underline shadow-sm">
                        Jelajahi Layanan
                    </a>

                    <a href="#cabang"
                        class="inline-flex items-center justify-center px-7 py-3.5 rounded-xl border border-white/25 hover:border-white/50 hover:bg-white/10 text-white text-sm font-semibold transition-all no-underline">
                        Pilih Cabang
                    </a>
                </div>
            </div>

            {{-- Sisi Kanan: Supporting Text & Micro Info (Mendukung, Lebih Ringan) --}}
            <div class="lg:col-span-4 flex flex-col justify-end text-left lg:self-end lg:pb-1 max-w-md">
                <p class="text-sm sm:text-base text-[#DCE8FF]/80 leading-relaxed mb-5 font-normal">
                    Temukan layanan dari berbagai cabang, pilih layanan yang sesuai kebutuhan, buat pesanan, dan pantau prosesnya dalam satu tempat.
                </p>

                {{-- Micro Info --}}
                <div class="pt-4 border-t border-white/15 flex flex-col gap-2.5 text-xs text-[#DCE8FF]/70">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#DCE8FF] shrink-0"></span>
                        <span>Mode Layanan Appointment & Drop-off Cabang</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#DCE8FF] shrink-0"></span>
                        <span>Multi-Cabang Terintegrasi Dalam Satu Sistem</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
