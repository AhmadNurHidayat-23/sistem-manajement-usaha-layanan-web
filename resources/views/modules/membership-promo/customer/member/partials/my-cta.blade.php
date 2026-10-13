<!-- Customer Not Yet a Member CTA Card -->
<x-cards.card class="p-8 border-2 border-dashed border-[#0B3A9E]/30 text-center bg-gradient-to-b from-[#F8F9FC] to-white">
    <div class="max-w-xl mx-auto space-y-5">
        <div class="w-16 h-16 rounded-2xl bg-[#DCE8FF] text-[#062072] flex items-center justify-center mx-auto shadow-sm">
            <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <h3 class="text-xl font-bold text-[#101828] mb-2">Gabung Member & Nikmati Reward Eksklusif!</h3>
            <p class="text-xs text-[#667085] leading-relaxed">
                Anda belum mengaktifkan keanggotaan. Dapatkan poin loyalitas otomatis setiap kali bertransaksi di semua cabang kami, klaim promo khusus member, serta tukarkan poin dengan diskon dan reward spesial.
            </p>
        </div>

        <!-- Benefits grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-left pt-2">
            <div class="p-3.5 bg-white rounded-xl border border-[#E4E7EC] shadow-2xs hover:border-[#062072] transition-colors">
                <div class="w-8 h-8 rounded-lg bg-[#DCE8FF] text-[#062072] flex items-center justify-center mb-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h4 class="text-xs font-bold text-[#101828] m-0">1 Poin / Rp 10.000</h4>
                <p class="text-[11px] text-[#667085] mt-1 m-0">Akumulasi poin otomatis dari setiap transaksi kasir & booking.</p>
            </div>

            <div class="p-3.5 bg-white rounded-xl border border-[#E4E7EC] shadow-2xs hover:border-[#062072] transition-colors">
                <div class="w-8 h-8 rounded-lg bg-[#DCE8FF] text-[#062072] flex items-center justify-center mb-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
                <h4 class="text-xs font-bold text-[#101828] m-0">Bonus 10 Poin</h4>
                <p class="text-[11px] text-[#667085] mt-1 m-0">Langsung terima saldo poin selamat datang tanpa syarat.</p>
            </div>

            <div class="p-3.5 bg-white rounded-xl border border-[#E4E7EC] shadow-2xs hover:border-[#062072] transition-colors">
                <div class="w-8 h-8 rounded-lg bg-[#DCE8FF] text-[#062072] flex items-center justify-center mb-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <h4 class="text-xs font-bold text-[#101828] m-0">Tukar Hadiah</h4>
                <p class="text-[11px] text-[#667085] mt-1 m-0">Tukar saldo poin kapan saja dengan potongan harga layanan.</p>
            </div>
        </div>

        <!-- Registration Trigger -->
        <form action="{{ route('members.register-self') }}" method="POST" class="pt-3">
            @csrf
            <x-buttons.btn type="submit" variant="primary" class="px-6 py-2.5 text-sm gap-2 shadow-md">
                <x-icons.icon name="check" class="w-5 h-5" />
                Aktifkan Kartu Member Saya Sekarang (Gratis)
            </x-buttons.btn>
        </form>
    </div>
</x-cards.card>
