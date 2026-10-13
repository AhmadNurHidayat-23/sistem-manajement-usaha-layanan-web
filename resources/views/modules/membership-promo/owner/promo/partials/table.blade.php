<!-- Promo Table Card -->
<x-cards.card class="overflow-hidden">
    <div class="overflow-x-auto w-full">
        <table class="w-full min-w-[860px] text-left border-collapse text-[13px]">
            <thead>
                <tr class="bg-[#F8F9FC] border-b border-[#E4E7EC] text-[#667085] font-semibold text-xs uppercase tracking-wider">
                    <th class="py-3 px-4">Kode Promo</th>
                    <th class="py-3 px-4">Nama Promo</th>
                    <th class="py-3 px-4">Cabang</th>
                    <th class="py-3 px-4">Nilai Diskon</th>
                    <th class="py-3 px-4 text-center">Kuota & Terpakai</th>
                    <th class="py-3 px-4">Periode Berlaku</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC] text-[#101828]">
                @forelse ($promotions as $promo)
                    @php
                        $isExpired = $promo->end_date->isPast();
                        $isPercentage = $promo->discount_type->value === 'percentage';
                        $statusVal = $promo->status->value;
                        $promoPayload = [
                            'id' => $promo->id,
                            'code' => $promo->code,
                            'name' => $promo->name,
                            'branch_id' => $promo->branch_id,
                            'discount_type' => $promo->discount_type->value,
                            'discount_value' => (float) $promo->discount_value,
                            'quota' => $promo->quota,
                            'start_date' => $promo->start_date?->format('Y-m-d'),
                            'end_date' => $promo->end_date?->format('Y-m-d'),
                            'status' => $promo->status->value,
                        ];
                    @endphp
                    <tr class="hover:bg-[#F8F9FC]/60 transition-colors">
                        <!-- Kode Promo -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-[#DCE8FF] text-[#062072] border border-[#0B3A9E]/20">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                {{ $promo->code }}
                            </span>
                        </td>

                        <!-- Nama Promo -->
                        <td class="py-2.5 px-3">
                            <div class="font-bold text-[#101828] whitespace-nowrap">{{ $promo->name }}</div>
                        </td>

                        <!-- Cabang -->
                        <td class="py-2.5 px-3 whitespace-nowrap">
                            @if ($promo->branch)
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#101828] bg-slate-100 px-2 py-0.5 rounded">
                                    <svg class="w-3 h-3 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    {{ $promo->branch->display_name }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                    Semua Cabang (Global)
                                </span>
                            @endif
                        </td>

                        <!-- Nilai Diskon -->
                        <td class="py-2.5 px-3 whitespace-nowrap">
                            @if ($isPercentage)
                                <span class="font-bold text-[#062072] text-xs">{{ number_format($promo->discount_value, 0) }}%</span>
                                <span class="text-[10px] text-[#667085] block">Potongan Persen</span>
                            @else
                                <span class="font-bold text-[#062072] text-xs">Rp {{ number_format($promo->discount_value, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-[#667085] block">Potongan Tetap</span>
                            @endif
                        </td>

                        <!-- Kuota & Terpakai -->
                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                            <div class="font-bold text-[#101828]">
                                {{ $promo->usages_count }} / {{ $promo->quota !== null ? $promo->quota : '∞' }}
                            </div>
                            @if ($promo->quota !== null && $promo->usages_count >= $promo->quota)
                                <span class="text-[10px] text-rose-600 font-semibold">Kuota Habis</span>
                            @else
                                <span class="text-[10px] text-[#667085]">Terpakai</span>
                            @endif
                        </td>

                        <!-- Periode Berlaku -->
                        <td class="py-2.5 px-3 whitespace-nowrap">
                            <div class="text-[11px] font-medium text-[#101828]">
                                {{ $promo->start_date->format('d/m/Y') }} - {{ $promo->end_date->format('d/m/Y') }}
                            </div>
                            @if ($isExpired)
                                <span class="text-[10px] text-rose-600 font-semibold block">Sudah Berakhir</span>
                            @elseif ($promo->start_date->isFuture())
                                <span class="text-[10px] text-amber-600 font-semibold block">Akan Datang</span>
                            @else
                                <span class="text-[10px] text-emerald-600 font-semibold block">Sedang Berlangsung</span>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                            @if ($statusVal === 'pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Menunggu Persetujuan
                                </span>
                            @elseif ($statusVal === 'approved')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Disetujui
                                </span>
                            @elseif ($statusVal === 'rejected')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Ditolak
                                </span>
                            @elseif ($statusVal === 'active' && ! $isExpired)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Aktif
                                </span>
                            @elseif ($isExpired)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Expired
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    Nonaktif
                                </span>
                            @endif
                        </td>

                        <!-- Kolom Aksi Terarah Berdasarkan Status Domain -->
                        <td class="py-2.5 px-3 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                @if ($statusVal === 'pending')
                                    @if (auth()->user()->role?->name === 'owner')
                                        <!-- Tombol Setujui (Hijau) -->
                                        <button type="button" class="btn-approve-promo h-7 px-2.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-200 transition-colors inline-flex items-center gap-1 cursor-pointer" data-url="{{ route('promotions.approve', $promo) }}" data-code="{{ $promo->code }}" data-name="{{ $promo->name }}" data-branch="{{ $promo->branch?->display_name ?? 'Semua Cabang (Global)' }}" aria-label="Setujui Promo {{ $promo->code }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span>Setujui</span>
                                        </button>

                                        <!-- Tombol Tolak (Merah) -->
                                        <button type="button" class="btn-reject-promo h-7 px-2.5 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-700 hover:bg-rose-600 hover:text-white border border-rose-200 transition-colors inline-flex items-center gap-1 cursor-pointer" data-url="{{ route('promotions.reject', $promo) }}" data-code="{{ $promo->code }}" data-name="{{ $promo->name }}" data-branch="{{ $promo->branch?->display_name ?? 'Semua Cabang (Global)' }}" aria-label="Tolak Promo {{ $promo->code }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            <span>Tolak</span>
                                        </button>
                                    @endif
                                @elseif (in_array($statusVal, ['active', 'approved'], true))
                                    <!-- Tombol Nonaktifkan (Amber) -->
                                    <button type="button" class="btn-toggle-promo h-7 px-2.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 hover:bg-amber-600 hover:text-white border border-amber-200 transition-colors inline-flex items-center gap-1 cursor-pointer" data-url="{{ route('promotions.toggle-status', $promo) }}" data-code="{{ $promo->code }}" data-name="{{ $promo->name }}" data-is-active="1" aria-label="Nonaktifkan Promo {{ $promo->code }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                        </svg>
                                        <span>Nonaktifkan</span>
                                    </button>
                                @elseif ($statusVal === 'inactive')
                                    <!-- Tombol Aktifkan (Emerald) -->
                                    <button type="button" class="btn-toggle-promo h-7 px-2.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-200 transition-colors inline-flex items-center gap-1 cursor-pointer" data-url="{{ route('promotions.toggle-status', $promo) }}" data-code="{{ $promo->code }}" data-name="{{ $promo->name }}" data-is-active="0" aria-label="Aktifkan Promo {{ $promo->code }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>Aktifkan</span>
                                    </button>
                                @endif

                                <!-- Tombol Edit (Soft Blue) -->
                                <button type="button" class="btn-edit-promo h-7 px-2.5 rounded-md text-[11px] font-semibold bg-[#EAF2FF] text-[#062072] hover:bg-[#062072] hover:text-white border border-[#DCE8FF] transition-colors inline-flex items-center gap-1 cursor-pointer" data-promo="{{ json_encode($promoPayload) }}" aria-label="Edit Promo {{ $promo->code }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    <span>Edit</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-[#667085]">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-8 h-8 text-[#98A2B3] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                </svg>
                                <p class="text-xs font-semibold text-[#101828] m-0">Tidak ada data promo yang cocok</p>
                                <p class="text-[11px] text-[#667085] mt-0.5 m-0">Coba ubah kata kunci filter atau klik tombol Tambah Promo Baru di atas.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($promotions->hasPages())
        <div class="p-4 border-t border-[#E4E7EC] bg-[#F8F9FC]">
            {{ $promotions->links() }}
        </div>
    @endif
</x-cards.card>
