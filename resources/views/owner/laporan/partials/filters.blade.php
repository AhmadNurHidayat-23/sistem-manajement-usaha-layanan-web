@php
    $isOwner = auth()->user()?->role?->name === 'owner';
    $currentPeriod = $report['period'] ?? 'this_month';
    $isCustom = ($currentPeriod === 'custom');
    $currentBranchId = $report['branch_id'] ?? null;
    $customFilters = $report['custom_filters'] ?? [];
    $selectedDay = strtolower((string) ($customFilters['day'] ?? request('day', '')));
    $selectedDate = (string) ($customFilters['date'] ?? request('date', ''));
    $selectedMonth = (string) ($customFilters['month'] ?? request('month', ''));
    $selectedYear = (string) ($customFilters['year'] ?? request('year', ''));

    $daysList = [
        'senin' => 'Senin',
        'selasa' => 'Selasa',
        'rabu' => 'Rabu',
        'kamis' => 'Kamis',
        'jumat' => 'Jumat',
        'sabtu' => 'Sabtu',
        'minggu' => 'Minggu',
    ];

    $monthsList = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $availableYears = $report['available_years'] ?? [(int) date('Y')];
    $hasActiveCustomCriteria = $isCustom && ($selectedDay !== '' || $selectedDate !== '' || $selectedMonth !== '' || $selectedYear !== '');
@endphp

