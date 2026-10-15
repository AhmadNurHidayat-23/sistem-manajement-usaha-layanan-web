<?php

namespace App\Services\DataDemo;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Services\BookingAntrian\BookingService;

class PesananDemoService
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Orkestrasi order drop-off untuk Cabang B (Selatan) dan Cabang C (Barat).
     *
     * @param  array<string, User>  $customers
     * @param  (callable(string, string): void)|null  $logger
     * @return array<int, Order>
     */
    public function jalankan(array $customers, ?callable $logger = null): array
    {
        $branchB = Branch::where('name', 'like', '%Selatan%')->firstOrFail();
        $branchC = Branch::where('name', 'like', '%Barat%')->firstOrFail();

        $managerB = User::whereIn('email', ['manajer.laundry@simaul.com', 'manager.selatan@example.com'])->firstOrFail();
        $managerC = User::whereIn('email', ['manajer.autocare@simaul.com', 'manager.barat@example.com'])->firstOrFail();

        $defaultCustomer = $customers['Rian Saputra'] ?? reset($customers);

        $servicesB = Service::where('branch_id', $branchB->id)->get()->keyBy('name');
        $servicesC = Service::where('branch_id', $branchC->id)->get()->keyBy('name');

        $createdOrders = [];

        // 1. Order Cabang B (Laundry & Shoe Care)
        $orderDefsB = [
            ['cust' => 'Rina Maharani', 'service' => 'Cuci Kering Setrika', 'qty' => 5, 'date' => now()->subDays(15), 'status' => 'completed', 'notes' => 'Pakaian kerja dan kemeja rapi'],
            ['cust' => 'Siti Rahmawati', 'service' => 'Deep Clean Sepatu', 'qty' => 2, 'date' => now()->subDays(13), 'status' => 'completed', 'notes' => 'Sepatu putih kanvas & kulit'],
            ['cust' => 'Maya Lestari', 'service' => 'Dry Clean Jas & Kebaya', 'qty' => 2, 'date' => now()->subDays(11), 'status' => 'completed', 'notes' => 'Jas blazer kantor & kebaya pesta'],
            ['cust' => 'Nabila Putri', 'service' => 'Cuci Bed Cover', 'qty' => 1, 'date' => now()->subDays(9), 'status' => 'completed', 'notes' => 'Bed cover king size wangi floral'],
            ['cust' => 'Rina Maharani', 'service' => 'Laundry Express', 'qty' => 3, 'date' => now()->subDays(7), 'status' => 'completed', 'notes' => 'Cuci kilat kebutuhan mendesak'],
            ['cust' => 'Dian Anggraini', 'service' => 'Cuci Karpet', 'qty' => 1, 'date' => now()->subDays(5), 'status' => 'completed', 'notes' => 'Karpet ruang tamu ukuran 2x3m'],
            ['cust' => 'Siti Rahmawati', 'service' => 'Cuci Kering Setrika', 'qty' => 8, 'date' => now()->subDays(3), 'status' => 'completed', 'notes' => 'Pakaian santai keluarga'],
            ['cust' => 'Hendra Gunawan', 'service' => 'Deep Clean Sepatu', 'qty' => 3, 'date' => now()->subDays(2), 'status' => 'completed', 'notes' => 'Sepatu basket dan boots kulit'],
            ['cust' => 'Maya Lestari', 'service' => 'Laundry Express', 'qty' => 4, 'date' => now()->subDay(), 'status' => 'ready', 'notes' => 'Sudah dipacking rapi di rak B-12'],
            ['cust' => 'Nabila Putri', 'service' => 'Dry Clean Jas & Kebaya', 'qty' => 1, 'date' => now(), 'status' => 'processing', 'notes' => 'Sedang proses steaming dan pressing'],
            ['cust' => 'Bima Saputra', 'service' => 'Deep Clean Sepatu', 'qty' => 1, 'date' => now(), 'status' => 'received', 'notes' => 'Baru drop-off di meja kasir'],
            ['cust' => 'Eko Prasetyo', 'service' => 'Cuci Bed Cover', 'qty' => 2, 'date' => now(), 'status' => 'pending', 'notes' => 'Menunggu penimbangan dan kurir drop'],
        ];

        foreach ($orderDefsB as $def) {
            $order = $this->prosesOrder($def, $branchB, $managerB, $servicesB, $customers, $defaultCustomer, $logger);
            if ($order) {
                $createdOrders[] = $order;
            }
        }

        // 2. Order Cabang C (Auto Care - Drop-off)
        $orderDefsC = [
            ['cust' => 'Fajar Ramadhan', 'service' => 'Cuci Mobil Premium', 'qty' => 1, 'date' => now()->subDays(13), 'status' => 'completed', 'notes' => 'Cuci salju & pembersihan bodi'],
            ['cust' => 'Andi Pratama', 'service' => 'Cuci Helm', 'qty' => 2, 'date' => now()->subDays(11), 'status' => 'completed', 'notes' => 'Helm full-face & half-face wangi'],
            ['cust' => 'Nabila Putri', 'service' => 'Cuci Mobil Premium', 'qty' => 1, 'date' => now()->subDays(8), 'status' => 'completed', 'notes' => 'Cuci mobil city car'],
            ['cust' => 'Fajar Ramadhan', 'service' => 'Cuci Helm', 'qty' => 1, 'date' => now()->subDays(6), 'status' => 'completed', 'notes' => 'Helm touring harian'],
            ['cust' => 'Siti Rahmawati', 'service' => 'Perawatan Eksterior Mobil', 'qty' => 1, 'date' => now()->subDays(2), 'status' => 'completed', 'notes' => 'Pembersihan jamur kaca & wax bodi'],
            ['cust' => 'Arif Setiawan', 'service' => 'Cuci Mobil Premium', 'qty' => 1, 'date' => now(), 'status' => 'processing', 'notes' => 'Sedang dicuci di bay basah'],
            ['cust' => 'Bima Saputra', 'service' => 'Cuci Helm', 'qty' => 1, 'date' => now(), 'status' => 'received', 'notes' => 'Baru drop-off helm'],
        ];

        foreach ($orderDefsC as $def) {
            $order = $this->prosesOrder($def, $branchC, $managerC, $servicesC, $customers, $defaultCustomer, $logger);
            if ($order) {
                $createdOrders[] = $order;
            }
        }

        return $createdOrders;
    }

    /**
     * Buat atau perbarui pesanan drop-off secara idempotent melalui BookingService.
     *
     * @param  array<string, mixed>  $def
     * @param  array<string, User>  $customers
     */
    protected function prosesOrder(
        array $def,
        Branch $branch,
        User $manager,
        $services,
        array $customers,
        User $defaultCustomer,
        ?callable $logger
    ): ?Order {
        $cust = $customers[$def['cust']] ?? $defaultCustomer;
        $srv = $services[$def['service']] ?? $services->first();

        $existing = Order::where('user_id', $cust->id)
            ->where('branch_id', $branch->id)
            ->where('service_id', $srv->id)
            ->whereDate('created_at', $def['date'])
            ->first();

        if ($existing) {
            return $existing;
        }

        try {
            $order = $this->bookingService->createOrder($cust, [
                'branch_id' => $branch->id,
                'service_id' => $srv->id,
                'estimated_quantity' => $def['qty'],
                'notes' => $def['notes'],
            ]);

            if ($def['status'] !== 'pending') {
                $this->bookingService->updateOrderStatus($manager, $order, $def['status']);
            }

            return $order;
        } catch (\Throwable $e) {
            if ($logger) {
                $logger('warn', "Order {$branch->name} ({$def['cust']}) dilewati: {$e->getMessage()}");
            }

            return null;
        }
    }
}
