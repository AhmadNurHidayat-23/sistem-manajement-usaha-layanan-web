<!-- Filter Komisi Toolbar (Compact Inline) -->
@if (! ($isSelfView ?? false))
    <form method="GET" action="{{ route('employees.commissions.index') }}" class="flex flex-wrap items-center gap-2.5">
        <!-- Filter cabang -->
        @if ($branches->count() > 1)
            <div class="w-full sm:w-40 shrink-0">
                <select id="f_branch" name="branch_id" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Filter karyawan -->
        <div class="flex-1 min-w-[180px]">
            <select id="f_emp" name="employee_id" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Karyawan</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter status -->
        <div class="w-full sm:w-36 shrink-0">
            <select id="f_status" name="status" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Telah Cair</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>

        <!-- Date Range Filters -->
        <div class="w-full sm:w-36 shrink-0">
            <input type="date" id="f_start" name="start_date" value="{{ request('start_date') }}" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
        </div>

        <!-- Submit Button -->
        <div class="flex items-center gap-2 shrink-0">
            <button type="submit" class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
                <span>Filter</span>
            </button>
            <a href="{{ route('employees.commissions.index') }}" class="h-10 w-10 bg-white border border-[#D0D5DD] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg text-[13px] font-medium transition-colors flex items-center justify-center cursor-pointer shadow-2xs" title="Reset Filter">
                <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
            </a>
        </div>
    </form>
@endif
