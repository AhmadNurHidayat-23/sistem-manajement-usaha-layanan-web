<!-- Skema Komisi Layanan (KRY-F-003) -->
<x-cards.card title="Daftar Skema Komisi Layanan" subtitle="Daftar aturan persentase atau nominal komisi per layanan pada katalog">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[#E4E7EC]">
        <div>
            <p class="text-xs text-[#667085] m-0">
                Skema komisi ditentukan pada masing-masing layanan. Komisi akan otomatis dihitung ke karyawan yang bertugas saat transaksi diselesaikan.
            </p>
        </div>
        @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
            <div class="shrink-0">
                <a href="{{ route('catalog.services.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#F5F8FC] hover:bg-[#EBF2FE] border border-[#D0D5DD] hover:border-[#062072] text-[#062072] rounded-lg text-xs font-semibold transition-colors shadow-2xs">
                    <x-icons.icon name="service" class="w-3.5 h-3.5 shrink-0" />
                    <span>Buka Katalog Layanan</span>
                </a>
            </div>
        @endif
    </div>

    @if ($services->isEmpty())
        <div class="py-12 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <p class="text-sm font-semibold text-[#101828] m-0">Tidak ada layanan aktif</p>
            <p class="text-xs text-[#667085] mt-1 m-0">Belum ada katalog layanan aktif yang dapat dikonfigurasi skema komisinya.</p>
        </div>
    @else
        {{-- Tampilan Mobile Cards (md:hidden) --}}
        <div class="block md:hidden divide-y divide-[#E4E7EC] -mx-4 -my-3 sm:-mx-6">
            @foreach ($services as $svc)
                @php
                    $commType = $svc->commission_type?->value ?? (string) $svc->commission_type;
                    $commVal = (float) ($svc->commission_value ?? 0);
                    $estimatedNominal = 0;
                    if ($commType === 'percentage' && $commVal > 0) {
                        $estimatedNominal = ($svc->price * $commVal) / 100;
                    } elseif ($commType === 'fixed' && $commVal > 0) {
                        $estimatedNominal = $commVal;
                    }
                @endphp
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <h4 class="font-bold text-[#101828] text-sm block truncate m-0">{{ $svc->name }}</h4>
                            <span class="text-xs text-[#667085] block truncate">
                                {{ $svc->category?->name ?? 'Tanpa Kategori' }} &bull; {{ $svc->branch?->name ?? 'Semua Cabang' }}
                            </span>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="text-xs font-bold text-[#101828] block">Rp {{ number_format($svc->price, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-[#667085] block">/ {{ $svc->unit ?? 'sesi' }}</span>
                        </div>
                    </div>

                    <div class="bg-[#F8FAFC] p-3 rounded-lg border border-[#E4E7EC] space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[#667085]">Tipe Skema:</span>
                            @if ($commType === 'percentage')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-[#062072]">
                                    Persentase ({{ $commVal }}%)
                                </span>
                            @elseif ($commType === 'fixed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                    Nominal Tetap (Rp {{ number_format($commVal, 0, ',', '.') }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-500">
                                    Belum Diatur
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#667085]">Estimasi Komisi:</span>
                            @if ($estimatedNominal > 0)
                                <span class="font-bold text-[#062072]">Rp {{ number_format($estimatedNominal, 0, ',', '.') }}</span>
                            @else
                                <span class="text-slate-400 italic">Rp 0</span>
                            @endif
                        </div>
                    </div>

                    @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
                        <div class="pt-1">
                            <a href="{{ route('catalog.services.edit', $svc) }}" class="w-full h-8.5 px-3 bg-white hover:bg-[#F5F8FC] border border-[#D0D5DD] hover:border-[#062072] text-[#062072] rounded-lg text-xs font-semibold transition-colors flex items-center justify-center gap-1.5 shadow-2xs">
                                <x-icons.icon name="edit" class="w-3.5 h-3.5" />
                                <span>Atur Skema</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Tampilan Desktop Table (hidden md:block) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-[13px] text-[#101828]">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] uppercase text-xs tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3">Layanan</th>
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Harga Layanan</th>
                        <th class="px-4 py-3">Bentuk Skema</th>
                        <th class="px-4 py-3">Nilai Skema</th>
                        <th class="px-4 py-3">Estimasi Komisi</th>
                        @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
                            <th class="px-4 py-3 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($services as $svc)
                        @php
                            $commType = $svc->commission_type?->value ?? (string) $svc->commission_type;
                            $commVal = (float) ($svc->commission_value ?? 0);
                            $estimatedNominal = 0;
                            if ($commType === 'percentage' && $commVal > 0) {
                                $estimatedNominal = ($svc->price * $commVal) / 100;
                            } elseif ($commType === 'fixed' && $commVal > 0) {
                                $estimatedNominal = $commVal;
                            }
                        @endphp
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-bold text-[#101828] block">{{ $svc->name }}</span>
                                <span class="text-xs text-[#667085] block">{{ $svc->category?->name ?? 'Tanpa Kategori' }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-[#667085]">
                                {{ $svc->branch?->name ?? 'Semua Cabang' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-[#101828]">Rp {{ number_format($svc->price, 0, ',', '.') }}</span>
                                <span class="text-[11px] text-[#667085]">/ {{ $svc->unit ?? 'sesi' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($commType === 'percentage')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-[#062072]">
                                        Persentase
                                    </span>
                                @elseif ($commType === 'fixed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700">
                                        Nominal Tetap
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-500">
                                        Belum Diatur
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium">
                                @if ($commType === 'percentage')
                                    <span class="text-[#101828]">{{ $commVal }}%</span>
                                @elseif ($commType === 'fixed')
                                    <span class="text-[#101828]">Rp {{ number_format($commVal, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-bold text-[#062072]">
                                @if ($estimatedNominal > 0)
                                    Rp {{ number_format($estimatedNominal, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-400 font-normal">Rp 0</span>
                                @endif
                            </td>
                            @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('catalog.services.edit', $svc) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-white hover:bg-[#F5F8FC] border border-[#D0D5DD] hover:border-[#062072] text-[#062072] rounded-lg text-xs font-semibold transition-colors shadow-2xs">
                                        <x-icons.icon name="edit" class="w-3.5 h-3.5" />
                                        <span>Atur Skema</span>
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-cards.card>
