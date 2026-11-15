<!-- Satu Tabel: Pesanan yang Perlu Ditangani -->
<div class="bg-white border border-[#E4E7EC] rounded-xl shadow-2xs overflow-hidden" id="dashboard-payment-card" data-service-mode="{{ $activeBranch?->service_mode ?? 'both' }}">
    <!-- Header Tabel dan Filter Mode Layanan -->
    <div class="p-4 sm:p-5 border-b border-[#E4E7EC] flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h3 class="text-base sm:text-lg font-bold text-[#101828] m-0 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#062072] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                Pesanan yang Perlu Ditangani
            </h3>
            <p class="text-xs text-[#667085] mt-1 m-0">Daftar pesanan dan transaksi pelanggan yang perlu ditangani kasir.</p>
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

    <!-- Tabel Pesanan & Pembayaran -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse" id="payment-table">
            <thead>
                <tr class="bg-[#F8FAFC] border-b border-[#E4E7EC] text-[#475467] font-semibold uppercase tracking-wider text-[11px]">
                    <th class="px-4 py-3 sm:px-5">Waktu</th>
                    <th class="px-4 py-3 sm:px-5">Pelanggan</th>
                    <th class="px-4 py-3 sm:px-5">Layanan</th>
                    <th class="px-4 py-3 sm:px-5">Jenis</th>
                    <th class="px-4 py-3 sm:px-5">Status</th>
                    <th class="px-4 py-3 sm:px-5">Tagihan</th>
                    <th class="px-4 py-3 sm:px-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E7EC]" id="payment-tbody">
                @foreach ($pendingPayments as $item)
                    <tr class="payment-row hover:bg-[#F5F8FC]/60 transition-colors" data-type="{{ $item['type'] }}" id="payment-row-{{ $item['type'] }}-{{ $item['id'] }}">
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
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $item['status_badge_class'] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $item['display_status'] ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 sm:px-5 whitespace-nowrap">
                            <span class="font-semibold text-[#101828] tabular-nums block">
                                Rp {{ number_format($item['remaining_amount'] ?? $item['total_amount'] ?? 0, 0, ',', '.') }}
                            </span>
                            @if (!empty($item['payment_status_label']))
                                <span class="text-[11px] font-medium {{ ($item['payment_status_label'] === 'Lunas') ? 'text-emerald-600' : 'text-amber-600' }} block">
                                    {{ $item['payment_status_label'] }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 sm:px-5 text-right whitespace-nowrap">
                            @if (($item['action_type'] ?? '') === 'terima')
                                <button type="button"
                                    data-action="open-receive-modal"
                                    data-order-id="{{ $item['id'] }}"
                                    data-customer-name="{{ $item['customer_name'] }}"
                                    data-service-name="{{ $item['service_name'] }}"
                                    data-is-weight-based="{{ !empty($item['is_weight_based']) ? '1' : '0' }}"
                                    data-price-per-kg="{{ $item['price_per_kg'] ?? 0 }}"
                                    data-branch-id="{{ $activeBranch?->id }}"
                                    data-submit-url="{{ route('pos.orders.receive', $item['id']) }}"
                                    class="inline-flex items-center justify-center px-3 py-1.5 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors whitespace-nowrap cursor-pointer">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Terima
                                </button>
                            @elseif (($item['action_type'] ?? '') === 'input_berat')
                                <button type="button"
                                    data-action="open-weight-modal"
                                    data-order-id="{{ $item['id'] }}"
                                    data-customer-name="{{ $item['customer_name'] }}"
                                    data-service-name="{{ $item['service_name'] }}"
                                    data-price-per-kg="{{ $item['price_per_kg'] ?? 0 }}"
                                    data-actual-weight="{{ $item['actual_weight'] ?? '' }}"
                                    data-branch-id="{{ $activeBranch?->id }}"
                                    data-submit-url="{{ route('pos.orders.weigh', $item['id']) }}"
                                    class="inline-flex items-center justify-center px-3 py-1.5 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors whitespace-nowrap cursor-pointer">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                                    </svg>
                                    Timbang
                                </button>
                            @elseif (($item['action_type'] ?? '') === 'detail')
                                <a href="{{ $item['detail_url'] ?? '#' }}"
                                    class="inline-flex items-center justify-center px-3 py-1.5 bg-[#F5F8FC] hover:bg-[#E4E7EC] text-[#062072] border border-[#D0D5DD] text-xs font-semibold rounded-lg shadow-2xs transition-colors whitespace-nowrap">
                                    Lihat
                                </a>
                            @elseif (($item['action_type'] ?? '') === 'bayar')
                                <button type="button"
                                    data-action="open-payment-modal"
                                    data-id="{{ $item['id'] }}"
                                    data-type="{{ $item['type'] }}"
                                    data-service-id="{{ $item['service_id'] ?? '' }}"
                                    data-type-label="{{ $item['type_label'] }}"
                                    data-customer-name="{{ $item['customer_name'] }}"
                                    data-service-name="{{ $item['service_name'] }}"
                                    data-total-amount="{{ $item['total_amount'] }}"
                                    data-paid-amount="{{ $item['paid_amount'] }}"
                                    data-remaining-amount="{{ $item['remaining_amount'] }}"
                                    data-branch-id="{{ $activeBranch?->id }}"
                                    data-branch-name="{{ $activeBranch?->display_name ?? $activeBranch?->name }}"
                                    class="inline-flex items-center justify-center px-3 py-1.5 bg-[#062072] hover:bg-[#0B3A9E] text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors whitespace-nowrap cursor-pointer">
                                    Bayar
                                </button>
                            @else
                                <span class="text-[#98A2B3] font-semibold text-xs px-2">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    @php
        $emptyMessage = match($currentFilter) {
            'appointment' => 'Tidak ada pesanan Appointment yang perlu ditangani.',
            'drop_off' => 'Tidak ada pesanan Drop-off yang perlu ditangani.',
            default => 'Tidak ada pesanan yang perlu ditangani.',
        };
    @endphp
    <div id="payment-empty-state" class="p-8 text-center {{ $pendingPayments->isEmpty() ? '' : 'hidden' }}">
        <div class="w-12 h-12 rounded-full bg-[#F5F8FC] border border-[#E4E7EC] text-[#667085] flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-[#667085]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        </div>
        <p class="text-xs font-semibold text-[#101828] m-0" id="payment-empty-text">{{ $emptyMessage }}</p>
        <p class="text-[11px] text-[#667085] mt-1 m-0">Seluruh pesanan saat ini telah diselesaikan atau lunas.</p>
    </div>
</div>
