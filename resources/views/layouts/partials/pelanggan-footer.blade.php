{{-- Footer --}}
<footer id="tentang" class="bg-white border-t border-[#E4E7EC] text-[#667085] mt-auto scroll-mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-12">
            
            {{-- Info SIMAUL --}}
            <div class="lg:col-span-2 space-y-3">
                <a href="{{ route('customer.home') }}" class="inline-block no-underline">
                    <img src="{{ asset('images/brand/logo-header.png') }}" alt="SIMAUL" class="h-10 w-auto object-contain">
                </a>
                <p class="text-xs text-[#667085] leading-relaxed max-w-sm m-0">
                    Platform manajemen operasional dan layanan multi-cabang terpadu. Pengelolaan cabang, pemesanan, dan penanganan profesional terstandar dalam satu sistem.
                </p>
                <div class="pt-1 text-xs text-[#667085]">
                    <span>Jam Operasional: Setiap Hari, 08:00 - 21:00 WIB</span>
                </div>
            </div>

            {{-- Navigasi --}}
            <div>
                <h4 class="text-xs font-semibold text-[#101828] uppercase tracking-wider mb-3">Navigasi</h4>
                <ul class="space-y-2 text-xs list-none p-0 m-0">
                    <li><a href="{{ route('customer.home') }}" class="text-[#667085] hover:text-[#101828] transition-colors no-underline">Beranda</a></li>
                    <li><a href="{{ route('customer.home') }}#cabang" class="text-[#667085] hover:text-[#101828] transition-colors no-underline">Cabang</a></li>
                    <li><a href="{{ route('customer.home') }}#layanan" class="text-[#667085] hover:text-[#101828] transition-colors no-underline">Layanan</a></li>
                    <li><a href="{{ route('customer.home') }}#cara-kerja" class="text-[#667085] hover:text-[#101828] transition-colors no-underline">Cara Kerja</a></li>
                    <li><a href="{{ route('customer.promo') }}" class="text-[#667085] hover:text-[#101828] transition-colors no-underline">Promo</a></li>
                    <li><a href="{{ route('customer.member') }}" class="text-[#667085] hover:text-[#101828] transition-colors no-underline">Membership</a></li>
                </ul>
            </div>

            {{-- Cabang & Layanan --}}
            <div>
                <h4 class="text-xs font-semibold text-[#101828] uppercase tracking-wider mb-3">Cabang Layanan</h4>
                <ul class="space-y-2 text-xs list-none p-0 m-0 text-[#667085]">
                    <li>Barbershop & Studio</li>
                    <li>Laundry & Shoe Care</li>
                    <li>Auto Care & Detailing</li>
                    <li>Layanan Terpadu Terstandar</li>
                </ul>
            </div>

            {{-- Kontak --}}
            <div>
                <h4 class="text-xs font-semibold text-[#101828] uppercase tracking-wider mb-3">Kontak</h4>
                <ul class="space-y-2 text-xs list-none p-0 m-0 text-[#667085]">
                    <li>CS / WhatsApp: 0812-3456-7890</li>
                    <li>Email: info@simaul.local</li>
                    <li class="pt-1">
                        <a href="{{ route('customer.pesanan.create') }}" class="inline-block text-xs font-medium text-[#062072] hover:underline no-underline">
                            Buat Pesanan Baru
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Hak Cipta --}}
        <div class="mt-10 pt-6 border-t border-[#E4E7EC] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#667085]">
            <p class="m-0">&copy; {{ date('Y') }} SIMAUL — Sistem Informasi Manajemen Usaha Layanan.</p>
            <div class="flex items-center gap-6">
                <span class="hover:text-[#101828] transition-colors cursor-default">Privasi</span>
                <span class="hover:text-[#101828] transition-colors cursor-default">Syarat & Ketentuan</span>
            </div>
        </div>
    </div>
</footer>
