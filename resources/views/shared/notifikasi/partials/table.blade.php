<x-cards.card>
    @if ($logs->isEmpty())
        <div class="py-10 sm:py-12 text-center text-[#667085]">
            <svg class="w-9 h-9 mx-auto mb-2 text-[#D0D5DD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <p class="text-xs sm:text-sm font-semibold text-[#101828] mb-0.5">Belum ada log notifikasi</p>
            <p class="text-[11px] text-[#667085] m-0">Riwayat pengiriman notifikasi WhatsApp akan tercatat di sini.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] text-[#101828] border-collapse">
                <thead class="bg-[#F5F8FC] border-b border-[#E4E7EC] text-[11px] font-semibold text-[#667085] uppercase tracking-wider">
                    <tr>
                        <th class="px-3.5 py-3 text-left whitespace-nowrap">Waktu</th>
                        <th class="px-3.5 py-3 text-left whitespace-nowrap">Penerima</th>
                        <th class="px-3.5 py-3 text-left whitespace-nowrap">Tipe</th>
                        <th class="px-3.5 py-3 text-left whitespace-nowrap">Status</th>
                        <th class="px-3.5 py-3 text-left">Pesan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E7EC]">
                    @foreach ($logs as $log)
                        <tr class="hover:bg-[#F5F8FC]/50 transition-colors">
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                <div class="text-xs text-[#101828] font-medium">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-[11px] text-[#667085]">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                @if ($log->user)
                                    <div class="text-xs font-semibold text-[#101828]">{{ $log->user->name }}</div>
                                @endif
                                <div class="text-xs text-[#667085]">{{ $log->recipient ?? '—' }}</div>
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#DCE8FF] text-[#062072] text-xs font-medium">
                                    {{ str_replace('_', ' ', ucfirst($log->type)) }}
                                </span>
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                @if ($log->status === 'sent')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Terkirim
                                    </span>
                                @elseif ($log->status === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" title="{{ $log->error }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Gagal
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-3.5 py-3 max-w-sm">
                                <p class="text-xs text-[#667085] truncate whitespace-pre-line line-clamp-2 m-0" title="{{ $log->message }}">
                                    {{ $log->message }}
                                </p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="pt-4 border-t border-[#E4E7EC]">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif
    @endif
</x-cards.card>
