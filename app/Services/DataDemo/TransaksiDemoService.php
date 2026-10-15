<?php

namespace App\Services\DataDemo;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\User;
use App\Services\KasirPOS\KasirService;
use App\Services\KasirPOS\TransaksiService;
use App\Services\NotifikasiService;
use Carbon\Carbon;

class TransaksiDemoService
{
    protected array $paymentMethods = ['cash', 'qris', 'transfer', 'e-wallet'];

    public function __construct(
        protected KasirService $kasirService,
        protected TransaksiService $transaksiService,
        protected NotifikasiService $notifikasiService
    ) {}

    /**
     * Orkestrasi shift kasir, transaksi pembayaran, dan pengiriman notifikasi struk.
     *
     * @param  (callable(string, string): void)|null  $logger
     */
    public function jalankan(?callable $logger = null): void
    {
        $branchA = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $branchB = Branch::where('name', 'like', '%Selatan%')->firstOrFail();
        $branchC = Branch::where('name', 'like', '%Barat%')->firstOrFail();

        $cashierA = User::whereIn('email', ['kasir.andi@simaul.com', 'kasir@simaul.com', 'cashier@example.com'])->firstOrFail();
        $cashierB = User::whereIn('email', ['kasir.dewi@simaul.com', 'kasir.laundry@simaul.com', 'cashier.selatan@example.com'])->firstOrFail();
        $cashierC = User::whereIn('email', ['kasir.bagus@simaul.com', 'kasir.autocare@simaul.com', 'cashier.barat@example.com'])->firstOrFail();

        $promos = [
            'global' => Promotion::where('code', 'SIMAUL10')->first() ?: Promotion::where('code', 'DISKON10')->first(),
            'branchA' => Promotion::where('code', 'BARBER15K')->first(),
            'branchB' => Promotion::where('code', 'LAUNDRY10')->first(),
            'branchC' => Promotion::where('code', 'AUTOCARE20')->first(),
        ];

        $servicesA = Service::where('branch_id', $branchA->id)->get();
        $servicesB = Service::where('branch_id', $branchB->id)->get();
        $servicesC = Service::where('branch_id', $branchC->id)->get();

        $methodIdx = 0;

        // 1. Transaksi Cabang A (Pusat)
        $this->prosesSiklusCabangA($branchA, $cashierA, $servicesA, $promos, $methodIdx, $logger);

        // 2. Transaksi Cabang B (Selatan)
        $this->prosesSiklusCabangB($branchB, $cashierB, $servicesB, $promos, $methodIdx, $logger);

        // 3. Transaksi Cabang C (Barat)
        $this->prosesSiklusCabangC($branchC, $cashierC, $servicesC, $promos, $methodIdx, $logger);
    }

    protected function prosesSiklusCabangA(
        Branch $branch,
        User $cashier,
        $services,
        array $promos,
        int &$methodIdx,
        ?callable $logger
    ): void {
        $shift = $this->kasirService->getActiveShift($cashier, $branch->id);
        if (! $shift) {
            $shift = $this->kasirService->openShift($cashier, $branch->id, 250000);
        }

        $completedBookings = Booking::where('branch_id', $branch->id)
            ->where('status', BookingStatus::Completed)
            ->doesntHave('transaction')
            ->get();

        foreach ($completedBookings as $booking) {
            $method = $this->paymentMethods[$methodIdx % 4];
            $methodIdx++;

            $promoId = null;
            $discount = 0;

            if ($methodIdx % 3 === 0 && $promos['branchA']) {
                $promoId = $promos['branchA']->id;
                $discount = (float) $promos['branchA']->discount_value;
            } elseif ($methodIdx % 4 === 0 && $promos['global']) {
                $promoId = $promos['global']->id;
                $discount = (float) $booking->service->price * 0.10;
            }

            try {
                $tx = $this->transaksiService->createTransaction($cashier, [
                    'branch_id' => $branch->id,
                    'booking_id' => $booking->id,
                    'customer_name' => $booking->user?->name,
                    'customer_phone' => $booking->user?->phone,
                    'items' => [
                        ['service_id' => $booking->service_id, 'qty' => 1],
                    ],
                    'discount' => $discount,
                    'promotion_id' => $promoId,
                    'payment_method' => $method,
                    'cash_received' => $method === 'cash' ? ceil(($booking->service->price - $discount) / 50000) * 50000 : null,
                    'transaction_date' => Carbon::parse($booking->booking_date)->setTime(12, 0),
                ]);

                $this->notifikasiService->kirimStrukTransaksi($tx);
            } catch (\Throwable $e) {
                if ($logger) {
                    $logger('warn', "Trx Booking A (#{$booking->id}) dilewati: {$e->getMessage()}");
                }
            }
        }

        // Walk-in Cabang A
        $walkinServices = $services->values();
        for ($i = 0; $i < 2; $i++) {
            $srv = $walkinServices[$i % $walkinServices->count()];
            try {
                $this->transaksiService->createTransaction($cashier, [
                    'branch_id' => $branch->id,
                    'customer_name' => 'Pelanggan Walk-In Barber #'.($i + 1),
                    'customer_phone' => '0812999000'.($i + 1),
                    'items' => [['service_id' => $srv->id, 'qty' => 1]],
                    'payment_method' => 'cash',
                    'cash_received' => (float) $srv->price + 5000,
                    'transaction_date' => now()->subDays(3 - $i)->setTime(15, 30),
                ]);
            } catch (\Throwable $e) {
                // skip jika sudah tercatat
            }
        }

        $this->kasirService->closeShift($shift, $shift->opening_cash + 850000, 'Shift lancar, kas fisik klop.');
        $this->kasirService->openShift($cashier, $branch->id, 200000);
    }

