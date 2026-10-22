<!-- Commissions Table Card -->
<x-cards.card title="Daftar Rekap Komisi" subtitle="Total {{ $commissions->total() }} catatan komisi">
    @if ($commissions->isEmpty())
        <div class="py-12 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-sm font-semibold text-[#101828] m-0">Tidak ada riwayat komisi</p>
            <p class="text-xs text-[#667085] mt-1 m-0">Belum ada komisi tercatat sesuai dengan kriteria yang dipilih.</p>
        </div>
    @else
        @php
            $commVariants = [
                'pending' => ['label' => 'Menunggu (Estimasi)', 'badge' => 'warning'],
                'approved' => ['label' => 'Disetujui', 'badge' => 'primary'],
                'paid' => ['label' => 'Telah Cair', 'badge' => 'success'],
                'cancelled' => ['label' => 'Dibatalkan', 'badge' => 'danger'],
            ];
        @endphp

        {{-- Tampilan Kartu untuk Layar Mobile (md:hidden) --}}
        <div class="block md:hidden divide-y divide-[#E4E7EC] -mx-4 -my-3 sm:-mx-6">
            @foreach ($commissions as $comm)
                @php
                    $cStatus = $comm->status->value ?? 'pending';
                    $badgeInfo = $commVariants[$cStatus] ?? ['label' => $cStatus, 'badge' => 'neutral'];
                @endphp
                <div class="p-4 space-y-3">
                    {{-- Header Kartu: Karyawan & Status --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            @if ($isSelfView ?? false)
                                <span class="font-bold text-[#101828] text-sm block truncate">{{ $comm->employee->name }}</span>
                            @else
                                <a href="{{ route('employees.show', $comm->employee) }}" class="font-bold text-[#101828] hover:text-[#062072] text-sm block truncate">
                                    {{ $comm->employee->name }}
                                </a>
                            @endif
                            <span class="text-xs text-[#667085] block truncate">{{ $comm->employee->branch->display_name }}</span>
                        </div>
                        <div class="shrink-0">
                            <x-badges.badge :variant="$badgeInfo['badge']">{{ $badgeInfo['label'] }}</x-badges.badge>
                        </div>
                    </div>

                    {{-- Informasi Layanan, Harga & Waktu --}}
                    <div class="bg-[#F8FAFC] p-3 rounded-lg border border-[#E4E7EC] space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[#667085]">Layanan:</span>
                            <span class="font-semibold text-[#101828] text-right">{{ $comm->service->name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#667085]">Harga Layanan:</span>
                            <span class="text-[#101828]">Rp {{ number_format($comm->service->price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#667085]">Transaksi:</span>
                            <span>
                                @if ($comm->transaction)
                                    @if (($isSelfView ?? false) || !in_array(auth()->user()?->role?->name, ['owner', 'manager', 'cashier']))
                                        <span class="font-semibold text-[#101828]">{{ $comm->transaction->transaction_code }}</span>
                                    @else
                                        <a href="{{ route('pos.transactions.show', $comm->transaction) }}" class="font-semibold text-[#062072] hover:underline">
                                            {{ $comm->transaction->transaction_code }}
                                        </a>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Manual</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#667085]">Waktu:</span>
                            <span class="text-[#667085]">{{ $comm->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    {{-- Skema & Nominal Komisi --}}
                    <div class="flex items-center justify-between pt-1">
                        <div>
                            <span class="text-[11px] text-[#667085] block">Skema:</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">
                                {{ $comm->commission_type->value === 'percentage' ? $comm->commission_value . '%' : 'Nominal Tetap (Rp ' . number_format($comm->commission_value, 0, ',', '.') . ')' }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-[#667085] block">Nominal Komisi:</span>
                            <span class="text-base font-bold text-[#062072]">
                                Rp {{ number_format($comm->commission_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- Tombol Aksi Mobile --}}
                    @if (! ($isSelfView ?? false) && $comm->status->value !== 'cancelled')
                        <div class="flex items-center gap-2 pt-2 border-t border-[#E4E7EC]">
                            @if ($comm->status->value === 'pending')
                                <form method="POST" action="{{ route('employees.commissions.status', $comm) }}" class="flex-1 m-0">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="w-full h-8.5 px-3 bg-blue-50 hover:bg-blue-100 text-[#062072] rounded-lg text-xs font-semibold transition-colors flex items-center justify-center cursor-pointer">
                                        Setujui
                                    </button>
                                </form>
                            @endif
                            @if (in_array($comm->status->value, ['pending', 'approved']))
                                <form method="POST" action="{{ route('employees.commissions.status', $comm) }}" class="flex-1 m-0">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="paid">
                                    <button type="submit" class="w-full h-8.5 px-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold transition-colors flex items-center justify-center cursor-pointer">
                                        Cairkan
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('employees.commissions.status', $comm) }}" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan komisi ini?');">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="h-8.5 px-3 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs font-semibold transition-colors flex items-center justify-center cursor-pointer" title="Batalkan Komisi">
                                    Batalkan
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Tampilan Tabel untuk Layar Desktop (hidden md:block) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-[13px] text-[#101828]">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] uppercase text-xs tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Karyawan</th>
                        <th class="px-4 py-3">Layanan & Harga</th>
                        <th class="px-4 py-3">Kode Transaksi</th>
                        <th class="px-4 py-3">Skema Komisi</th>
                        <th class="px-4 py-3">Nominal Komisi</th>
                        <th class="px-4 py-3">Status</th>
                        @if (! ($isSelfView ?? false))
                            <th class="px-4 py-3 text-right">Aksi Pengelolaan</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($commissions as $comm)
                        @php
                            $cStatus = $comm->status->value ?? 'pending';
                            $badgeInfo = $commVariants[$cStatus] ?? ['label' => $cStatus, 'badge' => 'neutral'];
                        @endphp
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <!-- Date -->
                            <td class="px-4 py-3 text-[#667085] whitespace-nowrap">
                                {{ $comm->created_at->format('d M Y H:i') }}
                            </td>

                            <!-- Employee -->
                            <td class="px-4 py-3">
                                @if ($isSelfView ?? false)
                                    <span class="font-bold text-[#101828] block">{{ $comm->employee->name }}</span>
                                @else
                                    <a href="{{ route('employees.show', $comm->employee) }}" class="font-bold text-[#101828] hover:text-[#062072] hover:underline block">
                                        {{ $comm->employee->name }}
                                    </a>
                                @endif
                                <span class="text-[10px] text-[#667085] block">{{ $comm->employee->branch->display_name }}</span>
                            </td>

                            <!-- Service -->
                            <td class="px-4 py-3">
                                <span class="font-semibold text-[#101828] block">{{ $comm->service->name }}</span>
                                <span class="text-[10px] text-[#667085]">Rp {{ number_format($comm->service->price, 0, ',', '.') }}</span>
                            </td>

                            <!-- Transaction -->
                            <td class="px-4 py-3 text-[#667085]">
                                @if ($comm->transaction)
                                    @if (($isSelfView ?? false) || !in_array(auth()->user()?->role?->name, ['owner', 'manager', 'cashier']))
                                        <span class="font-semibold text-[#101828]">{{ $comm->transaction->transaction_code }}</span>
                                    @else
                                        <a href="{{ route('pos.transactions.show', $comm->transaction) }}" class="font-semibold text-[#062072] hover:underline">
                                            {{ $comm->transaction->transaction_code }}
                                        </a>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Manual</span>
                                @endif
                            </td>

                            <!-- Scheme -->
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $comm->commission_type->value === 'percentage' ? $comm->commission_value . '%' : 'Nominal Tetap (Rp ' . number_format($comm->commission_value, 0, ',', '.') . ')' }}
                                </span>
                            </td>

                            <!-- Amount -->
                            <td class="px-4 py-3 font-bold text-[#062072] whitespace-nowrap">
                                Rp {{ number_format($comm->commission_amount, 0, ',', '.') }}
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <x-badges.badge :variant="$badgeInfo['badge']">{{ $badgeInfo['label'] }}</x-badges.badge>
                            </td>

                            <!-- Action Buttons -->
                            @if (! ($isSelfView ?? false))
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if ($comm->status->value === 'pending')
                                            <form method="POST" action="{{ route('employees.commissions.status', $comm) }}" class="inline m-0">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-[#062072] rounded text-xs font-semibold transition-colors cursor-pointer">
                                                    Setujui
                                                </button>
                                            </form>
                                        @endif
                                        @if (in_array($comm->status->value, ['pending', 'approved']))
                                            <form method="POST" action="{{ route('employees.commissions.status', $comm) }}" class="inline m-0">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="paid">
                                                <button type="submit" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded text-xs font-semibold transition-colors cursor-pointer">
                                                    Cairkan
                                                </button>
                                            </form>
                                        @endif
                                        @if ($comm->status->value !== 'cancelled')
                                            <form method="POST" action="{{ route('employees.commissions.status', $comm) }}" class="inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan komisi ini?');">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded transition-colors cursor-pointer" title="Batalkan Komisi" aria-label="Batalkan Komisi">
                                                    <x-icons.icon name="close" class="w-4 h-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pt-4 border-t border-[#E4E7EC]">
            {{ $commissions->withQueryString()->links() }}
        </div>
    @endif
</x-cards.card>

