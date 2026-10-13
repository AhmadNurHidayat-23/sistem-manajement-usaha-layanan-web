<!-- Filter data karyawan -->
<x-cards.card class="p-3.5 sm:px-5 sm:py-3.5">
    <form method="GET" action="{{ route('employees.index') }}" class="flex flex-col lg:flex-row lg:items-end gap-3">
        <!-- Pencarian nama, telepon, email -->
        <div class="flex-1 min-w-[200px]">
            <label for="filter_q" class="block text-[13px] font-medium text-[#101828] mb-1">Cari Karyawan</label>
            <div class="relative">
                <input type="text" id="filter_q" name="q" value="{{ request('q') }}" placeholder="Cari nama, no. telepon, atau email..." class="w-full h-10 pl-9 pr-3.5 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                <x-icons.icon name="search" class="w-4 h-4 text-[#667085] absolute left-3 top-3" />
            </div>
        </div>

        <!-- Filter cabang -->
        @if ($branches->count() > 1)
            <div class="w-full sm:w-48 shrink-0">
                <label for="filter_branch" class="block text-[13px] font-medium text-[#101828] mb-1">Cabang</label>
                <select id="filter_branch" name="branch_id" class="w-full h-10 px-3 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Filter status aktif -->
        <div class="w-full sm:w-36 shrink-0">
            <label for="filter_status" class="block text-[13px] font-medium text-[#101828] mb-1">Status</label>
            <select id="filter_status" name="status" class="w-full h-10 px-3 bg-white border border-[#E4E7EC] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        <!-- Tombol aksi -->
        <div class="flex items-center gap-2 shrink-0">
            <button type="submit" class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
                <span>Filter</span>
            </button>
            <a href="{{ route('employees.index') }}" class="h-10 w-10 bg-white border border-[#E4E7EC] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg text-[13px] font-medium transition-colors flex items-center justify-center cursor-pointer shadow-2xs" title="Reset Filter">
                <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
            </a>
        </div>
    </form>
</x-cards.card>
