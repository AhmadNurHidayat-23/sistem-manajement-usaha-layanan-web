<!-- Tabel daftar member -->
<x-cards.card title="Daftar Member Terdaftar" subtitle="Total {{ $members->total() }} member ditemukan">
    @if ($members->isEmpty())
        <div class="py-12 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <x-icons.icon name="user" class="w-6 h-6" />
            </div>
            <p class="text-sm font-semibold text-[#101828] m-0">Tidak ada data member</p>
            <p class="text-xs text-[#667085] mt-1 m-0">Belum ada pelanggan yang terdaftar sebagai member sesuai filter pencarian.</p>
            <div class="mt-4">
                <x-buttons.btn variant="primary" href="{{ route('members.create') }}" data-modal-open="modal-register-member" class="gap-1.5 text-xs">
                    <x-icons.icon name="plus" class="w-4 h-4" />
                    Daftarkan Member Baru
                </x-buttons.btn>
            </div>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#101828]">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[#667085] uppercase text-[11px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-3 py-2">Kode Member</th>
                        <th class="px-3 py-2">Nama Pelanggan</th>
                        <th class="px-3 py-2">Kontak & Email</th>
                        <th class="px-3 py-2">Bergabung Sejak</th>
                        <th class="px-3 py-2">Saldo Poin</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($members as $member)
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg font-semibold text-xs bg-[#DCE8FF] text-[#062072] border border-[#0B3A9E]/20">
                                    <x-icons.icon name="receipt" class="w-3.5 h-3.5" />
                                    {{ $member->member_code }}
                                </span>
                            </td>

                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <a href="{{ route('members.show', $member) }}" class="font-bold text-[#101828] hover:text-[#062072] hover:underline block leading-tight">
                                    {{ $member->user->name }}
                                </a>
                            </td>

                            <td class="px-3 py-2.5 text-[#667085] whitespace-nowrap">
                                <span class="block text-[#101828] font-medium leading-tight">{{ $member->user->phone ?? '-' }}</span>
                                <span class="text-[11px] leading-tight">{{ $member->user->email }}</span>
                            </td>

                            <td class="px-3 py-2.5 text-[#667085] whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($member->joined_at)->translatedFormat('d M Y') }}
                            </td>

                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 font-bold text-amber-600 text-xs">
                                    <x-icons.icon name="money" class="w-3.5 h-3.5 text-amber-500" />
                                    {{ number_format($member->loyaltyPoint?->total_points ?? 0) }} Poin
                                </div>
                                <span class="text-[10px] text-[#667085] block">
                                    {{ $member->loyalty_transactions_count }} transaksi poin
                                </span>
                            </td>

                            <td class="px-3 py-2.5 whitespace-nowrap">
                                @php
                                    $statusVariants = [
                                        'active' => ['label' => 'Aktif', 'badge' => 'success'],
                                        'inactive' => ['label' => 'Nonaktif', 'badge' => 'neutral'],
                                        'suspended' => ['label' => 'Suspended', 'badge' => 'danger'],
                                    ];
                                    $st = $statusVariants[$member->status->value] ?? ['label' => $member->status->value, 'badge' => 'neutral'];
                                @endphp
                                <x-badges.badge :variant="$st['badge']">{{ $st['label'] }}</x-badges.badge>
                            </td>

                            <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('members.show', $member) }}" class="h-7 px-2.5 bg-[#062072] text-white hover:bg-[#0B3A9E] rounded-lg text-xs font-semibold transition-colors inline-flex items-center gap-1" title="Lihat Kartu & Riwayat">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Detail & Poin
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginasi tabel -->
        <div class="pt-4 border-t border-[#E4E7EC]">
            {{ $members->withQueryString()->links() }}
        </div>
    @endif
</x-cards.card>
