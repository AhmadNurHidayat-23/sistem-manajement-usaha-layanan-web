<form method="GET" action="{{ route('members.index') }}" class="flex flex-wrap items-center gap-2.5">
    <!-- Pencarian member -->
    <div class="flex-1 min-w-[220px]">
        <div class="relative">
            <input type="text" id="f_q" name="q" value="{{ request('q') }}" placeholder="Cari kode member, nama, telepon, atau email..." class="w-full h-10 pl-9 pr-3.5 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
            <x-icons.icon name="search" class="w-4 h-4 text-[#667085] absolute left-3 top-3 pointer-events-none" />
        </div>
    </div>

    <!-- Filter status keanggotaan -->
    <div class="w-full sm:w-44 shrink-0">
        <select id="f_status" name="status" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
            <option value="">Semua Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
    </div>

    <!-- Tombol filter & reset -->
    <div class="flex items-center gap-2 shrink-0">
        <button type="submit" class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs whitespace-nowrap">
            <x-icons.icon name="search" class="w-3.5 h-3.5 shrink-0" />
            <span>Filter</span>
        </button>
        <a href="{{ route('members.index') }}" class="h-10 w-10 bg-white border border-[#D0D5DD] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg transition-colors flex items-center justify-center cursor-pointer shadow-2xs shrink-0" title="Reset Filter">
            <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
        </a>
    </div>
</form>
