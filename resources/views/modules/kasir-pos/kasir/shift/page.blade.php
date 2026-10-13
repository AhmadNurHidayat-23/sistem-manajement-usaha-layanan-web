@extends('layouts.app', ['title' => 'Shift Kasir | SIMAUL'])

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-[#101828] m-0 flex items-center gap-2">
                <x-icons.icon name="clock" class="w-6 h-6 text-[#062072]" />
                Shift Kasir
            </h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Pengelolaan sesi kerja kasir, audit kas laci, dan rekapitulasi harian.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($accessibleBranches->count() > 1)
                <form method="GET" action="{{ route('pos.shift.index') }}" class="flex items-center gap-2 m-0">
                    <label for="shift_branch_id" class="text-xs font-medium text-[#667085] whitespace-nowrap">Cabang:</label>
                    <select id="shift_branch_id" name="branch_id" class="branch-switch-select px-3 py-1.5 bg-white border border-[#E4E7EC] rounded-lg text-xs font-semibold text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]">
                        @foreach ($accessibleBranches as $b)
                            <option value="{{ $b->id }}" {{ $activeBranch?->id === $b->id ? 'selected' : '' }}>{{ $b->display_name }}</option>
                        @endforeach
                    </select>
                </form>
            @elseif ($activeBranch)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-[#E4E7EC] text-xs font-semibold text-[#101828]">
                    <x-icons.icon name="branch" class="w-4 h-4 text-[#062072]" />
                    {{ $activeBranch->display_name }}
                </span>
            @endif

            <x-buttons.btn variant="primary" href="{{ route('pos.index', ['branch_id' => $activeBranch?->id]) }}" class="gap-1.5 text-xs">
                <x-icons.icon name="cart" class="w-4 h-4" />
                Terminal POS
            </x-buttons.btn>
        </div>
    </div>

    @if (session('success'))
        <x-alerts.alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-alerts.alert type="danger" :message="session('error')" />
    @endif
    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
            <p class="font-semibold mb-1">Terjadi kesalahan:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($activeShift)
        <!-- BAGIAN A: STATUS SHIFT AKTIF -->
        <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-2xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E4E7EC]">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase bg-emerald-100 text-emerald-800">Shift Aktif</span>
                            <span class="text-xs text-[#667085]">Sesi ID #{{ $activeShift->id }}</span>
                        </div>
                        <h4 class="text-base font-bold text-[#101828] mt-1 m-0">{{ $activeBranch?->display_name ?? 'Usaha SIMAUL' }}</h4>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs bg-[#F5F8FC] p-3 rounded-lg border border-[#E4E7EC]/60">
                    <div>
                        <span class="text-[#667085] block text-[11px]">Kasir Bertugas</span>
                        <span class="font-semibold text-[#101828]">{{ $activeShift->user?->name ?? 'Kasir' }}</span>
                    </div>
                    <div>
                        <span class="text-[#667085] block text-[11px]">Waktu Buka Sesi</span>
                        <span class="font-semibold text-[#101828]">{{ $activeShift->opened_at->format('H:i') }} WIB ({{ $activeShift->opened_at->format('d/m/Y') }})</span>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <span class="text-[#667085] block text-[11px]">Saldo Modal Awal</span>
                        <span class="font-bold text-[#062072]">Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- BAGIAN B: REKAP SESI BERJALAN -->
            <div class="pt-5">
                <h5 class="text-xs font-bold uppercase tracking-wider text-[#667085] mb-3">Rekapitulasi Penjualan & Kas Sesi Ini</h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                    <div class="p-3.5 rounded-xl border border-[#E4E7EC] bg-[#F5F8FC]">
                        <span class="text-[11px] text-[#667085] font-medium block">Total Transaksi</span>
                        <span class="text-lg font-bold text-[#101828] mt-1 block tabular-nums">{{ $shiftSummary['total_transactions'] ?? 0 }} Trx</span>
                    </div>
                    <div class="p-3.5 rounded-xl border border-[#E4E7EC] bg-[#F5F8FC]">
                        <span class="text-[11px] text-[#667085] font-medium block">Total Penjualan</span>
                        <span class="text-lg font-bold text-emerald-600 mt-1 block tabular-nums">Rp {{ number_format($shiftSummary['total_sales'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-3.5 rounded-xl border border-[#E4E7EC] bg-[#F5F8FC]">
                        <span class="text-[11px] text-[#667085] font-medium block">Pembayaran Tunai</span>
                        <span class="text-lg font-bold text-[#101828] mt-1 block tabular-nums">Rp {{ number_format($shiftSummary['cash_sales'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-3.5 rounded-xl border border-[#E4E7EC] bg-[#F5F8FC]">
                        <span class="text-[11px] text-[#667085] font-medium block">Pembayaran Nontunai</span>
                        <span class="text-lg font-bold text-[#062072] mt-1 block tabular-nums">Rp {{ number_format($shiftSummary['non_cash_sales'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-3.5 rounded-xl border border-blue-200 bg-blue-50/70 sm:col-span-2 lg:col-span-1">
                        <span class="text-[11px] text-blue-900 font-semibold block">Ekspektasi Kas di Laci</span>
                        <span class="text-lg font-bold text-[#062072] mt-1 block tabular-nums" id="shift-page-expected-cash" data-expected="{{ $shiftSummary['expected_cash'] ?? $activeShift->opening_cash }}">
                            Rp {{ number_format($shiftSummary['expected_cash'] ?? $activeShift->opening_cash, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-blue-700 block mt-0.5">Modal Awal + Kas Tunai</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- BAGIAN D: AKSI TUTUP SHIFT -->
        <x-cards.card title="Tutup Sesi Shift Kasir" subtitle="Hitung kas fisik di laci dan konfirmasi penutupan sesi kerja">
            <form method="POST" action="{{ route('pos.shift.close') }}" class="space-y-4 max-w-xl">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $activeShift->branch_id }}">
                <input type="hidden" name="redirect_to" value="{{ route('pos.shift.index', ['branch_id' => $activeBranch?->id]) }}">

                <div>
                    <label for="shift_closing_cash" class="block text-xs font-semibold text-[#101828] mb-1">
                        Jumlah Kas Fisik di Laci (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="shift_closing_cash" name="closing_cash" required min="0" step="1000"
                           value="{{ old('closing_cash', $shiftSummary['expected_cash'] ?? $activeShift->opening_cash) }}"
                           class="shift-cash-audit-input w-full px-3.5 py-2.5 bg-[#F5F8FC] border border-[#E4E7EC] rounded-lg text-sm font-semibold text-[#101828] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#062072]"
                           placeholder="Masukkan nominal kas aktual">
                    <p id="shift-page-diff-alert" class="mt-1.5 text-xs font-semibold text-emerald-600">Kas Seimbang (Sesuai ekspektasi)</p>
                </div>

                <div>
                    <label for="shift_notes" class="block text-xs font-semibold text-[#101828] mb-1">Catatan Penutupan Shift (Opsional)</label>
                    <textarea id="shift_notes" name="notes" rows="2" class="w-full px-3.5 py-2 bg-[#F5F8FC] border border-[#E4E7EC] rounded-lg text-xs text-[#101828] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#062072]" placeholder="Keterangan audit atau catatan operasional kasir...">{{ old('notes') }}</textarea>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-colors cursor-pointer shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Konfirmasi & Tutup Shift Sekarang
                    </button>
                    <a href="{{ route('pos.index', ['branch_id' => $activeBranch?->id]) }}" class="px-4 py-2.5 border border-[#E4E7EC] text-[#344054] hover:bg-[#F5F8FC] rounded-lg text-xs font-semibold transition-colors">
                        Kembali ke POS
                    </a>
                </div>
            </form>
        </x-cards.card>
    @else
        <!-- BAGIAN A & C: BELUM ADA SHIFT AKTIF -> FORM BUKA SHIFT -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase bg-amber-200 text-amber-900">Shift Belum Aktif</span>
                    <h4 class="text-base font-bold text-amber-950 mt-1 m-0">{{ $activeBranch?->display_name ?? 'Usaha SIMAUL' }}</h4>
                    <p class="text-xs text-amber-800 mt-0.5 m-0">Kasir: {{ auth()->user()->name }} &bull; Buka shift kasir untuk memulai transaksi.</p>
                </div>
            </div>
        </div>

        <x-cards.card title="Buka Shift Kasir Baru" subtitle="Input modal awal uang kas fisik di laci kasir untuk membuka sesi baru">
            <form method="POST" action="{{ route('pos.shift.open') }}" class="space-y-4 max-w-xl">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $activeBranch?->id }}">
                <input type="hidden" name="redirect_to" value="{{ route('pos.shift.index', ['branch_id' => $activeBranch?->id]) }}">

                <div>
                    <label for="shift_opening_cash" class="block text-xs font-semibold text-[#101828] mb-1">
                        Modal Awal Laci Kasir (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="shift_opening_cash" name="opening_cash" required min="0" step="1000"
                           value="{{ old('opening_cash', 200000) }}"
                           class="w-full px-3.5 py-2.5 bg-[#F5F8FC] border border-[#E4E7EC] rounded-lg text-sm font-semibold text-[#101828] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#062072]"
                           placeholder="Contoh: 200000">
                    <p class="mt-1 text-[11px] text-[#667085]">Uang kembalian yang dimasukkan ke laci kasir sebelum transaksi dimulai.</p>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit" class="px-5 py-2.5 bg-[#062072] hover:bg-[#0B3A9E] text-white rounded-lg text-xs font-bold transition-colors cursor-pointer shadow-sm flex items-center gap-2">
                        <x-icons.icon name="plus" class="w-4 h-4" />
                        Buka Shift Sekarang
                    </button>
                    <a href="{{ route('cashier.dashboard') }}" class="px-4 py-2.5 border border-[#E4E7EC] text-[#344054] hover:bg-[#F5F8FC] rounded-lg text-xs font-semibold transition-colors">
                        Kembali ke Dashboard
                    </a>
                </div>
            </form>
        </x-cards.card>
    @endif

    <!-- BAGIAN E: RIWAYAT SHIFT KASIR -->
    <x-cards.card title="Riwayat Sesi Shift Kasir" subtitle="Catatan 10 sesi shift kasir terakhir pada cabang ini">
        <div class="overflow-x-auto -mx-4 -mb-4">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#F5F8FC] text-[#667085] font-semibold border-y border-[#E4E7EC]">
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Waktu Buka</th>
                        <th class="py-3 px-4">Waktu Tutup</th>
                        <th class="py-3 px-4 text-right">Modal Awal</th>
                        <th class="py-3 px-4 text-right">Kas Akhir</th>
                        <th class="py-3 px-4 text-right">Selisih Kas</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @forelse ($shiftHistory as $sh)
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <td class="py-3 px-4 font-bold text-[#101828]">#{{ $sh->id }}</td>
                            <td class="py-3 px-4 text-[#344054]">{{ $sh->opened_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 text-[#344054]">{{ $sh->closed_at ? $sh->closed_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="py-3 px-4 text-right font-medium text-[#101828]">Rp {{ number_format($sh->opening_cash, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-medium text-[#101828]">{{ $sh->closing_cash !== null ? 'Rp ' . number_format($sh->closing_cash, 0, ',', '.') : '-' }}</td>
                            <td class="py-3 px-4 text-right font-bold">
                                @if ($sh->cash_difference === null)
                                    <span class="text-[#667085]">-</span>
                                @elseif ($sh->cash_difference == 0)
                                    <span class="text-emerald-600">Rp 0 (Pas)</span>
                                @elseif ($sh->cash_difference > 0)
                                    <span class="text-blue-600">+Rp {{ number_format($sh->cash_difference, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-rose-600">-Rp {{ number_format(abs($sh->cash_difference), 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($sh->status === \App\Enums\CashierShiftStatus::Open)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-[#344054]">Ditutup</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-[#667085]">Belum ada riwayat sesi shift kasir pada cabang ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-cards.card>
</div>
@endsection
