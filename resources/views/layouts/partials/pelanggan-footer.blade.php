{{-- Footer --}}
<footer id="tentang" class="bg-white dark:bg-[#060B18] border-t border-[#E4E7EC] dark:border-[#1E2D4A] text-[#667085] dark:text-[#94A3B8] mt-auto scroll-mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-7 sm:pt-8 lg:pt-9 pb-5 sm:pb-6 lg:pb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 sm:gap-8 lg:gap-10">
            
            {{-- Info SIMAUL (Kolom 1: 2 Kolom Grid) --}}
            <div class="lg:col-span-2 space-y-2.5">
                <a href="{{ route('customer.home') }}" class="inline-block no-underline">
                    <img src="{{ asset('images/brand/logo-header.png') }}" alt="SIMAUL" class="h-8 sm:h-9 w-auto object-contain dark:brightness-0 dark:invert transition-all">
                </a>
                <p class="text-xs text-[#667085] dark:text-[#94A3B8] leading-relaxed max-w-sm m-0">
                    {{ __('public.footer.description') }}
                </p>
                <div class="pt-0.5 text-xs text-[#667085] dark:text-[#94A3B8]">
                    <span>{{ __('public.footer.hours') }}</span>
                </div>
            </div>

            {{-- Navigasi (Kolom 2) --}}
            <div>
                <h4 class="text-xs font-semibold text-[#101828] dark:text-white uppercase tracking-wider mb-2.5">{{ __('public.footer.nav') }}</h4>
                <ul class="space-y-1.5 sm:space-y-2 text-xs list-none p-0 m-0">
                    <li><a href="{{ route('customer.home') }}" class="text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white transition-colors no-underline">{{ __('public.navbar.home') }}</a></li>
                    <li><a href="{{ route('customer.home') }}#cabang" class="text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white transition-colors no-underline">{{ __('public.navbar.branches') }}</a></li>
                    <li><a href="{{ route('customer.home') }}#layanan" class="text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white transition-colors no-underline">{{ __('public.navbar.services') }}</a></li>
                    <li><a href="{{ route('customer.home') }}#cara-kerja" class="text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white transition-colors no-underline">{{ __('public.navbar.workflow') }}</a></li>
                    <li><a href="{{ route('customer.promo') }}" class="text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white transition-colors no-underline">{{ __('public.navbar.promo') }}</a></li>
                    <li><a href="{{ route('customer.member') }}" class="text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white transition-colors no-underline">{{ __('public.navbar.member_points') }}</a></li>
                </ul>
            </div>

            {{-- Cabang & Layanan (Kolom 3) --}}
            <div>
                <h4 class="text-xs font-semibold text-[#101828] dark:text-white uppercase tracking-wider mb-2.5">{{ __('public.footer.branches') }}</h4>
                <ul class="space-y-1.5 sm:space-y-2 text-xs list-none p-0 m-0 text-[#667085] dark:text-[#94A3B8]">
                    <li>Barbershop & Studio</li>
                    <li>Laundry & Shoe Care</li>
                    <li>Auto Care & Detailing</li>
                    <li>{{ __('public.footer.standardized_services') }}</li>
                </ul>
            </div>

            {{-- Kontak (Kolom 4) --}}
            <div>
                <h4 class="text-xs font-semibold text-[#101828] dark:text-white uppercase tracking-wider mb-2.5">{{ __('public.footer.contact') }}</h4>
                <ul class="space-y-1.5 sm:space-y-2 text-xs list-none p-0 m-0 text-[#667085] dark:text-[#94A3B8]">
                    <li>CS / WhatsApp: 0812-3456-7890</li>
                    <li>Email: info@simaul.local</li>
                    <li class="pt-0.5">
                        <a href="{{ route('customer.pesanan.create') }}" class="inline-block text-xs font-medium text-[#062072] dark:text-[#93C5FD] hover:underline no-underline">
                            {{ __('public.footer.new_order') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Hak Cipta --}}
        <div class="mt-6 sm:mt-7 pt-3.5 sm:pt-4 border-t border-[#E4E7EC] dark:border-[#1E2D4A] flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 text-xs text-[#667085] dark:text-[#94A3B8]">
            <p class="m-0">&copy; {{ date('Y') }} SIMAUL — {{ __('public.footer.tagline') }}</p>
            <div class="flex items-center gap-6 sm:mr-16 lg:mr-20">
                <span class="hover:text-[#101828] dark:hover:text-white transition-colors cursor-default">{{ __('public.footer.privacy') }}</span>
                <span class="hover:text-[#101828] dark:hover:text-white transition-colors cursor-default">{{ __('public.footer.terms') }}</span>
            </div>
        </div>
    </div>
</footer>
