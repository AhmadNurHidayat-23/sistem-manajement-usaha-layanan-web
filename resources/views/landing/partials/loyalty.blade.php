{{-- Membership --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="rounded-2xl bg-[#062072] p-8 sm:p-12 text-white shadow-xs">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-8 space-y-4">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#DCE8FF] bg-white/10 px-3 py-1 rounded-md inline-block m-0">
                    Loyalty Program
                </span>
                <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight m-0">
                    Kumpulkan Poin, Dapatkan Layanan Gratis!
                </h3>
                <p class="text-sm text-white/85 leading-relaxed max-w-2xl m-0 font-normal">
                    Kumpulkan poin pada setiap transaksi, tukarkan voucher potongan harga, dan nikmati berbagai reward eksklusif member.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 text-xs text-white">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white shrink-0"></span>
                        <span>Poin Transaksi otomatis di setiap pesanan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white shrink-0"></span>
                        <span>Voucher & potongan harga penukaran poin</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white shrink-0"></span>
                        <span>Promo Member khusus pelanggan terdaftar</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white shrink-0"></span>
                        <span>Reward & prioritas layanan bagi member setia</span>
                    </div>
                </div>

                <div class="pt-3">
                    @guest
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-semibold text-[#062072] bg-white hover:bg-neutral-100 transition-colors no-underline shadow-xs">
                            Daftar Member Sekarang
                        </a>
                    @else
                        @if (! $userMembership)
                            <form method="POST" action="{{ route('customer.member.register') }}" class="inline-block m-0">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-semibold text-[#062072] bg-white hover:bg-neutral-100 transition-colors cursor-pointer border-0 shadow-xs">
                                    Daftar Member Sekarang
                                </button>
                            </form>
                        @else
                            <a href="{{ route('customer.member') }}"
                                class="inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-semibold text-[#062072] bg-white hover:bg-neutral-100 transition-colors no-underline shadow-xs">
                                Lihat Member & Poin Saya
                            </a>
                        @endif
                    @endguest
                </div>
            </div>

            {{-- Kartu Digital --}}
            <div class="lg:col-span-4">
                <div class="rounded-xl border border-white/20 bg-white p-5 shadow-xs space-y-4 text-[#101828]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-[#667085] uppercase tracking-wider">Kartu Member</span>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-[#DCE8FF]/60 text-[#062072]">
                            {{ $userMembership ? 'Aktif' : 'Pratinjau' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] text-[#667085] block uppercase tracking-wider">Kode Member</span>
                        <span class="font-sans text-base font-bold tracking-wide text-[#101828]">
                            {{ $userMembership->member_code ?? 'MBR-PELANGGAN' }}
                        </span>
                    </div>
                    <div class="pt-3 border-t border-[#E4E7EC] flex items-center justify-between text-xs">
                        <div>
                            <span class="text-[10px] text-[#667085] block">Nama Pelanggan</span>
                            <span class="font-medium text-[#101828]">{{ auth()->user()?->name ?? 'Pelanggan Setia' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-[#667085] block">Poin Tersedia</span>
                            <span class="font-bold text-[#062072]">{{ number_format($userMembership?->loyaltyPoint?->total_points ?? 10) }} Poin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
