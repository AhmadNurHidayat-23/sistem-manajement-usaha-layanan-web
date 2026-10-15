<?php

namespace App\Services\LaporanKeuangan;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EksporLaporanService
{
    /**
     * Ekspor data laporan ke format Microsoft Excel asli (.xlsx).
     *
     * @param  array<string, mixed>  $report
     */
    public function exportExcel(array $report): StreamedResponse
    {
        $filename = 'laporan-transaksi-'.now()->format('Y-m-d').'.xlsx';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Transaksi');

        // 1. Informasi Header Laporan
        $sheet->setCellValue('A1', 'SIMAUL - LAPORAN KINERJA & TRANSAKSI');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $branchName = $report['is_combined']
            ? 'Konsolidasi Seluruh Cabang'
            : ($report['selected_branch']?->name ?? 'Semua Cabang');

        $sheet->setCellValue('A2', 'Periode: '.$report['formatted_start_date'].' - '.$report['formatted_end_date']);
        $sheet->setCellValue('A3', 'Cabang: '.$branchName);
        $sheet->setCellValue('A4', 'Tanggal Ekspor: '.now()->translatedFormat('d F Y H:i'));

        // 2. Ringkasan Finansial
        $sheet->setCellValue('A6', 'RINGKASAN UTAMA');
        $sheet->getStyle('A6')->getFont()->setBold(true);

        $sheet->setCellValue('A7', 'Total Transaksi:');
        $sheet->setCellValue('B7', $report['summary']['total_transactions'] ?? 0);

        $sheet->setCellValue('A8', 'Omzet Bersih (Net):');
        $sheet->setCellValue('B8', (float) ($report['summary']['total_omzet'] ?? 0));
        $sheet->getStyle('B8')->getNumberFormat()->setFormatCode('#,##0');

        $sheet->setCellValue('D7', 'Omzet Kotor (Gross):');
        $sheet->setCellValue('E7', (float) ($report['summary']['gross_omzet'] ?? 0));
        $sheet->getStyle('E7')->getNumberFormat()->setFormatCode('#,##0');

        $sheet->setCellValue('D8', 'Total Diskon:');
        $sheet->setCellValue('E8', (float) ($report['summary']['total_discount'] ?? 0));
        $sheet->getStyle('E8')->getNumberFormat()->setFormatCode('#,##0');

        // 3. Tabel Rincian Transaksi
        $tableStartRow = 11;
        $headers = [
            'No',
            'Tanggal Transaksi',
            'Kode Transaksi',
            'Cabang',
            'Customer',
            'Metode Pembayaran',
            'Subtotal (Rp)',
            'Diskon (Rp)',
            'Total (Rp)',
            'Status',
        ];

        $col = 'A';
        foreach ($headers as $headerText) {
            $sheet->setCellValue($col.$tableStartRow, $headerText);
            $col++;
        }

        // Header Styling
        $headerRange = 'A'.$tableStartRow.':J'.$tableStartRow;
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('062072');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Data Rows
        $currentRow = $tableStartRow + 1;
        $transactions = $report['transactions'] ?? collect();

        foreach ($transactions as $index => $trx) {
            $customerName = $trx->booking?->user?->name
                ?? $trx->order?->user?->name
                ?? ($trx->customer_name ?? 'Pelanggan Walk-In');

            $firstPayment = $trx->payments->first();
            $paymentMethod = $firstPayment?->payment_method
                ? strtoupper(str_replace('_', ' ', $firstPayment->payment_method))
                : 'TUNAI';

            $sheet->setCellValue('A'.$currentRow, $index + 1);
            $sheet->setCellValue('B'.$currentRow, $trx->transaction_date ? $trx->transaction_date->format('d/m/Y H:i') : '-');
            $sheet->setCellValue('C'.$currentRow, $trx->transaction_code);
            $sheet->setCellValue('D'.$currentRow, $trx->branch?->name ?? '-');
            $sheet->setCellValue('E'.$currentRow, $customerName);
            $sheet->setCellValue('F'.$currentRow, $paymentMethod);
            $sheet->setCellValue('G'.$currentRow, (float) $trx->subtotal);
            $sheet->setCellValue('H'.$currentRow, (float) $trx->discount);
            $sheet->setCellValue('I'.$currentRow, (float) $trx->total);
            $sheet->setCellValue('J'.$currentRow, strtoupper($trx->status?->value ?? 'PAID'));

            // Format number columns
            $sheet->getStyle('G'.$currentRow.':I'.$currentRow)
                ->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getStyle('A'.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J'.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $currentRow++;
        }

        // Baris Total jika ada transaksi
        if ($transactions->isNotEmpty()) {
            $sheet->setCellValue('A'.$currentRow, 'TOTAL');
            $sheet->mergeCells('A'.$currentRow.':F'.$currentRow);
            $sheet->getStyle('A'.$currentRow)->getFont()->setBold(true);
            $sheet->getStyle('A'.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->setCellValue('G'.$currentRow, '=SUM(G'.($tableStartRow + 1).':G'.($currentRow - 1).')');
            $sheet->setCellValue('H'.$currentRow, '=SUM(H'.($tableStartRow + 1).':H'.($currentRow - 1).')');
            $sheet->setCellValue('I'.$currentRow, '=SUM(I'.($tableStartRow + 1).':I'.($currentRow - 1).')');

            $sheet->getStyle('G'.$currentRow.':I'.$currentRow)->getFont()->setBold(true);
            $sheet->getStyle('G'.$currentRow.':I'.$currentRow)
                ->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getStyle('A'.$currentRow.':J'.$currentRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F1F5F9');

            $currentRow++;
        }

        // Border keliling tabel
        $tableRange = 'A'.$tableStartRow.':J'.($currentRow - 1);
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('CBD5E1');

        // Auto-fit kolom
        foreach (range('A', 'J') as $colId) {
            $sheet->getColumnDimension($colId)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public',
        ];

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, $headers);
    }

    /**
     * Ekspor data laporan ke format PDF asli (.pdf) server-side via Dompdf.
     *
     * @param  array<string, mixed>  $report
     */
    public function exportPdf(array $report): Response
    {
        $filename = 'laporan-transaksi-'.now()->format('Y-m-d').'.pdf';

        $css = file_exists(resource_path('css/laporan-pdf.css'))
            ? (string) file_get_contents(resource_path('css/laporan-pdf.css'))
            : '';

        $pdf = Pdf::loadView('owner.laporan.print', compact('report', 'css'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'sans-serif',
            ]);

        return $pdf->download($filename);
    }

    /**
     * Ekspor data laporan ke format CSV (backward compatibility).
     *
     * @param  array<string, mixed>  $report
     */
    public function exportCsv(array $report): StreamedResponse
    {
        $filename = 'Laporan_Penjualan_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($report) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['LAPORAN KINERJA & PENJUALAN']);
            fputcsv($handle, ['Sistem Manajemen POS']);
            fputcsv($handle, ['Periode', $report['formatted_start_date'].' - '.$report['formatted_end_date']]);
            fputcsv($handle, ['Cakupan Cabang', $report['is_combined'] ? 'Konsolidasi Seluruh Cabang' : ($report['selected_branch']?->name ?? 'Semua Cabang')]);
            fputcsv($handle, ['Tanggal Ekspor', now()->translatedFormat('d F Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, ['RINGKASAN UTAMA']);
            fputcsv($handle, ['Total Transaksi', $report['summary']['total_transactions']]);
            fputcsv($handle, ['Omzet Bersih (Net)', $report['summary']['total_omzet']]);
            fputcsv($handle, ['Omzet Kotor (Gross)', $report['summary']['gross_omzet']]);
            fputcsv($handle, ['Total Diskon Diberikan', $report['summary']['total_discount']]);
            fputcsv($handle, ['Rata-Rata Nilai Transaksi', $report['summary']['average_transaction_value']]);
            fputcsv($handle, []);

            fputcsv($handle, ['METODE PEMBAYARAN']);
            fputcsv($handle, ['Metode', 'Jumlah Transaksi', 'Total Nominal (Rp)', 'Kontribusi (%)']);
            foreach ($report['payment_methods'] as $pm) {
                fputcsv($handle, [
                    $pm['method_label'],
                    $pm['count'],
                    $pm['total_amount'],
                    $pm['percentage'].'%',
                ]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['PENDAPATAN PER LAYANAN']);
            fputcsv($handle, ['Nama Layanan', 'Kategori', 'Total Terjual (Qty)', 'Total Pendapatan (Rp)', 'Kontribusi (%)']);
            foreach ($report['services'] as $srv) {
                fputcsv($handle, [
                    $srv['service_name'],
                    $srv['category_name'],
                    $srv['total_qty'],
                    $srv['total_revenue'],
                    $srv['percentage'].'%',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
