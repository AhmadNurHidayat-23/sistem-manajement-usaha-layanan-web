<!-- Filter Shift Toolbar (Compact Inline) -->
@if (! ($isSelfView ?? false))
    <div class="space-y-3">
        <!-- Quick Scope Tabs: Hari Ini, Mendatang, Riwayat -->
        <div class="flex flex-wrap items-center gap-2">
            @php
                $currentScope = request('scope');
                $scopes = [
                    'today' => 'HARI INI',
                    'upcoming' => 'MENDATANG',
                    'history' => 'RIWAYAT',
                    '' => 'SEMUA JADWAL',
                ];
            @endphp
            @foreach ($scopes as $key => $label)
                @php
                    $isActive = ($key === '' && !request()->filled('scope') && !request()->filled('date')) || ($key !== '' && $currentScope === $key);
                    $scopeParams = request()->except(['scope', 'page', 'date']);
                    if ($key !== '') {
                        $scopeParams['scope'] = $key;
                    }
                @endphp
                <a href="{{ route('employees.shifts.index', $scopeParams) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all shadow-2xs {{ $isActive ? 'bg-[#062072] text-white' : 'bg-[#EAF2FF] text-[#062072] hover:bg-[#D5E5FD] border border-[#C2D8FC]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('employees.shifts.index') }}" class="flex flex-wrap items-center gap-2.5">
            <input type="hidden" name="scope" value="{{ request('scope') }}">
            <!-- Date Filter -->
            <div class="w-full sm:w-40 shrink-0">
                <input type="date" id="filter_date" name="date" value="{{ request('date') }}" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
            </div>

        <!-- Filter cabang -->
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

        <!-- Filter karyawan -->
        <div class="flex-1 min-w-[180px]">
            <select id="filter_emp" name="employee_id" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Karyawan</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter status -->
        <div class="w-full sm:w-36 shrink-0">
            <select id="filter_status" name="status" class="w-full h-10 px-3 bg-white border border-[#D0D5DD] rounded-lg text-[13px] text-[#101828] focus:outline-none focus:ring-1 focus:ring-[#062072] shadow-2xs">
                <option value="">Semua Status</option>
                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Hadir</option>
                <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat</option>
                <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absen</option>
                <option value="off" {{ request('status') === 'off' ? 'selected' : '' }}>Libur</option>
            </select>
        </div>

        <!-- Action Button -->
        <div class="flex items-center gap-2 shrink-0">
            <button type="submit" class="h-10 px-4 bg-[#062072] hover:bg-[#04184F] text-white rounded-lg text-[13px] font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                <x-icons.icon name="search" class="w-4 h-4 shrink-0" />
                <span>Filter</span>
            </button>
            <a href="{{ route('employees.shifts.index') }}" class="h-10 w-10 bg-white border border-[#D0D5DD] hover:bg-[#F5F8FC] text-[#667085] hover:text-[#101828] rounded-lg text-[13px] font-medium transition-colors flex items-center justify-center cursor-pointer shadow-2xs" title="Reset Filter">
                <x-icons.icon name="refresh" class="w-4 h-4 shrink-0" />
            </a>
        </div>
    </form>
    </div>
@endif
