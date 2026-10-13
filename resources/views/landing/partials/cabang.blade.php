{{-- Cabang Layanan --}}
@if (isset($availableBranches) && $availableBranches->count())
<section id="cabang" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-[#062072] block mb-2">CABANG LAYANAN</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-[#101828] tracking-tight m-0">
                Cabang Operasional
            </h2>
            <p class="text-sm text-[#667085] mt-1.5 m-0 max-w-xl">
                Setiap cabang memiliki spesialisasi layanan dan mode operasional yang disesuaikan untuk kenyamanan Anda.
            </p>
        </div>

        @if (isset($currentBranch) && $currentBranch)
            <a href="{{ route('customer.home') }}#cabang"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#062072] hover:text-[#04184F] no-underline">
                <span>Tampilkan Semua Cabang</span>
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($availableBranches as $branch)
            @php
                $isSelected = isset($currentBranch) && $currentBranch && $currentBranch->id === $branch->id;
                $serviceModeText = match($branch->service_mode) {
                    'appointment' => 'Appointment',
                    'drop_off' => 'Drop-off',
                    'both' => 'Appointment & Drop-off',
                    default => 'Appointment & Drop-off',
                };
                $branchCategories = $branch->services
                    ? $branch->services->map(fn($s) => $s->category?->name)->filter()->unique()->values()
                    : collect();
            @endphp
            <div class="rounded-xl border {{ $isSelected ? 'border-[#062072] bg-white ring-2 ring-[#062072]/15 shadow-sm' : 'border-[#E4E7EC] bg-white hover:border-[#062072]/40 shadow-2xs' }} p-5 sm:p-6 flex flex-col justify-between transition-all h-full">
                <div class="space-y-3.5">
                    {{-- Badge Status & Jam Operasional --}}
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <span class="text-xs font-semibold {{ $isSelected ? 'text-white bg-[#062072]' : 'text-[#062072] bg-[#DCE8FF]/70' }} px-2.5 py-1 rounded-md">
                            {{ $isSelected ? 'Cabang Terpilih' : 'Cabang Resmi' }}
                        </span>
                        <div class="flex flex-col items-end text-[11px] text-[#667085]">
                            <span class="font-medium text-emerald-700">{{ $branch->operating_days_label }}</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-[#667085] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $branch->operating_hours_label }}
                            </span>
                        </div>
                    </div>

                    {{-- Nama Cabang --}}
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-[#101828] m-0 leading-snug">
                            {{ $branch->display_name }}
                        </h3>
                        <p class="text-xs text-[#667085] leading-relaxed mt-1.5 m-0">
                            {{ $branch->address ?? 'Alamat lengkap cabang operasional resmi SIMAUL.' }}
                        </p>
                    </div>

                    {{-- Kontak Cabang --}}
                    @if ($branch->phone)
                        <div class="text-xs text-[#667085] flex items-center gap-1.5 pt-0.5">
                            <svg class="w-3.5 h-3.5 text-[#062072] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span>{{ $branch->phone }}</span>
                        </div>
                    @endif

                    {{-- Layanan Yang Tersedia --}}
                    @if ($branchCategories->count())
                        <div class="pt-2 border-t border-[#F2F4F7]">
                            <span class="text-[11px] font-medium text-[#667085] block mb-1.5">Layanan Tersedia:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($branchCategories as $categoryName)
                                    <span class="inline-flex items-center text-[11px] font-medium text-[#062072] bg-[#F5F8FC] border border-[#E4E7EC] px-2 py-0.5 rounded">
                                        {{ $categoryName }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Footer Kartu: Mode & CTA --}}
                <div class="pt-4 mt-5 border-t border-[#E4E7EC] flex items-center justify-between gap-3">
                    <div class="text-[11px] text-[#667085]">
                        <span class="block text-[10px] uppercase tracking-wider text-[#98A2B3]">Mode</span>
                        <strong class="text-[#101828] font-semibold text-xs">{{ $serviceModeText }}</strong>
                    </div>

                    <a href="{{ route('customer.home', ['branch_id' => $branch->id]) }}#layanan"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors no-underline shrink-0 {{ $isSelected ? 'bg-[#062072] text-white hover:bg-[#04184F]' : 'border border-[#E4E7EC] text-[#101828] hover:bg-[#F5F8FC] hover:border-[#062072]/30' }}">
                        {{ $isSelected ? 'Lihat Layanan' : 'Pilih Cabang' }}
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif
