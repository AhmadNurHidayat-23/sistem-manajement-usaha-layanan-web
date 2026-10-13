<!-- Filter riwayat transaksi (Compact Inline Toolbar) -->
<form method="GET" action="{{ route('pos.transactions.index') }}" class="flex flex-wrap items-center gap-2.5">
    <!-- Cari kode / pelanggan -->
    <div class="flex-1 min-w-[180px]">
        <input type="text" id="filter_q" name="q" value="{{ request('q') }}" placeholder="Kode TRX / Nama Pelanggan..." class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
    </div>

    @if ($branches->count() > 1)
        <div class="w-full sm:w-40 shrink-0">
            <select id="filter_branch" name="branch_id" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Cabang</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="w-full sm:w-36 shrink-0">
        <select id="filter_payment" name="payment_method" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
            <option value="">Metode Bayar</option>
            <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tunai</option>
            <option value="qris" {{ request('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
            <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer</option>
            <option value="e_wallet" {{ in_array(request('payment_method'), ['e_wallet', 'e-wallet'], true) ? 'selected' : '' }}>E-Wallet</option>
            <option value="virtual_account" {{ request('payment_method') === 'virtual_account' ? 'selected' : '' }}>Virtual Account</option>
        </select>
    </div>

    <div class="w-full sm:w-36 shrink-0">
        <input type="date" id="filter_start" name="start_date" value="{{ request('start_date') }}" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
    </div>

    <div class="w-full sm:w-36 shrink-0">
        <input type="date" id="filter_end" name="end_date" value="{{ request('end_date') }}" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
    </div>

    <div class="flex items-center gap-2 shrink-0">
        <button type="submit" class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
            <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
            <span>Filter</span>
        </button>
        <a href="{{ route('pos.transactions.index') }}" class="h-10 w-10 bg-white border border-[#D0D5DD] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg text-[13px] font-medium transition-colors flex items-center justify-center cursor-pointer shadow-2xs" title="Reset Filter">
            <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
        </a>
    </div>
</form>
