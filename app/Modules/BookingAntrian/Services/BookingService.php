<?php

namespace App\Modules\BookingAntrian\Services;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Enums\StatusShiftKaryawan;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    // ─── BOOKING (APPOINTMENT) ─────────────────────────────────────────────────

    /**
     * Daftar booking pengguna.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getBookingsForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::query()
            ->with(['user', 'branch', 'service', 'employee'])
            ->latest();

        $roleName = $user->role?->name;

        if ($roleName === 'customer') {
            $query->where('user_id', $user->id);
        } elseif ($roleName === 'employee') {
            $employeeId = $user->employee?->id;
            $query->where('employee_id', $employeeId);
        } elseif (in_array($roleName, ['manager', 'cashier'], true)) {
            $branchIds = $user->branches()->pluck('branches.id');
            if ($branchIds->isEmpty() && $user->employee?->branch_id) {
                $branchIds = collect([$user->employee->branch_id]);
            }
            $query->whereIn('branch_id', $branchIds);
        }
        // owner sees all

        $this->applyBookingFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Get bookings for a specific date (antrian harian).
     *
     * @param  array<string, mixed>  $filters
     */
    public function getDailyQueue(User $user, string $date, array $filters = []): Collection
    {
        $query = Booking::query()
            ->with(['user', 'service', 'employee'])
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', [BookingStatus::Cancelled->value])
            ->orderBy('booking_time');

        $roleName = $user->role?->name;

        if ($roleName === 'employee') {
            $query->where('employee_id', $user->employee?->id);
        } elseif (in_array($roleName, ['manager', 'cashier'], true)) {
            $branchIds = $user->branches()->pluck('branches.id');
            if ($branchIds->isEmpty() && $user->employee?->branch_id) {
                $branchIds = collect([$user->employee->branch_id]);
            }
            $query->whereIn('branch_id', $branchIds);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        return $query->get();
    }

    /**
     * Buat booking baru.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \RuntimeException
     */
    public function createBooking(User $user, array $data): Booking
    {
        return DB::transaction(function () use ($user, $data) {
            $branch = Branch::where('id', $data['branch_id'])->lockForUpdate()->first();
            if (! $branch) {
                throw new \RuntimeException('Cabang tidak ditemukan.');
            }
            if (! in_array($branch->service_mode, ['appointment', 'both'], true)) {
                throw new \RuntimeException('Cabang ini tidak mendukung layanan booking janji temu.');
            }

            // Kunci baris booking cabang & tanggal ini agar validasi kapasitas bebas dari bentrok bersamaan
            Booking::where('branch_id', $data['branch_id'])
                ->whereDate('booking_date', $data['booking_date'])
                ->lockForUpdate()
                ->get();

            $this->validateSlotAvailability(
                (int) $data['branch_id'],
                $data['booking_date'],
                $data['booking_time'],
                null,
                ! empty($data['employee_id']) ? (int) $data['employee_id'] : null,
                ! empty($data['service_id']) ? (int) $data['service_id'] : null,
                ! empty($data['extra_service_ids']) ? (array) $data['extra_service_ids'] : [],
            );

            $booking = Booking::create([
                'user_id' => $user->id,
                'branch_id' => $data['branch_id'],
                'service_id' => $data['service_id'],
                'employee_id' => $data['employee_id'] ?? null,
                'booking_date' => $data['booking_date'],
                'booking_time' => $data['booking_time'],
                'status' => BookingStatus::Pending,
                'notes' => $data['notes'] ?? null,
            ]);

            // Store extra services as BookingDetail rows
            if (! empty($data['extra_service_ids'])) {
                $this->syncBookingDetails($booking, $data['extra_service_ids']);
            }

            return $booking->load(['branch', 'service', 'employee']);
        });
    }

    /**
     * Perbarui booking.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException|\RuntimeException
     */
    public function updateBooking(User $user, Booking $booking, array $data): Booking
    {
        $this->authorizeBookingModification($user, $booking);

        $branchId = (int) ($data['branch_id'] ?? $booking->branch_id);
        $date = $data['booking_date'] ?? $booking->booking_date->toDateString();
        $time = $data['booking_time'] ?? (string) $booking->booking_time;
        $employeeId = array_key_exists('employee_id', $data)
            ? (! empty($data['employee_id']) ? (int) $data['employee_id'] : null)
            : ($booking->employee_id ? (int) $booking->employee_id : null);
        $serviceId = (int) ($data['service_id'] ?? $booking->service_id);
        $extraServiceIds = array_key_exists('extra_service_ids', $data)
            ? (array) $data['extra_service_ids']
            : $booking->details()->pluck('service_id')->toArray();

        // Validasi ulang slot jika jadwal, petugas, cabang, layanan, atau extras berubah
        $dateChanged = $date !== $booking->booking_date->toDateString();
        $timeChanged = substr($time, 0, 5) !== substr((string) $booking->booking_time, 0, 5);
        $empChanged = $employeeId !== ($booking->employee_id ? (int) $booking->employee_id : null);
        $branchChanged = $branchId !== (int) $booking->branch_id;
        $serviceChanged = $serviceId !== (int) $booking->service_id;
        $extrasChanged = array_key_exists('extra_service_ids', $data);

        return DB::transaction(function () use ($booking, $data, $branchId, $date, $time, $employeeId, $serviceId, $extraServiceIds, $dateChanged, $timeChanged, $empChanged, $branchChanged, $serviceChanged, $extrasChanged) {
            Branch::where('id', $branchId)->lockForUpdate()->first();

            Booking::where('branch_id', $branchId)
                ->whereDate('booking_date', $date)
                ->lockForUpdate()
                ->get();

            if ($dateChanged || $timeChanged || $empChanged || $branchChanged || $serviceChanged || $extrasChanged) {
                $this->validateSlotAvailability(
                    $branchId,
                    $date,
                    $time,
                    $booking->id,
                    $employeeId,
                    $serviceId,
                    $extraServiceIds,
                );
            }

            $booking->update([
                'branch_id' => $data['branch_id'] ?? $booking->branch_id,
                'service_id' => $data['service_id'] ?? $booking->service_id,
                'employee_id' => $data['employee_id'] ?? null,
                'booking_date' => $data['booking_date'],
                'booking_time' => $data['booking_time'],
                'notes' => $data['notes'] ?? null,
            ]);

            if (isset($data['extra_service_ids'])) {
                $this->syncBookingDetails($booking, $data['extra_service_ids']);
            }

            return $booking->fresh(['branch', 'service', 'employee']);
        });
    }

    /**
     * Batalkan booking.
     *
     * @throws AuthorizationException
     */
    public function cancelBooking(User $user, Booking $booking): Booking
    {
        $this->authorizeBookingModification($user, $booking);

        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Confirmed])) {
            throw new \RuntimeException('Booking tidak dapat dibatalkan pada status saat ini.');
        }

        $booking->update(['status' => BookingStatus::Cancelled]);

        return $booking;
    }

    /**
     * Update status booking.
     *
     * @throws AuthorizationException
     */
    public function updateBookingStatus(User $user, Booking $booking, string $status): Booking
    {
        $this->authorizeStatusUpdate($user, $booking);

        $booking->update(['status' => $status]);

        return $booking;
    }

    // ─── ORDER (DROP-OFF) ──────────────────────────────────────────────────────

    /**
     * Daftar pesanan pengguna.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getOrdersForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::query()
            ->with(['user', 'branch', 'service'])
            ->latest();

        $roleName = $user->role?->name;

        if ($roleName === 'customer') {
            $query->where('user_id', $user->id);
        } elseif (in_array($roleName, ['manager', 'cashier'], true)) {
            $branchIds = $user->branches()->pluck('branches.id');
            if ($branchIds->isEmpty() && $user->employee?->branch_id) {
                $branchIds = collect([$user->employee->branch_id]);
            }
            $query->whereIn('branch_id', $branchIds);
        } elseif ($roleName === 'employee') {
            $branchId = $user->employee?->branch_id;
            if ($branchId) {
                $query->where('branch_id', $branchId);
            } else {
                $branchIds = $user->branches()->pluck('branches.id');
                $query->whereIn('branch_id', $branchIds);
            }
        }
        // owner sees all

        $this->applyOrderFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Antrian pesanan drop-off hari ini.
     */
    public function getDailyOrderQueue(User $user, string $date, array $filters = []): Collection
    {
        $query = Order::query()
            ->with(['user', 'service'])
            ->whereDate('created_at', $date)
            ->whereNotIn('status', [StatusPesanan::Cancelled->value])
            ->latest();

        $roleName = $user->role?->name;

        if (in_array($roleName, ['manager', 'cashier'], true)) {
            $branchIds = $user->branches()->pluck('branches.id');
            if ($branchIds->isEmpty() && $user->employee?->branch_id) {
                $branchIds = collect([$user->employee->branch_id]);
            }
            $query->whereIn('branch_id', $branchIds);
        } elseif ($roleName === 'employee') {
            $branchId = $user->employee?->branch_id;
            if ($branchId) {
                $query->where('branch_id', $branchId);
            } else {
                $branchIds = $user->branches()->pluck('branches.id');
                $query->whereIn('branch_id', $branchIds);
            }
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        return $query->get();
    }

    /**
     * Buat pesanan drop-off.
     *
     * @param  array<string, mixed>  $data
     */
    public function createOrder(User $user, array $data): Order
    {
        $branch = Branch::find($data['branch_id']);
        if ($branch && ! in_array($branch->service_mode, ['drop_off', 'both'], true)) {
            throw new \RuntimeException('Cabang ini tidak mendukung layanan drop-off.');
        }

        $service = Service::find($data['service_id']);
        $estimatedFinish = $this->calculateEstimatedFinish($service, $data);

        $order = Order::create([
            'user_id' => $user->id,
            'branch_id' => $data['branch_id'],
            'service_id' => $data['service_id'],
            'estimated_quantity' => $data['estimated_quantity'] ?? null,
            'pickup_option' => isset($data['pickup_option']) && $data['pickup_option'],
            'estimated_finish_at' => $estimatedFinish,
            'status' => StatusPesanan::Pending,
            'notes' => $data['notes'] ?? null,
        ]);

        if (! empty($data['extra_service_ids'])) {
            $this->syncOrderDetails($order, $data['extra_service_ids']);
        }

        return $order->load(['branch', 'service']);
    }

    /**
     * Update status pesanan.
     *
     * @throws AuthorizationException
     */
    public function updateOrderStatus(User $user, Order $order, string $status): Order
    {
        $this->authorizeOrderStatusUpdate($user, $order);

        $changes = ['status' => $status];

        // Estimasi waktu pengerjaan
        if ($status === StatusPesanan::Processing->value && is_null($order->estimated_finish_at)) {
            $service = $order->service;
            $minutes = $service?->estimated_duration ?? 60;
            $changes['estimated_finish_at'] = now()->addMinutes($minutes);
        }

        $order->update($changes);

        return $order;
    }

    /**
     * Cancel a drop-off order.
     *
     * @throws AuthorizationException|\RuntimeException
     */
    public function cancelOrder(User $user, Order $order): Order
    {
        $roleName = $user->role?->name;

        $isOwner = $roleName === 'owner' || $order->user_id === $user->id;

        if (! $isOwner) {
            throw new AuthorizationException('Anda tidak dapat membatalkan order ini.');
        }

        if (! in_array($order->status->value, [StatusPesanan::Pending->value, StatusPesanan::Received->value])) {
            throw new \RuntimeException('Order tidak dapat dibatalkan pada status saat ini.');
        }

        $order->update(['status' => StatusPesanan::Cancelled]);

        return $order;
    }

    // ─── HELPERS ──────────────────────────────────────────────────────────────

    /**
     * Daftar cabang aktif.
     */
    public function getActiveBranches(?string $serviceMode = null): Collection
    {
        $query = Branch::where('is_active', true)->orderBy('name');

        if ($serviceMode) {
            $query->whereIn('service_mode', [$serviceMode, 'both']);
        }

        return $query->get();
    }

    /**
     * Daftar layanan aktif.
     */
    public function getActiveServices(?int $branchId = null): Collection
    {
        $query = Service::with('category')
            ->where('is_active', true)
            ->orderBy('name');

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    /**
     * Daftar karyawan cabang.
     */
    public function getBranchEmployees(int $branchId): Collection
    {
        return Employee::where('branch_id', $branchId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Hitung durasi estimasi layanan dalam menit (minimal 30 menit).
     *
     * @param  array<int>  $extraServiceIds
     */
    public function calculateDuration(?int $serviceId, array $extraServiceIds = []): int
    {
        $duration = 0;
        if ($serviceId) {
            $service = Service::find($serviceId);
            $duration += (int) ($service?->estimated_duration ?? 0);
        }

        if (! empty($extraServiceIds)) {
            $extraDuration = (int) Service::whereIn('id', $extraServiceIds)->sum('estimated_duration');
            $duration += $extraDuration;
        }

        return $duration > 0 ? $duration : 30;
    }

    /**
     * Ambil total durasi booking termasuk detail layanan tambahannya (minimal 30 menit).
     */
    public function getBookingDuration(Booking $booking): int
    {
        $duration = (int) ($booking->service?->estimated_duration ?? 0);
        if ($booking->relationLoaded('details')) {
            $extraIds = $booking->details->pluck('service_id')->toArray();
        } else {
            $extraIds = $booking->details()->pluck('service_id')->toArray();
        }

        if (! empty($extraIds)) {
            $duration += (int) Service::whereIn('id', $extraIds)->sum('estimated_duration');
        }

        return $duration > 0 ? $duration : 30;
    }

    /**
     * Slot waktu yang tersedia berdasarkan jam cabang, shift petugas (opsional), durasi layanan, dan kapasitas.
     *
     * @param  array<int>  $extraServiceIds
     * @return array<string>
     */
    public function getAvailableSlots(
        Branch $branch,
        string $date,
        ?int $excludeBookingId = null,
        ?int $employeeId = null,
        ?int $serviceId = null,
        array $extraServiceIds = []
    ): array {
        $duration = $this->calculateDuration($serviceId, $extraServiceIds);
        $branchOpen = Carbon::parse($date.' '.($branch->opening_time ?? '08:00'));
        $branchClose = Carbon::parse($date.' '.($branch->closing_time ?? '17:00'));
        $capacity = $branch->max_slot_capacity ?? 1;

        $windowStart = $branchOpen->copy();
        $windowEnd = $branchClose->copy();

        $empBookings = collect();

        if ($employeeId) {
            $shift = EmployeeShift::where('employee_id', $employeeId)
                ->where('branch_id', $branch->id)
                ->whereDate('shift_date', $date)
                ->whereIn('status', [StatusShiftKaryawan::Scheduled, StatusShiftKaryawan::Present, StatusShiftKaryawan::Late])
                ->first();

            if (! $shift) {
                return [];
            }

            $shiftStart = Carbon::parse($date.' '.$shift->start_time);
            $shiftEnd = Carbon::parse($date.' '.$shift->end_time);

            $windowStart = $branchOpen->greaterThan($shiftStart) ? $branchOpen->copy() : $shiftStart->copy();
            $windowEnd = $branchClose->lessThan($shiftEnd) ? $branchClose->copy() : $shiftEnd->copy();

            $empBookings = Booking::with(['service', 'details'])
                ->where('employee_id', $employeeId)
                ->whereDate('booking_date', $date)
                ->whereNotIn('status', [BookingStatus::Cancelled->value])
                ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
                ->get();
        }

        $branchBookings = Booking::with(['service', 'details'])
            ->where('branch_id', $branch->id)
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', [BookingStatus::Cancelled->value])
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->get();

        $slots = [];
        $current = $windowStart->copy();

        while ($current->copy()->addMinutes($duration)->lte($windowEnd)) {
            $candidateStart = $current->copy();
            $candidateEnd = $candidateStart->copy()->addMinutes($duration);

            $empClash = $employeeId
                ? $this->hasEmployeeConflict($empBookings, $date, $candidateStart, $candidateEnd)
                : false;

            if (! $empClash) {
                $overCapacitySlot = $this->getFirstOverCapacitySlot(
                    $branchBookings,
                    $date,
                    $candidateStart,
                    $candidateEnd,
                    $capacity
                );

                if ($overCapacitySlot === null) {
                    $slots[] = $candidateStart->format('H:i');
                }
            }

            $current->addMinutes(30);
        }

        return $slots;
    }

    /**
     * Dapatkan daftar seluruh slot waktu dengan status lengkap:
     * - 'available': Putih, dapat diklik
     * - 'full': Merah lembut, tidak dapat diklik
     * - 'past': Abu-abu, sudah lewat
     * - 'outside_shift': Abu-abu, di luar jam buka/shift
     *
     * @param  array<int>  $extraServiceIds
     * @return array<int, array<string, mixed>>
     */
    public function getSlotsWithStatus(
        Branch $branch,
        string $date,
        ?int $excludeBookingId = null,
        ?int $employeeId = null,
        ?int $serviceId = null,
        array $extraServiceIds = []
    ): array {
        $duration = $this->calculateDuration($serviceId, $extraServiceIds);
        $branchOpen = Carbon::parse($date.' '.($branch->opening_time ?? '08:00'));
        $branchClose = Carbon::parse($date.' '.($branch->closing_time ?? '17:00'));
        $capacity = (int) ($branch->max_slot_capacity ?? 1);

        $shift = null;
        $shiftStart = null;
        $shiftEnd = null;
        $employeeHasNoShift = false;

        $empBookings = collect();

        if ($employeeId) {
            $shift = EmployeeShift::where('employee_id', $employeeId)
                ->where('branch_id', $branch->id)
                ->whereDate('shift_date', $date)
                ->whereIn('status', [StatusShiftKaryawan::Scheduled, StatusShiftKaryawan::Present, StatusShiftKaryawan::Late])
                ->first();

            if (! $shift) {
                $employeeHasNoShift = true;
            } else {
                $shiftStart = Carbon::parse($date.' '.$shift->start_time);
                $shiftEnd = Carbon::parse($date.' '.$shift->end_time);
            }

            $empBookings = Booking::with(['service', 'details'])
                ->where('employee_id', $employeeId)
                ->whereDate('booking_date', $date)
                ->whereNotIn('status', [BookingStatus::Cancelled->value])
                ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
                ->get();
        }

        $branchBookings = Booking::with(['service', 'details'])
            ->where('branch_id', $branch->id)
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', [BookingStatus::Cancelled->value])
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->get();

        $slots = [];
        $current = $branchOpen->copy();
        $now = now();

        while ($current->lt($branchClose)) {
            $candidateStart = $current->copy();
            $candidateEnd = $candidateStart->copy()->addMinutes($duration);
            $timeString = $candidateStart->format('H:i');

            // 1. Cek apakah waktu sudah lewat
            $isPast = false;
            if ($date < $now->toDateString()) {
                $isPast = true;
            } elseif ($date === $now->toDateString() && $candidateStart->lte($now)) {
                $isPast = true;
            }

            // 2. Cek apakah di luar jadwal operasional / shift
            $isOutsideShift = false;
            $outsideReason = '';

            if ($candidateEnd->gt($branchClose)) {
                $isOutsideShift = true;
                $outsideReason = 'Melebihi jam operasional cabang';
            } elseif ($employeeId) {
                if ($employeeHasNoShift) {
                    $isOutsideShift = true;
                    $outsideReason = 'Petugas libur / tidak bertugas';
                } elseif ($candidateStart->lt($shiftStart) || $candidateEnd->gt($shiftEnd)) {
                    $isOutsideShift = true;
                    $outsideReason = 'Di luar jam shift petugas';
                }
            }

            // 3. Cek bentrok petugas atau kapasitas penuh
            $isFull = false;
            $fullReason = '';

            if (! $isPast && ! $isOutsideShift) {
                if ($employeeId && $this->hasEmployeeConflict($empBookings, $date, $candidateStart, $candidateEnd)) {
                    $isFull = true;
                    $fullReason = 'Petugas sudah dibooking';
                } else {
                    $overCapacitySlot = $this->getFirstOverCapacitySlot(
                        $branchBookings,
                        $date,
                        $candidateStart,
                        $candidateEnd,
                        $capacity
                    );

                    if ($overCapacitySlot !== null) {
                        $isFull = true;
                        $fullReason = 'Kapasitas slot penuh';
                    }
                }
            }

            // Hitung kapasitas tersisa pada rentang slot
            $segment = $candidateStart->copy();
            $maxActiveBookings = 0;
            while ($segment->lt($candidateEnd)) {
                $segFinish = $segment->copy()->addMinutes(30);
                $activeCount = 0;
                foreach ($branchBookings as $bb) {
                    $bbStart = Carbon::parse($date.' '.$bb->booking_time);
                    $bbDuration = $this->getBookingDuration($bb);
                    $bbEnd = $bbStart->copy()->addMinutes($bbDuration);
                    if ($segment->lt($bbEnd) && $segFinish->gt($bbStart)) {
                        $activeCount++;
                    }
                }
                if ($activeCount > $maxActiveBookings) {
                    $maxActiveBookings = $activeCount;
                }
                $segment->addMinutes(30);
            }
            $remainingCapacity = max(0, $capacity - $maxActiveBookings);

            // Tentukan status akhir
            if ($isPast) {
                $status = 'past';
                $message = 'Waktu sudah lewat';
            } elseif ($isOutsideShift) {
                $status = 'outside_shift';
                $message = $outsideReason ?: 'Di luar jadwal';
            } elseif ($isFull) {
                $status = 'full';
                $message = $fullReason ?: 'Penuh';
            } else {
                $status = 'available';
                $message = 'Tersedia';
            }

            $slots[] = [
                'time' => $timeString,
                'status' => $status,
                'is_available' => $status === 'available',
                'is_full' => $status === 'full',
                'is_past' => $status === 'past',
                'is_outside_shift' => $status === 'outside_shift',
                'booked_count' => $maxActiveBookings,
                'max_capacity' => $capacity,
                'remaining_capacity' => $status === 'available' ? $remainingCapacity : 0,
                'message' => $message,
            ];

            $current->addMinutes(30);
        }

        return $slots;
    }

    // ─── VALIDATION METHODS ───────────────────────────────────────────────────

    /**
     * Validasi ketersediaan slot, shift petugas, dan kapasitas cabang.
     *
     * @param  array<int>  $extraServiceIds
     *
     * @throws \RuntimeException
     */
    public function validateSlotAvailability(
        int $branchId,
        string $date,
        string $time,
        ?int $excludeBookingId = null,
        ?int $employeeId = null,
        ?int $serviceId = null,
        array $extraServiceIds = []
    ): void {
        $branch = Branch::findOrFail($branchId);
        $duration = $this->calculateDuration($serviceId, $extraServiceIds);
        $bookingStart = Carbon::parse($date.' '.$time);
        $bookingEnd = $bookingStart->copy()->addMinutes($duration);

        // 1. Validasi jam operasional cabang
        $branchOpen = Carbon::parse($date.' '.($branch->opening_time ?? '08:00'));
        $branchClose = Carbon::parse($date.' '.($branch->closing_time ?? '17:00'));

        if ($bookingStart->lt($branchOpen) || $bookingEnd->gt($branchClose)) {
            throw new \RuntimeException(
                "Waktu booking ({$bookingStart->format('H:i')} - {$bookingEnd->format('H:i')}) berada di luar jam operasional cabang ({$branchOpen->format('H:i')} - {$branchClose->format('H:i')})."
            );
        }

        // 2. Validasi jadwal shift petugas (bila memilih petugas)
        if ($employeeId) {
            $employee = Employee::find($employeeId);
            if (! $employee) {
                throw new \RuntimeException('Petugas yang dipilih tidak ditemukan.');
            }

            $shift = EmployeeShift::where('employee_id', $employeeId)
                ->where('branch_id', $branchId)
                ->whereDate('shift_date', $date)
                ->whereIn('status', [StatusShiftKaryawan::Scheduled, StatusShiftKaryawan::Present, StatusShiftKaryawan::Late])
                ->first();

            if (! $shift) {
                throw new \RuntimeException('Petugas tidak memiliki jadwal kerja aktif pada tanggal dan cabang yang dipilih.');
            }

            $shiftStart = Carbon::parse($date.' '.$shift->start_time);
            $shiftEnd = Carbon::parse($date.' '.$shift->end_time);

            if ($bookingStart->lt($shiftStart) || $bookingEnd->gt($shiftEnd)) {
                throw new \RuntimeException(
                    "Waktu booking ({$bookingStart->format('H:i')} - {$bookingEnd->format('H:i')}) berada di luar jam kerja shift petugas ({$shiftStart->format('H:i')} - {$shiftEnd->format('H:i')})."
                );
            }

            // Validasi bentrok jadwal petugas (lintas cabang)
            $existingEmployeeBookings = Booking::with(['service', 'details'])
                ->where('employee_id', $employeeId)
                ->whereDate('booking_date', $date)
                ->whereNotIn('status', [BookingStatus::Cancelled->value])
                ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
                ->get();

            $conflictingBooking = null;
            if ($this->hasEmployeeConflict($existingEmployeeBookings, $date, $bookingStart, $bookingEnd, $conflictingBooking)) {
                $ebStart = Carbon::parse($date.' '.$conflictingBooking->booking_time);
                $ebDuration = $this->getBookingDuration($conflictingBooking);
                $ebEnd = $ebStart->copy()->addMinutes($ebDuration);

                throw new \RuntimeException(
                    "Petugas yang dipilih sudah memiliki jadwal pada rentang waktu {$ebStart->format('H:i')} - {$ebEnd->format('H:i')}."
                );
            }
        }

        // 3. Validasi kapasitas slot cabang pada seluruh interval durasi booking
        $capacity = $branch->max_slot_capacity ?? 1;

        $existingBranchBookings = Booking::with(['service', 'details'])
            ->where('branch_id', $branchId)
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', [BookingStatus::Cancelled->value])
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->get();

        $overCapacitySlot = $this->getFirstOverCapacitySlot(
            $existingBranchBookings,
            $date,
            $bookingStart,
            $bookingEnd,
            $capacity
        );

        if ($overCapacitySlot !== null) {
            throw new \RuntimeException("Slot waktu {$overCapacitySlot} pada tanggal {$date} sudah penuh.");
        }
    }

    /**
     * Periksa apakah terdapat bentrok jadwal booking petugas pada rentang waktu yang diberikan.
     *
     * @param  Collection<int, Booking>  $employeeBookings
     */
    private function hasEmployeeConflict(
        Collection $employeeBookings,
        string $date,
        Carbon $start,
        Carbon $end,
        ?Booking &$conflictingBooking = null
    ): bool {
        foreach ($employeeBookings as $eb) {
            $ebStart = Carbon::parse($date.' '.$eb->booking_time);
            $ebDuration = $this->getBookingDuration($eb);
            $ebEnd = $ebStart->copy()->addMinutes($ebDuration);

            if ($start->lt($ebEnd) && $end->gt($ebStart)) {
                $conflictingBooking = $eb;

                return true;
            }
        }

        return false;
    }

    /**
     * Periksa apakah kapasitas cabang terlampaui pada salah satu sub-interval 30 menit.
     * Mengembalikan string slot waktu pertama yang penuh (format 'H:i'), atau null jika aman.
     *
     * @param  Collection<int, Booking>  $branchBookings
     */
    private function getFirstOverCapacitySlot(
        Collection $branchBookings,
        string $date,
        Carbon $start,
        Carbon $end,
        int $capacity
    ): ?string {
        $segment = $start->copy();

        while ($segment->lt($end)) {
            $segFinish = $segment->copy()->addMinutes(30);
            $activeCount = 0;

            foreach ($branchBookings as $bb) {
                $bbStart = Carbon::parse($date.' '.$bb->booking_time);
                $bbDuration = $this->getBookingDuration($bb);
                $bbEnd = $bbStart->copy()->addMinutes($bbDuration);

                if ($segment->lt($bbEnd) && $segFinish->gt($bbStart)) {
                    $activeCount++;
                }
            }

            if ($activeCount >= $capacity) {
                return $segment->format('H:i');
            }

            $segment->addMinutes(30);
        }

        return null;
    }

    /**
     * Sinkronisasi detail booking.
     *
     * @param  array<int>  $serviceIds
     */
    private function syncBookingDetails(Booking $booking, array $serviceIds): void
    {
        $booking->details()->delete();

        $services = Service::whereIn('id', $serviceIds)->get()->keyBy('id');

        foreach ($serviceIds as $serviceId) {
            $service = $services[$serviceId] ?? null;
            if (! $service) {
                continue;
            }

            BookingDetail::create([
                'booking_id' => $booking->id,
                'service_id' => $service->id,
                'qty' => 1,
                'price' => $service->price,
                'subtotal' => $service->price,
            ]);
        }
    }

    /**
     * Sinkronisasi detail pesanan.
     *
     * @param  array<int>  $serviceIds
     */
    private function syncOrderDetails(Order $order, array $serviceIds): void
    {
        $order->details()->delete();

        $services = Service::whereIn('id', $serviceIds)->get()->keyBy('id');

        foreach ($serviceIds as $serviceId) {
            $service = $services[$serviceId] ?? null;
            if (! $service) {
                continue;
            }

            OrderDetail::create([
                'order_id' => $order->id,
                'service_id' => $service->id,
                'qty' => 1,
                'price' => $service->price,
                'subtotal' => $service->price,
            ]);
        }
    }

    /**
     * Estimasi waktu selesai pesanan.
     *
     * @param  array<string, mixed>  $data
     */
    private function calculateEstimatedFinish(?Service $service, array $data): ?Carbon
    {
        if (! $service) {
            return null;
        }

        $minutes = $service->estimated_duration ?? 60;

        return now()->addMinutes($minutes);
    }

    /**
     * Filter query booking.
     *
     * @param  array<string, mixed>  $filters
     */
    private function applyBookingFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['date'])) {
            $query->whereDate('booking_date', $filters['date']);
        }
    }

    /**
     * Filter query pesanan.
     *
     * @param  array<string, mixed>  $filters
     */
    private function applyOrderFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }
    }

    /**
     * Otorisasi ubah booking.
     *
     * @throws AuthorizationException
     */
    private function authorizeBookingModification(User $user, Booking $booking): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager' && $user->branches()->where('branches.id', $booking->branch_id)->exists()) {
            return;
        }

        if ($booking->user_id === $user->id) {
            return;
        }

        throw new AuthorizationException('Anda tidak memiliki akses ke booking ini.');
    }

    /**
     * Otorisasi status booking.
     *
     * @throws AuthorizationException
     */
    private function authorizeStatusUpdate(User $user, Booking $booking): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager' && $user->branches()->where('branches.id', $booking->branch_id)->exists()) {
            return;
        }

        if ($roleName === 'employee' && $booking->employee_id === $user->employee?->id) {
            return;
        }

        throw new AuthorizationException('Anda tidak berhak mengubah status booking ini.');
    }

    /**
     * Otorisasi status pesanan.
     *
     * @throws AuthorizationException
     */
    private function authorizeOrderStatusUpdate(User $user, Order $order): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager' && $user->branches()->where('branches.id', $order->branch_id)->exists()) {
            return;
        }

        if (in_array($roleName, ['employee', 'cashier'])) {
            if ($user->employee?->branch_id === $order->branch_id || $user->branches()->where('branches.id', $order->branch_id)->exists()) {
                return;
            }
        }

        throw new AuthorizationException('Anda tidak berhak mengubah status order ini.');
    }
}