    protected function prosesSiklusCabangB(
        Branch $branch,
        User $cashier,
        $services,
        array $promos,
        int &$methodIdx,
        ?callable $logger
    ): void {
        $shift = $this->kasirService->getActiveShift($cashier, $branch->id);
        if (! $shift) {
            $shift = $this->kasirService->openShift($cashier, $branch->id, 300000);
        }

        $completedOrders = Order::where('branch_id', $branch->id)
            ->where('status', StatusPesanan::Completed)
            ->doesntHave('transaction')
            ->get();

        foreach ($completedOrders as $order) {
            $method = $this->paymentMethods[$methodIdx % 4];
            $methodIdx++;

            $promoId = null;
            $discount = 0;
            $qty = (float) ($order->estimated_quantity ?: 1);
            $subtotal = (float) $order->service->price * $qty;

            if ($methodIdx % 2 === 0 && $promos['branchB']) {
                $promoId = $promos['branchB']->id;
                $discount = $subtotal * 0.10;
            }

            try {
                $tx = $this->transaksiService->createTransaction($cashier, [
                    'branch_id' => $branch->id,
                    'order_id' => $order->id,
                    'customer_name' => $order->user?->name,
                    'customer_phone' => $order->user?->phone,
                    'items' => [
                        ['service_id' => $order->service_id, 'qty' => $qty],
                    ],
                    'discount' => $discount,
                    'promotion_id' => $promoId,
                    'payment_method' => $method,
                    'cash_received' => $method === 'cash' ? ceil(($subtotal - $discount) / 10000) * 10000 : null,
                    'transaction_date' => Carbon::parse($order->created_at)->addDays(1)->setTime(16, 0),
                ]);

                $this->notifikasiService->kirimStrukTransaksi($tx);
            } catch (\Throwable $e) {
                if ($logger) {
                    $logger('warn', "Trx Order B (#{$order->id}) dilewati: {$e->getMessage()}");
                }
            }
        }

        // Walk-in Cabang B
        $walkinServices = $services->values();
        for ($i = 0; $i < 2; $i++) {
            $srv = $walkinServices[$i % $walkinServices->count()];
            try {
                $this->transaksiService->createTransaction($cashier, [
                    'branch_id' => $branch->id,
                    'customer_name' => 'Pelanggan Walk-In Laundry #'.($i + 1),
                    'customer_phone' => '0812999111'.($i + 1),
                    'items' => [['service_id' => $srv->id, 'qty' => 1]],
                    'payment_method' => 'qris',
                    'transaction_date' => now()->subDays(4 - $i)->setTime(11, 15),
                ]);
            } catch (\Throwable $e) {
                // skip jika sudah tercatat
            }
        }

        $this->kasirService->closeShift($shift, $shift->opening_cash + 620000, 'Shift selesai tanpa selisih kas.');
        $this->kasirService->openShift($cashier, $branch->id, 250000);
    }

