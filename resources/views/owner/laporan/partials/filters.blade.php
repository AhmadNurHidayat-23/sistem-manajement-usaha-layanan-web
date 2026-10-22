@php
    $isOwner = auth()->user()?->role?->name === 'owner';
    $currentPeriod = $report['period'] ?? 'this_month';
    $isCustom = ($currentPeriod === 'custom');
    $currentBranchId = $report['branch_id'] ?? null;
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
                <button type="button" data-period="custom" id="btn-toggle-custom-period"
                    class="filter-period-btn h-9 px-3 sm:px-3.5 rounded-lg text-xs font-semibold transition-colors cursor-pointer select-none inline-flex items-center justify-center {{ $isCustom ? 'bg-[#062072] text-white border border-[#062072] shadow-2xs' : 'bg-[#EAF2FF] text-[#062072] border border-[#C2D8FC] hover:bg-[#D8E6FD]' }}">
                    Kustom
                </button>
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

            <div class="inline-flex items-center gap-1.5 text-xs text-[#062072] bg-[#EAF2FF] px-2.5 py-1.5 rounded-lg border border-[#C2D8FC] shrink-0 font-medium self-start sm:self-auto">
                <span class="text-[#6B7280] font-normal">Periode aktif:</span>
                <span class="font-semibold tabular-nums text-[#062072]">
                    {{ $report['formatted_start_date'] }} – {{ $report['formatted_end_date'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- Area Rentang Kustom (Muncul tepat di bawah baris utama saat Kustom dipilih/aktif) -->
    <form method="GET" action="{{ route('reports.index') }}" id="report-custom-filter-form"
        class="{{ $isCustom ? 'flex' : 'hidden' }} flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2.5 sm:gap-3 pt-3 mt-3 border-t border-[#E7ECF3] m-0">
        <input type="hidden" name="period" value="custom">
        @if (!empty($currentBranchId))
            <input type="hidden" name="branch_id" value="{{ $currentBranchId }}">
        @endif

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <label for="filter-start-date" class="text-xs font-medium text-[#4B5563] shrink-0 w-24 sm:w-auto">Mulai:</label>
            <input type="date" id="filter-start-date" name="start_date" value="{{ $isCustom ? ($report['start_date'] ?? '') : '' }}"
                class="flex-1 sm:flex-initial h-9 px-2.5 rounded-lg border border-[#D0D5DD] text-xs text-[#101828] bg-white focus:outline-none focus:ring-1 focus:ring-[#062072] w-full sm:w-38"
                placeholder="dd/mm/yyyy" required>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <label for="filter-end-date" class="text-xs font-medium text-[#4B5563] shrink-0 w-24 sm:w-auto">Akhir:</label>
            <input type="date" id="filter-end-date" name="end_date" value="{{ $isCustom ? ($report['end_date'] ?? '') : '' }}"
                class="flex-1 sm:flex-initial h-9 px-2.5 rounded-lg border border-[#D0D5DD] text-xs text-[#101828] bg-white focus:outline-none focus:ring-1 focus:ring-[#062072] w-full sm:w-38"
                placeholder="dd/mm/yyyy" required>
        </div>
        <button type="submit" id="btn-apply-custom-date"
            class="h-9 px-4 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-semibold rounded-lg transition-colors inline-flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs w-full sm:w-auto">
            <x-icons.icon name="search" class="w-3.5 h-3.5 shrink-0" />
            <span>Terapkan</span>
        </button>
        <span id="custom-date-error" class="text-xs text-rose-600 font-medium hidden"></span>
        @error('start_date')
            <span class="text-xs text-rose-600 font-medium">{{ $message }}</span>
        @enderror
        @error('end_date')
            <span class="text-xs text-rose-600 font-medium">{{ $message }}</span>
        @enderror
    </form>
</div>
