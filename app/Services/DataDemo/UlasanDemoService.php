<?php

namespace App\Services\DataDemo;

use App\Enums\StatusTransaksi;
use App\Models\Transaction;
use App\Modules\Katalog\Services\UlasanService;

class UlasanDemoService
{
    protected array $reviewTemplates = [
        5 => [
            'Pelayanannya cepat, staf ramah, dan hasilnya sangat rapi memuaskan.',
            'Hasil pengerjaan profesional dan rapi sekali. Sangat direkomendasikan!',
            'Tempatnya bersih, wangi, dan pelayanan sangat memuaskan. Pasti langganan.',
            'Hasilnya luar biasa glossy dan detail interiornya bersih sampai ke sela-sela.',
            'Sepatu saya kembali seperti baru keluar dari toko, bersih dan wangi!',
        ],
        4 => [
            'Hasil potong rapi dan sesuai instruksi. Sedikit antre tapi tetap worth it.',
            'Pencucian bersih dan pakaian wangi lembut. Pelayanan kasirnya juga cepat.',
            'Hasil cuci mobil kinclong, proses sedikit ramai di jam makan siang tapi oke.',
            'Teknisi ramah dan teliti saat detailing. Hasilnya bagus memuaskan.',
            'Proses pengerjaan tepat waktu sesuai estimasi. Sukses terus SIMAUL.',
        ],
        3 => [
            'Hasil cukup baik dan bersih, namun proses sedikit lebih lama dari perkiraan.',
            'Kualitas lumayan bagus, antrian di akhir pekan cukup padat.',
        ],
    ];

    protected array $ratingCycle = [5, 5, 4, 5, 4, 4, 5, 3, 5, 4, 5, 4, 5, 3, 4, 5, 5, 4, 5, 4];

    public function __construct(
        protected UlasanService $ulasanService
    ) {}

    /**
     * Orkestrasi ulasan / review pelanggan pada transaksi lunas.
     *
     * @param  (callable(string, string): void)|null  $logger
     */
    public function jalankan(?callable $logger = null): void
    {
        $paidTransactions = Transaction::with(['booking.user', 'order.user', 'details'])
            ->where('status', StatusTransaksi::Paid)
            ->doesntHave('ulasan')
            ->get();

        $rIdx = 0;

        foreach ($paidTransactions as $tx) {
            $customer = $tx->booking?->user ?? $tx->order?->user;
            if (! $customer) {
                continue;
            }

            $serviceId = $tx->details->first()?->service_id;
            if (! $serviceId) {
                continue;
            }

            $rating = $this->ratingCycle[$rIdx % count($this->ratingCycle)];
            $comments = $this->reviewTemplates[$rating];
            $komentar = $comments[$rIdx % count($comments)];
            $rIdx++;

            try {
                $this->ulasanService->createUlasan($customer, [
                    'transaction_id' => $tx->id,
                    'service_id' => $serviceId,
                    'employee_id' => $tx->booking?->employee_id ?? null,
                    'rating' => $rating,
                    'komentar' => $komentar,
                ]);
            } catch (\Throwable $e) {
                if ($logger) {
                    $logger('warn', "Ulasan TRX #{$tx->id} dilewati: {$e->getMessage()}");
                }
            }

            if ($rIdx >= 22) {
                break;
            }
        }
    }
}
