<!-- Right Column: Cart & Checkout Form -->
<div class="bg-white border border-[#E4E7EC] rounded-xl shadow-sm overflow-hidden sticky top-6">
    <div class="p-4 border-b border-[#E4E7EC] bg-[#F5F8FC] flex items-center justify-between">
        <h4 class="text-sm font-bold text-[#101828] m-0 flex items-center gap-2">
            <x-icons.icon name="cart" class="w-4 h-4 text-[#062072]" />
            Keranjang Transaksi
        </h4>
        <button type="button" onclick="clearCart()" class="text-[11px] font-semibold text-rose-600 hover:text-rose-800 transition-colors">
            Kosongkan
        </button>
    </div>

    <form id="pos-form" method="POST" action="{{ route('pos.transactions.store') }}">
        @csrf
        <input type="hidden" name="branch_id" value="{{ $activeBranch?->id }}">
        <input type="hidden" id="booking_id" name="booking_id" value="">
        <input type="hidden" id="order_id" name="order_id" value="">

        <!-- Customer Identity Fields -->
        <div class="p-4 border-b border-[#E4E7EC] space-y-2.5 bg-white">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-semibold text-[#101828] m-0">Data Pelanggan</label>
                <button type="button" onclick="openRegisterMemberModal()" class="text-[11px] font-bold text-[#062072] hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    + Daftar Member
                </button>
            </div>

            {{-- Verification & Check-in Banners for Booking --}}
            <div id="booking-verification-banner" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-amber-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Belum Diverifikasi (Pending)
                    </span>
                    <span class="text-[10px] bg-amber-200/70 text-amber-800 font-semibold px-2 py-0.5 rounded">Menunggu Konfirmasi</span>
                </div>
                <p class="text-[11px] text-amber-800 leading-normal m-0">
                    Pelanggan hadir di cabang? Lakukan verifikasi kehadiran (check-in) untuk mengonfirmasi booking sebelum pembayaran.
                </p>
                <button type="button" id="btn-pos-checkin" onclick="checkInCurrentBooking()" class="w-full py-1.5 px-3 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Konfirmasi Kehadiran (Check-in)</span>
                </button>
            </div>

            <div id="booking-confirmed-banner" class="hidden p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs flex items-center justify-between">
                <span class="font-bold text-emerald-800 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Booking Terverifikasi (Dikonfirmasi)
                </span>
                <span class="text-[10px] bg-emerald-100 text-emerald-800 font-semibold px-2 py-0.5 rounded">Check-in OK</span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <input type="text" id="customer_name" name="customer_name" placeholder="Nama Pelanggan (Umum)" class="w-full px-2.5 py-1.5 bg-[#F5F8FC] border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#062072]">
                </div>
                <div>
                    <input type="text" id="customer_phone" name="customer_phone" placeholder="No. HP / WA" class="w-full px-2.5 py-1.5 bg-[#F5F8FC] border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#062072]">
                </div>
            </div>
        </div>

        <!-- Cart Item List -->
        <div id="cart-container" class="p-4 max-h-56 overflow-y-auto divide-y divide-[#E4E7EC] space-y-2">
            <div id="empty-cart-msg" class="py-8 text-center text-xs text-[#667085]">
                Keranjang kosong. Pilih layanan di katalog sebelah kiri.
            </div>
        </div>

        <!-- Promo Code & Discount Section -->
        <div class="p-4 bg-[#F5F8FC] border-t border-b border-[#E4E7EC] space-y-2">
            <div class="flex items-center gap-2">
                <input type="text" id="promo_code_input" placeholder="Kode Promo / Kupon" class="flex-1 uppercase px-3 py-1.5 bg-white border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                <button type="button" onclick="applyPromoCode()" class="px-3 py-1.5 bg-[#062072] text-white hover:bg-[#0B3A9E] rounded-lg text-xs font-semibold shadow-sm transition-colors">
                    Pasang
                </button>
            </div>
            <div id="promo-status" class="text-[11px] hidden font-semibold"></div>
            <input type="hidden" id="applied_promo_code" name="promo_code" value="">
            <input type="hidden" id="applied_discount" name="discount" value="0">
        </div>

        <!-- Calculations Summary -->
        <div class="p-4 space-y-2 bg-white text-xs">
            <div class="flex justify-between text-[#667085]">
                <span>Subtotal Layanan</span>
                <span id="summary-subtotal" class="font-semibold text-[#101828]">Rp 0</span>
            </div>
            <div class="flex justify-between text-emerald-600">
                <span>Potongan Promo</span>
                <span id="summary-discount" class="font-semibold">- Rp 0</span>
            </div>
            <div class="border-t border-[#E4E7EC] pt-2 flex justify-between text-base font-black text-[#101828]">
                <span>Total Tagihan</span>
                <span id="summary-total" class="text-[#062072]">Rp 0</span>
            </div>
        </div>

        <!-- Payment Section (Modular) -->
        <div class="p-4 bg-[#F5F8FC] border-t border-[#E4E7EC] space-y-4">
            @include('kasir.pembayaran.metode')

            <!-- Submit Button -->
            <button type="submit" id="btn-submit-pos" @if(!$activeShift) disabled @endif class="w-full py-3 bg-[#062072] hover:bg-[#0B3A9E] disabled:bg-[#E4E7EC] disabled:text-[#667085] disabled:cursor-not-allowed text-white font-bold rounded-lg text-sm shadow-md transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Bayar & Cetak Struk
            </button>
        </div>
    </form>
</div>
