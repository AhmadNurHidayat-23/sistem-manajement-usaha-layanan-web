<?php

namespace App\Services\DataDemo;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use App\Modules\BookingAntrian\Services\BookingService;

class BookingDemoService
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Orkestrasi booking appointment untuk Cabang A (Pusat) dan Cabang C (Barat).
     *
     * @param  array<string, User>  $customers
     * @param  (callable(string, string): void)|null  $logger
     * @return array<int, Booking>
     */
    public function jalankan(array $customers, ?callable $logger = null): array
    {
        $branchA = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $branchC = Branch::where('name', 'like', '%Barat%')->firstOrFail();

        $managerA = User::whereIn('email', ['manajer@simaul.com', 'manager@example.com'])->firstOrFail();
        $managerC = User::whereIn('email', ['manajer.autocare@simaul.com', 'manager.barat@example.com'])->firstOrFail();

        $employeeA = Employee::where('branch_id', $branchA->id)->first();
        $employeeC = Employee::where('branch_id', $branchC->id)->first();

        $defaultCustomer = $customers['Rian Saputra'] ?? reset($customers);

        $servicesA = Service::where('branch_id', $branchA->id)->get()->keyBy('name');
        $servicesC = Service::where('branch_id', $branchC->id)->get()->keyBy('name');

        $createdBookings = [];

        // 1. Booking Cabang A (Barbershop & Studio)
        $bookingDefsA = [
            ['cust' => 'Andi Pratama', 'service' => 'Potong Rambut Pria', 'date' => now()->subDays(14)->toDateString(), 'time' => '10:00', 'status' => 'completed', 'notes' => 'Potong rapi gaya fade samping'],
            ['cust' => 'Bima Saputra', 'service' => 'Cukur & Rapikan Jenggot', 'date' => now()->subDays(12)->toDateString(), 'time' => '11:00', 'status' => 'completed', 'notes' => 'Trim jenggot presisi'],
            ['cust' => 'Dimas Nugroho', 'service' => 'Perawatan Kulit Kepala', 'date' => now()->subDays(10)->toDateString(), 'time' => '13:00', 'status' => 'completed', 'notes' => 'Perawatan kulit kepala'],
            ['cust' => 'Andi Pratama', 'service' => 'Cukur & Rapikan Jenggot', 'date' => now()->subDays(8)->toDateString(), 'time' => '14:00', 'status' => 'completed', 'notes' => 'Repeat booking grooming kumis & jenggot'],
            ['cust' => 'Fajar Ramadhan', 'service' => 'Potong Rambut & Styling', 'date' => now()->subDays(6)->toDateString(), 'time' => '15:00', 'status' => 'completed', 'notes' => 'Potong rambut dan styling pomade'],
            ['cust' => 'Arif Setiawan', 'service' => 'Paket Grooming Lengkap', 'date' => now()->subDays(4)->toDateString(), 'time' => '16:00', 'status' => 'completed', 'notes' => 'Paket komplit potong, jenggot, dan pijat'],
            ['cust' => 'Reza Firmansyah', 'service' => 'Pewarnaan Rambut', 'date' => now()->subDays(2)->toDateString(), 'time' => '10:00', 'status' => 'completed', 'notes' => 'Warna ash grey elegan'],
            ['cust' => 'Hendra Gunawan', 'service' => 'Potong Rambut Pria', 'date' => now()->toDateString(), 'time' => '11:00', 'status' => 'in_progress', 'notes' => 'Sedang potong rambut di kursi 1'],
            ['cust' => 'Bima Saputra', 'service' => 'Paket Grooming Lengkap', 'date' => now()->toDateString(), 'time' => '15:30', 'status' => 'confirmed', 'notes' => 'Konfirmasi via WhatsApp'],
            ['cust' => 'Dimas Nugroho', 'service' => 'Potong Rambut & Styling', 'date' => now()->addDay()->toDateString(), 'time' => '10:00', 'status' => 'confirmed', 'notes' => 'Jadwal booking besok pagi'],
            ['cust' => 'Eko Prasetyo', 'service' => 'Perawatan Kulit Kepala', 'date' => now()->addDays(2)->toDateString(), 'time' => '13:00', 'status' => 'pending', 'notes' => 'Menunggu verifikasi admin'],
            ['cust' => 'Arif Setiawan', 'service' => 'Pewarnaan Rambut', 'date' => now()->subDays(5)->toDateString(), 'time' => '15:00', 'status' => 'cancelled', 'notes' => 'Pelanggan mendadak berhalangan tugas'],
        ];

        foreach ($bookingDefsA as $def) {
            $booking = $this->prosesBooking($def, $branchA, $managerA, $employeeA, $servicesA, $customers, $defaultCustomer, $logger);
            if ($booking) {
                $createdBookings[] = $booking;
            }
        }

        // 2. Booking Cabang C (Auto Care - Appointment)
        $bookingDefsC = [
            ['cust' => 'Dimas Nugroho', 'service' => 'Detailing Mobil Lengkap', 'date' => now()->subDays(14)->toDateString(), 'time' => '09:00', 'status' => 'completed', 'notes' => 'Detailing exterior & paint correction Sedan'],
            ['cust' => 'Bima Saputra', 'service' => 'Nano Ceramic Coating', 'date' => now()->subDays(10)->toDateString(), 'time' => '10:00', 'status' => 'completed', 'notes' => 'Coating multi-layer SUV Hitam'],
            ['cust' => 'Reza Firmansyah', 'service' => 'Detailing Interior', 'date' => now()->subDays(7)->toDateString(), 'time' => '13:00', 'status' => 'completed', 'notes' => 'Pembersihan jok kulit & plafon aroma netral'],
            ['cust' => 'Dimas Nugroho', 'service' => 'Nano Ceramic Coating', 'date' => now()->subDays(3)->toDateString(), 'time' => '14:00', 'status' => 'completed', 'notes' => 'Perawatan berkala nano coating'],
            ['cust' => 'Arif Setiawan', 'service' => 'Detailing Mobil Lengkap', 'date' => now()->subDay()->toDateString(), 'time' => '09:00', 'status' => 'completed', 'notes' => 'Detailing komplit mobil hatchback'],
            ['cust' => 'Hendra Gunawan', 'service' => 'Detailing Interior', 'date' => now()->toDateString(), 'time' => '13:00', 'status' => 'in_progress', 'notes' => 'Sedang proses pembersihan interior di bay 2'],
            ['cust' => 'Andi Pratama', 'service' => 'Detailing Mobil Lengkap', 'date' => now()->addDay()->toDateString(), 'time' => '09:00', 'status' => 'confirmed', 'notes' => 'Jadwal pagi besok'],
        ];

        foreach ($bookingDefsC as $def) {
            $booking = $this->prosesBooking($def, $branchC, $managerC, $employeeC, $servicesC, $customers, $defaultCustomer, $logger);
            if ($booking) {
                $createdBookings[] = $booking;
            }
        }

        return $createdBookings;
    }

    /**
     * Buat atau perbarui booking secara idempotent melalui BookingService.
     *
     * @param  array<string, mixed>  $def
     * @param  array<string, User>  $customers
     */
    protected function prosesBooking(
        array $def,
        Branch $branch,
        User $manager,
        ?Employee $employee,
        $services,
        array $customers,
        User $defaultCustomer,
        ?callable $logger
    ): ?Booking {
        $cust = $customers[$def['cust']] ?? $defaultCustomer;
        $srv = $services[$def['service']] ?? $services->first();

        $existing = Booking::where('user_id', $cust->id)
            ->where('branch_id', $branch->id)
            ->whereDate('booking_date', $def['date'])
            ->where('booking_time', $def['time'])
            ->first();

        if ($existing) {
            return $existing;
        }

        try {
            $booking = $this->bookingService->createBooking($cust, [
                'branch_id' => $branch->id,
                'service_id' => $srv->id,
                'employee_id' => $employee?->id,
                'booking_date' => $def['date'],
                'booking_time' => $def['time'],
                'notes' => $def['notes'],
            ]);

            if ($def['status'] !== 'pending') {
                $this->bookingService->updateBookingStatus($manager, $booking, $def['status']);
            }

            return $booking;
        } catch (\Throwable $e) {
            if ($logger) {
                $logger('warn', "Booking {$branch->name} ({$def['cust']}) dilewati: {$e->getMessage()}");
            }

            return null;
        }
    }
}
