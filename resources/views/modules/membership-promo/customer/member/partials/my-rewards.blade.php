<!-- Right: Points Balance, Fast Redeem, & Stats -->
<div class="lg:col-span-2 space-y-4">
    <!-- Points Metric Box -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-cards.card class="p-4 bg-gradient-to-br from-amber-50/50 to-white border-amber-200">
            <p class="text-xs font-bold text-amber-800 uppercase tracking-wider m-0">Saldo Poin Saat Ini</p>
            <h3 class="text-3xl font-extrabold text-amber-600 mt-2 mb-0">{{ number_format($membership->loyaltyPoint?->total_points ?? $membership->loyaltyPoint?->points ?? 0) }}</h3>
            <p class="text-[11px] text-amber-700 font-medium mt-1 m-0">Siap ditukarkan reward</p>
        </x-cards.card>

        <x-cards.card class="p-4">
            <p class="text-xs font-medium text-[#667085] uppercase tracking-wider m-0">Total Poin Didapat</p>
            <h3 class="text-2xl font-bold text-[#101828] mt-2 mb-0">{{ number_format($membership->loyaltyPoint?->total_points ?? 0) }}</h3>
            <p class="text-[11px] text-[#667085] mt-1 m-0">Dari transaksi & bonus</p>
        </x-cards.card>

        <x-cards.card class="p-4">
            <p class="text-xs font-medium text-[#667085] uppercase tracking-wider m-0">Total Poin Digunakan</p>
            <h3 class="text-2xl font-bold text-[#062072] mt-2 mb-0">{{ number_format($membership->loyaltyPoint?->total_spent ?? 0) }}</h3>
            <p class="text-[11px] text-[#667085] mt-1 m-0">Telah ditebus menjadi reward</p>
        </x-cards.card>
    </div>

    <!-- Reward Redemption Card -->
    <x-cards.card class="p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-[#E4E7EC]">
            <div>
                <h3 class="text-sm font-bold text-[#101828] m-0">Tukar Poin Loyalitas</h3>
                <p class="text-xs text-[#667085] mt-0.5 m-0">Tukarkan poin Anda dengan potongan diskon belanja langsung saat transaksi kasir.</p>
            </div>
            <span class="text-xs font-medium text-[#062072] bg-[#DCE8FF] px-3 py-1 rounded-full">
                Saldo: {{ number_format($membership->loyaltyPoint?->total_points ?? 0) }} Poin
            </span>
        </div>

        <!-- Fast Redeem Options -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
            <!-- Option 1: 50 Pts -->
            <div class="p-3 border border-[#E4E7EC] rounded-lg hover:border-[#062072] hover:bg-[#F8F9FC] transition-colors flex flex-col justify-between">
                <div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">50 Poin</span>
                    <h4 class="text-xs font-bold text-[#101828] mt-2 m-0">Voucher Diskon Rp 10.000</h4>
                    <p class="text-[11px] text-[#667085] mt-1 m-0">Untuk minimal transaksi Rp 50.000.</p>
                </div>
                <form action="{{ route('members.redeem', $membership) }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="points" value="50">
                    <input type="hidden" name="reward_description" value="Voucher Diskon Rp 10.000">
                    <x-buttons.btn type="submit" variant="outline" class="w-full text-xs py-1" :disabled="($membership->loyaltyPoint?->total_points ?? 0) < 50">
                        Tukar 50 Poin
                    </x-buttons.btn>
                </form>
            </div>

            <!-- Option 2: 100 Pts -->
            <div class="p-3 border border-[#E4E7EC] rounded-lg hover:border-[#062072] hover:bg-[#F8F9FC] transition-colors flex flex-col justify-between">
                <div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">100 Poin</span>
                    <h4 class="text-xs font-bold text-[#101828] mt-2 m-0">Voucher Diskon Rp 25.000</h4>
                    <p class="text-[11px] text-[#667085] mt-1 m-0">Untuk minimal transaksi Rp 100.000.</p>
                </div>
                <form action="{{ route('members.redeem', $membership) }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="points" value="100">
                    <input type="hidden" name="reward_description" value="Voucher Diskon Rp 25.000">
                    <x-buttons.btn type="submit" variant="outline" class="w-full text-xs py-1" :disabled="($membership->loyaltyPoint?->total_points ?? 0) < 100">
                        Tukar 100 Poin
                    </x-buttons.btn>
                </form>
            </div>

            <!-- Option 3: 200 Pts -->
            <div class="p-3 border border-[#E4E7EC] rounded-lg hover:border-[#062072] hover:bg-[#F8F9FC] transition-colors flex flex-col justify-between">
                <div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">200 Poin</span>
                    <h4 class="text-xs font-bold text-[#101828] mt-2 m-0">Voucher Diskon Rp 60.000</h4>
                    <p class="text-[11px] text-[#667085] mt-1 m-0">Spesial paket perawatan / service lengkap.</p>
                </div>
                <form action="{{ route('members.redeem', $membership) }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="points" value="200">
                    <input type="hidden" name="reward_description" value="Voucher Diskon Rp 60.000">
                    <x-buttons.btn type="submit" variant="outline" class="w-full text-xs py-1" :disabled="($membership->loyaltyPoint?->total_points ?? 0) < 200">
                        Tukar 200 Poin
                    </x-buttons.btn>
                </form>
            </div>
        </div>
    </x-cards.card>
</div>
