<!-- Riwayat komisi -->
<x-cards.card class="p-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h4 class="text-sm font-bold text-[#101828] m-0">Rekap & Estimasi Komisi Karyawan</h4>
            <p class="text-xs text-[#667085] mt-0.5 m-0">Riwayat komisi layanan yang dihasilkan berdasarkan skema komisi.</p>
        </div>
        @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
            <button type="button" onclick="document.getElementById('modal-add-commission').classList.remove('hidden')" class="px-3 py-1.5 bg-[#062072] text-white rounded-lg text-xs font-semibold hover:bg-[#0B3A9E] transition-colors inline-flex items-center gap-1.5">
                <x-icons.icon name="plus" class="w-3.5 h-3.5" />
                + Catat Komisi Baru
            </button>
        @endif
    </div>

    @if ($performance['recent_commissions']->isEmpty())
        <div class="py-8 text-center bg-[#F5F8FC] rounded-lg">
            <p class="text-xs text-[#667085] m-0">Belum ada riwayat komisi untuk staf ini.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#101828]">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-2.5">Tanggal</th>
                        <th class="px-4 py-2.5">Layanan</th>
                        <th class="px-4 py-2.5">Transaksi</th>
                        <th class="px-4 py-2.5">Skema Komisi</th>
                        <th class="px-4 py-2.5">Nilai Komisi (Rp)</th>
                        <th class="px-4 py-2.5">Status</th>
                        @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
                            <th class="px-4 py-2.5 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($performance['recent_commissions'] as $comm)
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <td class="px-4 py-2.5 font-medium text-[#667085]">
                                {{ $comm->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-2.5 font-semibold text-[#101828]">
                                {{ $comm->service->name }}
                            </td>
                            <td class="px-4 py-2.5 text-[#667085]">
                                {{ $comm->transaction?->transaction_code ?? '-' }}
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $comm->commission_type->value === 'percentage' ? $comm->commission_value . '%' : 'Fixed' }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 font-bold text-[#062072]">
                                Rp {{ number_format($comm->commission_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5">
                                @php
                                    $commVariants = [
                                        'pending' => ['label' => 'Pending (Estimasi)', 'badge' => 'warning'],
                                        'approved' => ['label' => 'Disetujui (Siap Cair)', 'badge' => 'primary'],
                                        'paid' => ['label' => 'Telah Cair', 'badge' => 'success'],
                                        'cancelled' => ['label' => 'Dibatalkan', 'badge' => 'danger'],
                                    ];
                                    $cStatus = $comm->status->value ?? 'pending';
                                    $cBadge = $commVariants[$cStatus] ?? ['label' => $cStatus, 'badge' => 'neutral'];
                                @endphp
                                <x-badges.badge :variant="$cBadge['badge']">{{ $cBadge['label'] }}</x-badges.badge>
                            </td>
                            @if (in_array(auth()->user()?->role?->name, ['owner', 'manager']))
                                <td class="px-4 py-2.5 text-right">
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
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-cards.card>