<!-- Filter Laporan: Compact Single Row Filter Bar -->
<div class="bg-white rounded-xl border border-[#E7ECF3] shadow-2xs px-4 sm:px-5 py-3 sm:py-3.5">
    <!-- Baris Utama: Desktop 1 Baris Horizontal (Kiri: Tombol Periode, Kanan: Info Cabang & Periode Aktif) -->
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
        <!-- Sisi Kiri: Pilihan Periode Preset (Native GET Links) & Kustom -->
        <div class="flex flex-wrap items-center gap-2 min-w-0">
            <span class="text-xs font-semibold text-[#6B7280] uppercase tracking-wider shrink-0 mr-1">Periode:</span>
            <div class="inline-flex flex-wrap items-center gap-1.5" role="group" aria-label="Pilihan Periode">
                <a href="{{ route('reports.index', array_filter(['period' => 'today', 'branch_id' => $currentBranchId])) }}"
                    class="filter-period-btn h-9 px-3 sm:px-3.5 rounded-lg text-xs font-semibold transition-colors inline-flex items-center justify-center select-none {{ $currentPeriod === 'today' ? 'bg-[#062072] text-white border border-[#062072] shadow-2xs' : 'bg-[#EAF2FF] text-[#062072] border border-[#C2D8FC] hover:bg-[#D8E6FD]' }}"
                    data-period="today">
                    Hari Ini
                </a>
                <a href="{{ route('reports.index', array_filter(['period' => 'this_week', 'branch_id' => $currentBranchId])) }}"
                    class="filter-period-btn h-9 px-3 sm:px-3.5 rounded-lg text-xs font-semibold transition-colors inline-flex items-center justify-center select-none {{ $currentPeriod === 'this_week' ? 'bg-[#062072] text-white border border-[#062072] shadow-2xs' : 'bg-[#EAF2FF] text-[#062072] border border-[#C2D8FC] hover:bg-[#D8E6FD]' }}"
                    data-period="this_week">
                    Minggu Ini
                </a>
                <a href="{{ route('reports.index', array_filter(['period' => 'this_month', 'branch_id' => $currentBranchId])) }}"
                    class="filter-period-btn h-9 px-3 sm:px-3.5 rounded-lg text-xs font-semibold transition-colors inline-flex items-center justify-center select-none {{ $currentPeriod === 'this_month' ? 'bg-[#062072] text-white border border-[#062072] shadow-2xs' : 'bg-[#EAF2FF] text-[#062072] border border-[#C2D8FC] hover:bg-[#D8E6FD]' }}"
                    data-period="this_month">
                    Bulan Ini
                </a>
                <a href="{{ route('reports.index', array_filter(['period' => 'this_year', 'branch_id' => $currentBranchId])) }}"
                    class="filter-period-btn h-9 px-3 sm:px-3.5 rounded-lg text-xs font-semibold transition-colors inline-flex items-center justify-center select-none {{ $currentPeriod === 'this_year' ? 'bg-[#062072] text-white border border-[#062072] shadow-2xs' : 'bg-[#EAF2FF] text-[#062072] border border-[#C2D8FC] hover:bg-[#D8E6FD]' }}"
                    data-period="this_year">
                    Tahun Ini
                </a>

                {{-- Dropdown Periode Kustom --}}
                <div class="relative inline-block" id="custom-period-dropdown-wrapper">
                    <button type="button" data-period="custom" id="btn-toggle-custom-period"
                        aria-haspopup="true"
                        aria-expanded="{{ $isCustom ? 'true' : 'false' }}"
                        class="filter-period-btn h-9 px-3 sm:px-3.5 rounded-lg text-xs font-semibold transition-colors cursor-pointer select-none inline-flex items-center justify-center gap-1.5 {{ $isCustom ? 'bg-[#062072] text-white border border-[#062072] shadow-2xs' : 'bg-[#EAF2FF] text-[#062072] border border-[#C2D8FC] hover:bg-[#D8E6FD]' }}">
                        <span>Kustom</span>
                        @if ($hasActiveCustomCriteria)
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block" title="Filter kustom spesifik aktif"></span>
                        @endif
                        <svg class="w-3.5 h-3.5 transition-transform duration-200 {{ $isCustom ? 'rotate-180' : '' }}" id="custom-dropdown-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Panel Tepat di Bawah Tombol Kustom --}}
                    <div id="report-custom-filter-dropdown"
                         class="{{ $isCustom ? '' : 'hidden' }} absolute left-0 top-full mt-2 z-40 w-[300px] xs:w-[340px] sm:w-[380px] max-w-[calc(100vw-2rem)] bg-white rounded-xl border border-[#E4E7EC] shadow-xl p-4 sm:p-4.5 space-y-3.5">
                        <div class="flex items-center justify-between pb-2 border-b border-[#F2F4F7]">
                            <span class="text-xs font-bold text-[#101828]">Filter Kustom Periode</span>
                            <span class="text-[11px] text-[#667085]">Semua pilihan opsional</span>
                        </div>

                        <form method="GET" action="{{ route('reports.index') }}" id="report-custom-filter-form" class="m-0 space-y-3">
                            <input type="hidden" name="period" value="custom">
                            @if (!empty($currentBranchId))
                                <input type="hidden" name="branch_id" value="{{ $currentBranchId }}">
                            @endif

                            <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                                <!-- 1. Hari -->
                                <div class="space-y-1">
                                    <label for="filter-custom-day" class="block text-[11px] font-semibold text-[#344054]">
                                        Hari
                                    </label>
                                    <select id="filter-custom-day" name="day"
                                        class="custom-filter-select w-full h-9 px-2.5 rounded-lg border border-[#D0D5DD] text-xs text-[#101828] bg-white focus:outline-none focus:ring-1 focus:ring-[#062072] focus:border-[#062072] transition-colors cursor-pointer">
                                        <option value="">Semua Hari</option>
                                        @foreach ($daysList as $dKey => $dLabel)
                                            <option value="{{ $dKey }}" {{ $selectedDay === $dKey ? 'selected' : '' }}>
                                                {{ $dLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 2. Tanggal -->
                                <div class="space-y-1">
                                    <label for="filter-custom-date" class="block text-[11px] font-semibold text-[#344054]">
                                        Tanggal
                                    </label>
                                    <select id="filter-custom-date" name="date"
                                        class="custom-filter-select w-full h-9 px-2.5 rounded-lg border border-[#D0D5DD] text-xs text-[#101828] bg-white focus:outline-none focus:ring-1 focus:ring-[#062072] focus:border-[#062072] transition-colors cursor-pointer">
                                        <option value="">Semua Tanggal</option>
                                        @for ($d = 1; $d <= 31; $d++)
                                            <option value="{{ $d }}" {{ $selectedDate === (string) $d ? 'selected' : '' }}>
                                                {{ $d }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <!-- 3. Bulan -->
                                <div class="space-y-1">
                                    <label for="filter-custom-month" class="block text-[11px] font-semibold text-[#344054]">
                                        Bulan
                                    </label>
                                    <select id="filter-custom-month" name="month"
                                        class="custom-filter-select w-full h-9 px-2.5 rounded-lg border border-[#D0D5DD] text-xs text-[#101828] bg-white focus:outline-none focus:ring-1 focus:ring-[#062072] focus:border-[#062072] transition-colors cursor-pointer">
                                        <option value="">Semua Bulan</option>
                                        @foreach ($monthsList as $mNum => $mLabel)
                                            <option value="{{ $mNum }}" {{ $selectedMonth === (string) $mNum ? 'selected' : '' }}>
                                                {{ $mLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 4. Tahun -->
                                <div class="space-y-1">
                                    <label for="filter-custom-year" class="block text-[11px] font-semibold text-[#344054]">
                                        Tahun
                                    </label>
                                    <select id="filter-custom-year" name="year"
                                        class="custom-filter-select w-full h-9 px-2.5 rounded-lg border border-[#D0D5DD] text-xs text-[#101828] bg-white focus:outline-none focus:ring-1 focus:ring-[#062072] focus:border-[#062072] transition-colors cursor-pointer">
                                        <option value="">Semua Tahun</option>
                                        @foreach ($availableYears as $yr)
                                            <option value="{{ $yr }}" {{ $selectedYear === (string) $yr ? 'selected' : '' }}>
                                                {{ $yr }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div id="custom-date-error" class="hidden text-xs text-rose-600 font-medium leading-snug"></div>

                            <!-- Tombol Aksi: Batal | Terapkan -->
                            <div class="pt-2 flex items-center justify-end gap-2 border-t border-[#F2F4F7]">
                                <button type="button" id="btn-cancel-custom-date"
                                    class="h-9 px-3.5 rounded-lg border border-[#D0D5DD] bg-white text-xs font-medium text-[#344054] hover:bg-neutral-50 active:scale-[0.98] transition-all cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" id="btn-apply-custom-date"
                                    class="h-9 px-4 bg-[#062072] hover:bg-[#0B3A9E] active:scale-[0.98] text-white text-xs font-semibold rounded-lg transition-all inline-flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                    <x-icons.icon name="search" class="w-3.5 h-3.5 shrink-0" />
                                    <span>Terapkan</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sisi Kanan: Pilihan Cabang (Owner) / Info Cabang (Manager) & Periode Aktif -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 sm:gap-3 w-full xl:w-auto shrink-0">
            @if ($isOwner)
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <label for="report-branch-select" class="text-xs font-semibold text-[#6B7280] uppercase tracking-wider shrink-0">Cabang:</label>
                    <select name="branch_id" id="report-branch-select"
                        class="h-9 text-xs rounded-lg border border-[#E7ECF3] px-2.5 pr-8 text-[#1F2937] focus:outline-none focus:ring-1 focus:ring-[#062072] bg-white cursor-pointer w-full sm:w-auto sm:min-w-[200px]">
                        <option value="">Semua Cabang (Laporan Gabungan)</option>
                        @foreach ($report['branches'] as $branch)
                            <option value="{{ $branch->id }}" {{ (string) $report['branch_id'] === (string) $branch->id ? 'selected' : '' }}>
                                {{ $branch->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                @if ($report['selected_branch'])
                    <span class="text-xs text-[#6B7280]">
                        Cabang: <strong class="text-[#101828] font-semibold">{{ $report['selected_branch']->display_name }}</strong>
                    </span>
                @endif
            @endif

            <div class="inline-flex items-center gap-1.5 text-xs text-[#062072] bg-[#EAF2FF] px-2.5 py-1.5 rounded-lg border border-[#C2D8FC] shrink-0 font-medium self-start sm:self-auto max-w-full">
                <span class="text-[#6B7280] font-normal shrink-0">Periode aktif:</span>
                <span class="font-semibold tabular-nums text-[#062072] truncate" title="{{ $report['formatted_period_label'] ?? ($report['formatted_start_date'] . ' – ' . $report['formatted_end_date']) }}">
                    {{ $report['formatted_period_label'] ?? ($report['formatted_start_date'] . ' – ' . $report['formatted_end_date']) }}
                </span>
            </div>
        </div>
    </div>
</div>
