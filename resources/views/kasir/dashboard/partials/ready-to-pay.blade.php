<!-- Satu Tabel: Pembayaran yang Perlu Ditangani -->
<div class="bg-white border border-[#E4E7EC] rounded-xl shadow-2xs overflow-hidden" id="dashboard-payment-card" data-service-mode="{{ $activeBranch?->service_mode ?? 'both' }}">
    <!-- Header Tabel dan Filter Mode Layanan -->
    <div class="p-4 sm:p-5 border-b border-[#E4E7EC] flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h3 class="text-base sm:text-lg font-bold text-[#101828] m-0 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#062072] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Pembayaran yang Perlu Ditangani
            </h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Daftar transaksi pelanggan yang masih memiliki tagihan.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if ($activeBranch?->service_mode === 'both')
                <!-- Filter Tab: Semua, Appointment, Drop-off untuk usaha multi-mode -->
                <div class="inline-flex items-center gap-1 bg-[#F5F8FC] p-1 rounded-lg border border-[#E4E7EC]" role="tablist" aria-label="Filter Mode Transaksi">
                    <a href="{{ route('cashier.dashboard', array_filter(['branch_id' => $activeBranch?->id, 'type' => 'all'])) }}"
                       data-filter="all"
                       class="payment-tab-btn px-3 py-1.5 rounded-md text-xs font-semibold transition-all {{ $currentFilter === 'all' ? 'bg-[#062072] text-white shadow-2xs' : 'text-[#667085] hover:text-[#101828] hover:bg-white' }}">
                        Semua
                    </a>
                    <a href="{{ route('cashier.dashboard', array_filter(['branch_id' => $activeBranch?->id, 'type' => 'appointment'])) }}"
                       data-filter="appointment"
                       class="payment-tab-btn px-3 py-1.5 rounded-md text-xs font-semibold transition-all {{ $currentFilter === 'appointment' ? 'bg-[#062072] text-white shadow-2xs' : 'text-[#667085] hover:text-[#101828] hover:bg-white' }}">
                        Appointment
                    </a>
                    <a href="{{ route('cashier.dashboard', array_filter(['branch_id' => $activeBranch?->id, 'type' => 'drop_off'])) }}"
                       data-filter="drop_off"
                       class="payment-tab-btn px-3 py-1.5 rounded-md text-xs font-semibold transition-all {{ $currentFilter === 'drop_off' ? 'bg-[#062072] text-white shadow-2xs' : 'text-[#667085] hover:text-[#101828] hover:bg-white' }}">
                        Drop-off
                    </a>
                </div>
            @elseif ($activeBranch?->service_mode === 'appointment')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#F5F8FC] border border-[#E4E7EC] text-xs font-semibold text-[#062072]">
                    <span class="w-2 h-2 rounded-full bg-[#062072]"></span>
                    Mode: Appointment
                </span>
            @elseif ($activeBranch?->service_mode === 'drop_off')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#F5F8FC] border border-[#E4E7EC] text-xs font-semibold text-[#062072]">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    Mode: Drop-off
                </span>
            @endif
        </div>
    </div>

    <!-- Tabel Pembayaran -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse" id="payment-table">
            <thead>
                <tr class="bg-[#F8FAFC] border-b border-[#E4E7EC] text-[#475467] font-semibold uppercase tracking-wider text-[11px]">
                    <th class="px-4 py-3 sm:px-5">Waktu</th>
                    <th class="px-4 py-3 sm:px-5">Pelanggan</th>
                    <th class="px-4 py-3 sm:px-5">Layanan</th>
                    <th class="px-4 py-3 sm:px-5">Jenis</th>
                    <th class="px-4 py-3 sm:px-5">Pembayaran</th>
                    <th class="px-4 py-3 sm:px-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC]" id="payment-tbody">
                @foreach ($pendingPayments as $item)
                    <tr class="payment-row hover:bg-[#F5F8FC]/60 transition-colors" data-type="{{ $item['type'] }}">
                        <td class="px-4 py-3.5 sm:px-5 font-semibold text-[#101828] whitespace-nowrap tabular-nums">
                            {{ $item['time'] ?? '-' }}
                        </td>
                        <td class="px-4 py-3.5 sm:px-5">
                            <div class="font-semibold text-[#101828]">{{ $item['customer_name'] }}</div>
                        </td>
                        <td class="px-4 py-3.5 sm:px-5 text-[#475467] max-w-xs truncate">
                            {{ $item['service_name'] }}
                        </td>
                        <td class="px-4 py-3.5 sm:px-5 whitespace-nowrap">
                            @if ($item['type'] === 'appointment')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-[#062072] border border-blue-200">
                                    Appointment
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    Drop-off
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 sm:px-5 whitespace-nowrap">
                            <span class="font-semibold text-[#101828] tabular-nums block">
                                Rp {{ number_format($item['remaining_amount'], 0, ',', '.') }}
                            </span>
                            <span class="text-[11px] font-medium text-amber-600 block">
                                {{ $item['payment_status_label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 sm:px-5 text-right whitespace-nowrap">
                            <a href="{{ $item['pay_url'] }}"
                               class="inline-flex items-center justify-center px-3 py-1.5 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors whitespace-nowrap">
                                Bayar di POS
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    @php
        $emptyMessage = match($currentFilter) {
            'appointment' => 'Tidak ada pembayaran Appointment yang perlu ditangani.',
            'drop_off' => 'Tidak ada pembayaran Drop-off yang perlu ditangani.',
            default => 'Tidak ada pembayaran yang perlu ditangani.',
        };
    @endphp
    <div id="payment-empty-state" class="p-8 text-center {{ $pendingPayments->isEmpty() ? '' : 'hidden' }}">
        <div class="w-12 h-12 rounded-full bg-[#F5F8FC] border border-[#E4E7EC] text-[#667085] flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        </div>
        <p class="text-xs font-semibold text-[#101828] m-0" id="payment-empty-text">{{ $emptyMessage }}</p>
        <p class="text-[11px] text-[#667085] mt-1 m-0">Seluruh transaksi saat ini telah diselesaikan atau lunas.</p>
    </div>
</div>
