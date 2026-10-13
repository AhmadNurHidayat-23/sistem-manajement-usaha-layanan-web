<!-- Modal Tukar Poin -->
<div id="modal-redeem" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-[#E4E7EC] pb-3">
            <h3 class="text-base font-bold text-[#101828] m-0">Tukar Poin Reward</h3>
            <button type="button" onclick="document.getElementById('modal-redeem').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <x-icons.icon name="close" class="w-5 h-5" />
            </button>
        </div>

        <form method="POST" action="{{ route('members.redeem', $membership) }}" class="space-y-4">
            @csrf

            <!-- Balance Info -->
            <div class="p-3 bg-[#F5F8FC] border border-[#DCE8FF] rounded-lg flex items-center justify-between">
                <div>
                    <span class="text-[11px] text-[#667085] block">Saldo Poin Tersedia:</span>
                    <span class="text-lg font-bold text-[#062072]">{{ number_format($membership->loyaltyPoint?->total_points ?? 0) }} Poin</span>
                </div>
                <span class="text-xs font-semibold text-emerald-600">Senilai Rp {{ number_format(($membership->loyaltyPoint?->total_points ?? 0) * 1000, 0, ',', '.') }}</span>
            </div>

            <div>
                <label for="redeem_points" class="block text-xs font-semibold text-[#101828] mb-1">
                    Jumlah Poin yang Ditukarkan <span class="text-rose-500">*</span>
                </label>
                <input type="number" id="redeem_points" name="points" min="1" max="{{ $membership->loyaltyPoint?->total_points ?? 0 }}" value="10" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
            </div>

            <div>
                <label for="reward_description" class="block text-xs font-semibold text-[#101828] mb-1">
                    Keterangan Penukaran Reward <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="reward_description" name="reward_description" placeholder="Contoh: Voucher Diskon Servis Rp 20.000" required class="w-full px-3 py-2 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
            </div>

            <div class="pt-3 border-t border-[#E4E7EC] flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-redeem').classList.add('hidden')" class="px-3 py-1.5 border border-[#E4E7EC] rounded-lg text-xs font-semibold text-[#667085] hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="px-3 py-1.5 bg-[#062072] text-white rounded-lg text-xs font-semibold hover:bg-[#0B3A9E]">
                    Konfirmasi Penukaran
                </button>
            </div>
        </form>
    </div>
</div>
