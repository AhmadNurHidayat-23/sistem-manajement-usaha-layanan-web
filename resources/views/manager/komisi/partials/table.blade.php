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
        <div class="overflow-x-auto">
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
                            <th class="px-4 py-3 text-right">Aksi Persetujuan</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($commissions as $comm)
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
                                    {{ $comm->commission_type->value === 'percentage' ? $comm->commission_value . '%' : 'Fixed (Rp ' . number_format($comm->commission_value, 0, ',', '.') . ')' }}
                                </span>
                            </td>

                            <!-- Amount -->
                            <td class="px-4 py-3 font-bold text-[#062072] whitespace-nowrap">
                                Rp {{ number_format($comm->commission_amount, 0, ',', '.') }}
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                @php
                                    $commVariants = [
                                        'pending' => ['label' => 'Pending (Estimasi)', 'badge' => 'warning'],
                                        'approved' => ['label' => 'Disetujui', 'badge' => 'primary'],
                                        'paid' => ['label' => 'Telah Cair', 'badge' => 'success'],
                                        'cancelled' => ['label' => 'Dibatalkan', 'badge' => 'danger'],
                                    ];
                                    $cStatus = $comm->status->value ?? 'pending';
                                    $badgeInfo = $commVariants[$cStatus] ?? ['label' => $cStatus, 'badge' => 'neutral'];
                                @endphp
                                <x-badges.badge :variant="$badgeInfo['badge']">{{ $badgeInfo['label'] }}</x-badges.badge>
                            </td>

                            <!-- Action Buttons -->
                            @if (! ($isSelfView ?? false))
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        @if ($comm->status->value === 'pending')
                                            <form method="POST" action="{{ route('employees.commissions.status', $comm) }}" class="inline m-0">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-[#062072] rounded text-[10px] font-semibold transition-colors">
                                                    Setujui
                                                </button>
                                            </form>
                                        @endif
                                        @if (in_array($comm->status->value, ['pending', 'approved']))
                                            <form method="POST" action="{{ route('employees.commissions.status', $comm) }}" class="inline m-0">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="paid">
                                                <button type="submit" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded text-[10px] font-semibold transition-colors">
                                                    Cairkan
                                                </button>
                                            </form>
                                        @endif
                                        @if ($comm->status->value !== 'cancelled')
                                            <form method="POST" action="{{ route('employees.commissions.status', $comm) }}" class="inline m-0" onsubmit="return confirm('Batalkan komisi ini?');">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition-colors" title="Batalkan">
                                                    <x-icons.icon name="close" class="w-3.5 h-3.5" />
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
