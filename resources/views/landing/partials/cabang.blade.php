{{-- Cabang Layanan --}}
@if (isset($availableBranches) && $availableBranches->count())
<section id="cabang" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-[#101828] dark:text-white tracking-tight m-0">
                {{ __('public.branches.title') }}
            </h2>
            <p class="text-sm text-[#667085] dark:text-[#94A3B8] mt-1.5 m-0 max-w-xl">
                {{ __('public.branches.subtitle') }}
            </p>
        </div>

        <a href="{{ route('customer.home') }}#cabang"
            class="inline-flex items-center gap-1.5 text-xs font-semibold {{ (isset($currentBranch) && $currentBranch) ? 'text-[#062072] dark:text-[#93C5FD] hover:underline' : 'text-[#667085] dark:text-[#94A3B8] hover:text-[#101828] dark:hover:text-white' }} no-underline">
            <span>{{ __('public.branches.all') }}</span>
        </a>
    </div>

    {{-- Layout Tiga Kolom Terbuka dengan Pemisah Vertikal Tipis --}}
    <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-[#E5E7EB] dark:divide-[#1E2D4A]">
        @foreach ($availableBranches as $branch)
            @php
                $isSelected = isset($currentBranch) && $currentBranch && $currentBranch->id === $branch->id;
                $serviceModeText = match($branch->service_mode) {
                    'appointment' => __('public.catalog.appointment'),
                    'drop_off' => __('public.catalog.drop_off'),
                    default => __('public.catalog.both'),
                };
                $branchCategories = $branch->services
                    ? $branch->services->map(fn($s) => $s->category?->name)->filter()->unique()->values()
                    : collect();
            @endphp
            <div class="flex flex-col justify-between py-6 md:py-2 px-0 md:px-7 lg:px-9 first:md:pl-0 last:md:pr-0">
                <div class="space-y-5">
                    {{-- Nama Cabang, Alamat & Telepon --}}
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-xl sm:text-2xl font-medium tracking-tight text-[#101828] dark:text-white m-0 leading-snug">
                                {{ $branch->display_name }}
                            </h3>
                            @if ($isSelected)
                                <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-[4px] bg-[#062072]/10 dark:bg-[#93C5FD]/10 text-[#062072] dark:text-[#93C5FD] shrink-0">
                                    {{ __('dashboard.common.active') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-[13px] text-[#475467] dark:text-[#94A3B8] leading-relaxed mt-2.5 m-0">
                            {{ $branch->address ?? __('public.branches.default_address') }}
                        </p>
                        @if ($branch->phone)
                            <p class="text-xs text-[#667085] dark:text-[#94A3B8] mt-1.5 m-0">
                                {{ __('public.branches.phone_label') }}: <span class="font-medium text-[#101828] dark:text-[#F1F5F9]">{{ $branch->phone }}</span>
                            </p>
                        @endif
                    </div>

                    {{-- Jadwal Operasional Visual (Hari & Jam Menonjol) --}}
                    <div class="space-y-1">
                        <span class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#64748B] dark:text-[#94A3B8] block">
                            {{ __('public.branches.operating_hours_title') }}
                        </span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-base sm:text-lg font-semibold tracking-tight text-[#062072] dark:text-[#93C5FD]">
                                {{ str_replace(':', '.', str_replace(' - ', ' – ', $branch->operating_hours_label)) }}
                            </span>
                        </div>
                        <p class="text-xs text-[#475467] dark:text-[#94A3B8] mt-0.5 m-0">
                            {{ __('public.branches.days_open') }}
                        </p>
                    </div>

                    {{-- Layanan Tersedia & Mode --}}
                    @if ($branchCategories->count())
                        <div class="space-y-1">
                            <span class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#64748B] dark:text-[#94A3B8] block">
                                {{ __('public.branches.available_services') }}
                            </span>
                            <p class="text-xs text-[#344054] dark:text-[#CBD5E1] leading-relaxed m-0 font-normal">
                                {{ $branchCategories->join(' · ') }}
                            </p>
                            <p class="text-[11px] text-[#64748B] dark:text-[#94A3B8] m-0 pt-0.5">
                                {{ __('public.branches.service_mode_label') }}: {{ $serviceModeText }}
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Tombol Pilih Cabang Abu-abu Sejajar --}}
                <div class="mt-auto pt-6">
                    <a href="{{ route('customer.home', ['branch_id' => $branch->id]) }}#layanan"
                        class="w-full inline-flex items-center justify-center h-10 px-4 rounded-[4px] text-xs font-semibold transition-colors no-underline text-center {{ $isSelected ? 'bg-[#062072] text-white hover:bg-[#04184F]' : 'bg-[#E5E7EB] hover:bg-[#D1D5DB] dark:bg-[#1F2937] dark:hover:bg-[#374151] text-[#111827] dark:text-[#F9FAFB]' }}">
                        {{ $isSelected ? __('public.branches.selected') : __('public.branches.select_branch') }}
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif
