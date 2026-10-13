<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1">
    <form method="GET" action="{{ route('queue.index') }}" class="flex flex-wrap items-center gap-2.5">
        <label for="queue_date" class="text-xs sm:text-[13px] font-semibold text-[#475467] shrink-0">Tanggal Tugas:</label>
        <input type="date" id="queue_date" name="date" value="{{ $date }}"
            class="h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
        @if(count($branches) > 1 && auth()->user()?->role?->name !== 'employee')
            <select name="branch_id" class="h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Cabang</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ ($filters['branch_id'] ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->display_name }}</option>
                @endforeach
            </select>
        @endif
        <button type="submit" id="btn-filter-queue" class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
            <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
            <span>Tampilkan</span>
        </button>
        <a href="{{ route('queue.index') }}" class="h-10 w-10 bg-white border border-[#D0D5DD] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg text-[13px] font-medium transition-colors flex items-center justify-center cursor-pointer shadow-2xs" title="Reset Filter">
            <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
        </a>
    </form>
</div>
