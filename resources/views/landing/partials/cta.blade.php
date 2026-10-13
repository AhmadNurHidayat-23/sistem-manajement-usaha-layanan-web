{{-- CTA Akhir --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="rounded-2xl bg-[#04184F] p-8 sm:p-14 text-center text-white shadow-xs">
        <div class="max-w-2xl mx-auto space-y-4">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#DCE8FF]/90 block mb-1">LAYANAN SIMAUL</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-white tracking-tight m-0">
                Siap Menikmati Layanan Terbaik?
            </h2>
            <p class="text-sm sm:text-base text-[#DCE8FF]/85 leading-relaxed m-0 font-normal">
                Pilih layanan yang Anda butuhkan atau masuk ke akun Anda untuk kemudahan booking appointment dan memantau status pesanan secara real-time.
            </p>
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="#layanan"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-lg bg-white hover:bg-neutral-100 text-[#062072] text-sm font-semibold transition-colors no-underline shadow-xs">
                    Jelajahi Layanan
                </a>
                @guest
                    <a href="{{ route('login') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-lg border border-white/25 hover:border-white/50 hover:bg-white/10 text-white text-sm font-semibold transition-colors no-underline">
                        Login
                    </a>
                @else
                    <a href="{{ route('customer.pesanan.index') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-lg border border-white/25 hover:border-white/50 hover:bg-white/10 text-white text-sm font-semibold transition-colors no-underline">
                        Pesanan Saya
                    </a>
                @endguest
            </div>
        </div>
    </div>
</section>
