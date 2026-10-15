<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan & Kinerja — SIMAUL</title>
    @if (!empty($css))
        <style>
            {!! $css !!}
        </style>
    @elseif (file_exists(resource_path('css/laporan-pdf.css')))
        <style>
            {!! file_get_contents(resource_path('css/laporan-pdf.css')) !!}
        </style>
    @endif
</head>
<body>
    <!-- Header SIMAUL -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 58%;">
                    <div class="system-name">SIMAUL - Service Management & POS</div>
                    <div class="report-title">Laporan Kinerja & Rekap Transaksi Penjualan</div>
                    <div class="print-date">Tanggal Cetak: {{ now()->translatedFormat('d F Y H:i:s') }}</div>
                </td>
                <td style="width: 42%; text-align: right;">
                    <div class="badge-branch">
                        {{ $report['is_combined'] ? 'KONSOLIDASI SELURUH CABANG' : strtoupper($report['selected_branch']?->name ?? 'CABANG') }}
                    </div>
                    <div class="period-info">
                        Periode: <strong>{{ $report['formatted_start_date'] }} — {{ $report['formatted_end_date'] }}</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 1. Ringkasan Finansial Utama -->
    <div class="section-title">1. Ringkasan Finansial & Transaksi</div>
    <table class="summary-table">
        <tr>
            <td>
                <span class="summary-label">Total Omzet Bersih</span>
                <span class="summary-value">Rp {{ number_format($report['summary']['total_omzet'] ?? 0, 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="summary-label">Total Transaksi</span>
                <span class="summary-value">{{ number_format($report['summary']['total_transactions'] ?? 0) }} Trx</span>
            </td>
            <td>
                <span class="summary-label">Rata-Rata / Transaksi</span>
                <span class="summary-value">Rp {{ number_format($report['summary']['average_transaction_value'] ?? 0, 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="summary-label">Total Diskon</span>
                <span class="summary-value">Rp {{ number_format($report['summary']['total_discount'] ?? 0, 0, ',', '.') }}</span>
            </td>
        </tr>
    </table>

    <!-- 2. Tabel Rincian Transaksi -->
    <div class="section-title">2. Rincian Transaksi</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 20px;">No</th>
                <th class="text-center" style="width: 68px;">Tanggal</th>
                <th style="width: 90px;">Kode Trx</th>
                <th>Cabang</th>
                <th>Customer</th>
                <th class="text-center" style="width: 60px;">Metode</th>
                <th class="text-right" style="width: 68px;">Subtotal (Rp)</th>
                <th class="text-right" style="width: 58px;">Diskon (Rp)</th>
                <th class="text-right" style="width: 68px;">Total (Rp)</th>
                <th class="text-center" style="width: 45px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $transactions = $report['transactions'] ?? collect(); @endphp
            @forelse ($transactions as $index => $trx)
                @php
                    $customerName = $trx->booking?->user?->name
                        ?? $trx->order?->user?->name
                        ?? ($trx->customer_name ?? 'Pelanggan Walk-In');
                    $firstPayment = $trx->payments->first();
                    $paymentMethod = $firstPayment?->payment_method
                        ? strtoupper(str_replace('_', ' ', $firstPayment->payment_method))
                        : 'TUNAI';
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $trx->transaction_date ? $trx->transaction_date->format('d/m/Y H:i') : '-' }}</td>
                    <td class="font-bold">{{ $trx->transaction_code }}</td>
                    <td>{{ $trx->branch?->display_name ?? '-' }}</td>
                    <td>{{ $customerName }}</td>
                    <td class="text-center">{{ $paymentMethod }}</td>
                    <td class="text-right">Rp {{ number_format((float) $trx->subtotal, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format((float) $trx->discount, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format((float) $trx->total, 0, ',', '.') }}</td>
                    <td class="text-center">{{ strtoupper($trx->status?->value ?? 'PAID') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 8px; color: #64748b;">
                        Tidak ada transaksi pada periode ini.
                    </td>
                </tr>
            @endforelse
            @if ($transactions->isNotEmpty())
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="6" class="text-right">TOTAL:</td>
                    <td class="text-right">Rp {{ number_format((float) $report['summary']['gross_omzet'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format((float) $report['summary']['total_discount'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format((float) $report['summary']['total_omzet'] ?? 0, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- 3. Metode Pembayaran & Pendapatan Layanan (2 Kolom) -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 6px;">
        <tr>
            <td style="width: 48%; vertical-align: top; border: none; padding: 0 8px 0 0;">
                <div class="section-title">3. Metode Pembayaran</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Metode</th>
                            <th class="text-center" style="width: 40px;">Trx</th>
                            <th class="text-right" style="width: 75px;">Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['payment_methods'] as $pm)
                            <tr>
                                <td>{{ $pm['method_label'] }}</td>
                                <td class="text-center">{{ $pm['count'] }}</td>
                                <td class="text-right font-bold">Rp {{ number_format($pm['total_amount'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td style="width: 52%; vertical-align: top; border: none; padding: 0 0 0 8px;">
                <div class="section-title">4. Layanan Terlaris</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th class="text-center" style="width: 35px;">Qty</th>
                            <th class="text-right" style="width: 75px;">Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (array_slice($report['services'] ?? [], 0, 5) as $srv)
                            <tr>
                                <td>{{ $srv['service_name'] }}</td>
                                <td class="text-center">{{ $srv['total_qty'] }}</td>
                                <td class="text-right font-bold">Rp {{ number_format($srv['total_revenue'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan Dokumen -->
    <table class="signoff-table">
        <tr>
            <td>
                <p>Dibuat Oleh,</p>
                <div class="sign-line">{{ auth()->user()?->name ?? 'Administrator SIMAUL' }}</div>
            </td>
            <td>
                <p>Mengetahui / Menyetujui,</p>
                <div class="sign-line">Manajemen SIMAUL</div>
            </td>
        </tr>
    </table>
</body>
</html>
