<form method="GET" action="{{ route('notifikasi.index') }}" class="flex flex-wrap items-center gap-2.5" id="form-filter-notifikasi">
    <!-- Cari Pesan (Paling Fleksibel / Paling Lebar) -->
    <div class="flex-1 min-w-[200px]">
        <div class="relative">
            <input type="text" id="q" name="q" value="{{ request('q') }}"
                placeholder="Cari nomor atau isi pesan..."
                class="w-full h-10 pl-9 pr-3.5 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
            <x-icons.icon name="search" class="w-4 h-4 text-[#667085] absolute left-3 top-3 pointer-events-none" />
        </div>
    </div>

    <!-- Tipe Notifikasi -->
    <div class="w-full sm:w-44 shrink-0">
        <select id="type" name="type" onchange="this.form.submit()"
            class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
            <option value="">Semua Tipe</option>
            @foreach ($typeList as $type)
                <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', ucfirst($type)) }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Status Pengiriman -->
    <div class="w-full sm:w-40 shrink-0">
        <select id="status" name="status" onchange="this.form.submit()"
            class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
            <option value="">Semua Status</option>
            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Terkirim</option>
            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        </select>
    </div>

    <!-- Tombol Aksi (Compact & Square Reset) -->
    <div class="flex items-center gap-2 shrink-0">
        <button type="submit"
            class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white text-[13px] font-semibold rounded-lg transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs whitespace-nowrap">
            <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
            <span>Filter</span>
        </button>
        <a href="{{ route('notifikasi.index') }}"
            class="h-10 w-10 bg-white border border-[#D0D5DD] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg transition-colors flex items-center justify-center cursor-pointer shadow-2xs shrink-0" title="Reset Filter">
            <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
        </a>
    </div>
</form>