    protected function prosesSiklusCabangC(
        Branch $branch,
        User $cashier,
        $services,
        array $promos,
        int &$methodIdx,
        ?callable $logger
    ): void {
        $shift = $this->kasirService->getActiveShift($cashier, $branch->id);
        if (! $shift) {
            $shift = $this->kasirService->openShift($cashier, $branch->id, 500000);
        }

        // 1. Transaksi dari Booking C
        $completedBookings = Booking::where('branch_id', $branch->id)
            ->where('status', BookingStatus::Completed)
            ->doesntHave('transaction')
            ->get();

        foreach ($completedBookings as $booking) {
            $method = $this->paymentMethods[$methodIdx % 4];
            $methodIdx++;

            $promoId = null;
            $discount = 0;
            $subtotal = (float) $booking->service->price;

            if ($methodIdx % 2 === 0 && $promos['branchC']) {
                $promoId = $promos['branchC']->id;
                $discount = $subtotal * 0.20;
            }

            try {
                $tx = $this->transaksiService->createTransaction($cashier, [
                    'branch_id' => $branch->id,
                    'booking_id' => $booking->id,
                    'customer_name' => $booking->user?->name,
                    'customer_phone' => $booking->user?->phone,
                    'items' => [
                        ['service_id' => $booking->service_id, 'qty' => 1],
                    ],
                    'discount' => $discount,
                    'promotion_id' => $promoId,
                    'payment_method' => $method,
                    'cash_received' => $method === 'cash' ? ceil(($subtotal - $discount) / 100000) * 100000 : null,
                    'transaction_date' => Carbon::parse($booking->booking_date)->setTime(17, 0),
                ]);

                $this->notifikasiService->kirimStrukTransaksi($tx);
            } catch (\Throwable $e) {
                if ($logger) {
                    $logger('warn', "Trx Booking C (#{$booking->id}) dilewati: {$e->getMessage()}");
                }
            }
        }

        // 2. Transaksi dari Order C
        $completedOrders = Order::where('branch_id', $branch->id)
            ->where('status', StatusPesanan::Completed)
            ->doesntHave('transaction')
            ->get();

        foreach ($completedOrders as $order) {
            $method = $this->paymentMethods[$methodIdx % 4];
            $methodIdx++;

            $qty = (float) ($order->estimated_quantity ?: 1);
            $subtotal = (float) $order->service->price * $qty;

            try {
                $tx = $this->transaksiService->createTransaction($cashier, [
                    'branch_id' => $branch->id,
                    'order_id' => $order->id,
                    'customer_name' => $order->user?->name,
                    'customer_phone' => $order->user?->phone,
                    'items' => [
                        ['service_id' => $order->service_id, 'qty' => $qty],
                    ],
                    'payment_method' => $method,
                    'cash_received' => $method === 'cash' ? ceil($subtotal / 50000) * 50000 : null,
                    'transaction_date' => Carbon::parse($order->created_at)->setTime(15, 0),
                ]);

                $this->notifikasiService->kirimStrukTransaksi($tx);
            } catch (\Throwable $e) {
                if ($logger) {
                    $logger('warn', "Trx Order C (#{$order->id}) dilewati: {$e->getMessage()}");
                }
            }
        }

        // 3. Walk-in Cabang C
        $walkinServices = $services->values();
        for ($i = 0; $i < 2; $i++) {
            $srv = $walkinServices[$i % $walkinServices->count()];
            try {
                $this->transaksiService->createTransaction($cashier, [
                    'branch_id' => $branch->id,
                    'customer_name' => 'Pelanggan Walk-In Auto #'.($i + 1),
                    'customer_phone' => '0812999222'.($i + 1),
                    'items' => [['service_id' => $srv->id, 'qty' => 1]],
                    'payment_method' => 'transfer',
                    'transaction_date' => now()->subDays(2 - $i)->setTime(14, 0),
                ]);
            } catch (\Throwable $e) {
                // skip jika sudah tercatat
            }
        }

        $this->kasirService->closeShift($shift, $shift->opening_cash + 2150000, 'Shift kasir detailing klop.');
        $this->kasirService->openShift($cashier, $branch->id, 400000);
    }
}
