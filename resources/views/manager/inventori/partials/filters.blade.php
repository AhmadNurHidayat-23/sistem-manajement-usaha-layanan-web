<form method="GET" action="{{ route('inventori.index') }}" class="flex flex-wrap items-center gap-2.5">
    <div class="flex-1 min-w-[200px]">
        <input
            type="text"
            name="search"
            id="search"
            value="{{ $filters['search'] ?? '' }}"
            placeholder="Cari nama atau kode bahan..."
            class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs"
        >
    </div>

    @if ($branches->isNotEmpty())
        <div class="w-full sm:w-44 shrink-0">
            <select
                name="branch_id"
                id="branch_id"
                class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs"
            >
                <option value="">Semua Cabang</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" {{ (string) ($filters['branch_id'] ?? '') === (string) $branch->id ? 'selected' : '' }}>
                        {{ $branch->display_name }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="w-full sm:w-40 shrink-0">
        <select
            name="status"
            id="status"
            class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs"
        >
            <option value="">Semua Status</option>
            <option value="tersedia" {{ ($filters['status'] ?? '') === 'tersedia' ? 'selected' : '' }}>Tersedia (Aman)</option>
            <option value="menipis" {{ ($filters['status'] ?? '') === 'menipis' ? 'selected' : '' }}>Menipis (&le; Min)</option>
            <option value="habis" {{ ($filters['status'] ?? '') === 'habis' ? 'selected' : '' }}>Habis (Kosong)</option>
        </select>
    </div>

    <div class="flex items-center gap-2 shrink-0">
        <button
            type="submit"
            class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
        >
            <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
            <span>Filter</span>
        </button>
        <a
            href="{{ route('inventori.index') }}"
            class="h-10 w-10 bg-white border border-[#D0D5DD] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg text-[13px] font-medium transition-colors flex items-center justify-center cursor-pointer shadow-2xs"
            title="Reset Filter"
        >
            <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
        </a>
    </div>
</form>
