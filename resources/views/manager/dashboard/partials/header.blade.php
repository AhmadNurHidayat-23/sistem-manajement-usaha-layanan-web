<!-- Identitas usaha & heading Dashboard Manajer (teks biasa tanpa card besar) -->
<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 pb-1">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-[#101828] tracking-tight m-0">
            Dashboard Manajer
        </h1>
        <div class="flex flex-wrap items-center gap-2 mt-1">
            <span class="text-sm sm:text-base font-semibold text-[#1F2937]">
                {{ $branch?->display_name ?? 'Cabang Belum Ditugaskan' }}
            </span>
            @if ($branch)
                <x-badges.status :status="$branch->is_active" />
            @endif
        </div>
        @if ($branch?->address || $branch?->phone)
            <p class="text-xs text-[#6B7280] m-0 mt-0.5 flex flex-wrap items-center gap-1.5">
                @if ($branch?->address)
                    <span>{{ $branch->address }}</span>
                @endif
                @if ($branch?->phone)
                    <span>&bull; {{ $branch->phone }}</span>
                @endif
            </p>
        @endif
    </div>
</div>

@if (! $branch)
    <div class="p-3.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs">
        <strong>Perhatian:</strong> Akun Anda belum terdaftar mengelola cabang manapun. Hubungi Owner untuk menautkan cabang ke akun Manager Anda.
    </div>
@endif

<!-- Peringatan stok bahan -->
@if (($inventory_summary['total_warning'] ?? 0) > 0)
    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-amber-900">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-amber-100 text-amber-700 shrink-0">
                <x-icons.icon name="alert-triangle" class="w-5 h-5" />
            </div>
            <div>
                <h4 class="text-xs font-bold m-0 text-amber-950">Peringatan Ketersediaan Stok Bahan</h4>
                <p class="text-xs text-amber-800 m-0 mt-0.5">
                    Terdapat <strong>{{ $inventory_summary['low_stock_count'] }} bahan menipis</strong> dan <strong>{{ $inventory_summary['out_of_stock_count'] }} bahan habis</strong> di cabang Anda.
                </p>
            </div>
        </div>
        <a
            href="{{ route('inventori.index') }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-semibold rounded-lg shrink-0 transition-colors shadow-xs"
        >
            Kelola Inventori
            <x-icons.icon name="arrow-right" class="w-3.5 h-3.5" />
        </a>
    </div>
@endif
