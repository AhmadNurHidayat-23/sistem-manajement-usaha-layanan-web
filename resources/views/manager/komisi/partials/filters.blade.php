<!-- Filter Komisi Toolbar (Compact Inline & Responsive) -->
@if (! ($isSelfView ?? false))
    <form method="GET" action="{{ route('employees.commissions.index') }}" class="flex flex-wrap items-center gap-2.5">
        <input type="hidden" name="tab" value="{{ $activeTab ?? request('tab', 'history') }}">

        <!-- Filter cabang -->
        @if ($branches->count() > 1)
            <div class="w-full sm:w-44 shrink-0">
                <label for="f_branch" class="sr-only">Cabang</label>
                <select id="f_branch" name="branch_id" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-hidden focus:ring-1 focus:ring-[#062072] shadow-2xs">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Filter karyawan -->
        <div class="w-full sm:flex-1 sm:min-w-[180px]">
            <label for="f_emp" class="sr-only">Karyawan</label>
            <select id="f_emp" name="employee_id" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-hidden focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Karyawan</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter status -->
        <div class="w-full sm:w-36 shrink-0">
            <label for="f_status" class="sr-only">Status</label>
            <select id="f_status" name="status" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-hidden focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Telah Cair</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>

        <!-- Date Range Filters -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <div class="flex-1 sm:w-36 shrink-0">
                <label for="f_start" class="block sm:hidden text-xs text-[#667085] mb-1">Mulai</label>
                <input type="date" id="f_start" name="start_date" value="{{ request('start_date') }}" title="Tanggal Mulai" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-hidden focus:ring-1 focus:ring-[#062072] shadow-2xs">
            </div>
            <span class="text-xs text-[#667085] shrink-0 self-center hidden sm:inline">s/d</span>
            <div class="flex-1 sm:w-36 shrink-0">
                <label for="f_end" class="block sm:hidden text-xs text-[#667085] mb-1">Sampai</label>
                <input type="date" id="f_end" name="end_date" value="{{ request('end_date') }}" title="Tanggal Akhir" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-hidden focus:ring-1 focus:ring-[#062072] shadow-2xs">
            </div>
        </div>

        <!-- Submit & Reset Buttons -->
        <div class="flex items-center gap-2 w-full sm:w-auto shrink-0 mt-1 sm:mt-0">
            <button type="submit" class="flex-1 sm:flex-initial h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
                <span>Filter</span>
            </button>
            <a href="{{ route('employees.commissions.index', ['tab' => 'history']) }}" class="h-10 w-10 bg-white border border-[#D0D5DD] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg text-[13px] font-medium transition-colors flex items-center justify-center cursor-pointer shadow-2xs" title="Reset Filter" aria-label="Reset Filter">
                <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
            </a>
        </div>
    </form>
@endif

