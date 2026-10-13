<?php

namespace App\Modules\BookingAntrian\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Branch;
use App\Modules\BookingAntrian\Requests\StoreBookingRequest;
use App\Modules\BookingAntrian\Services\BookingService;
use App\Services\NotifikasiService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PelangganBookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected NotifikasiService $notifikasiService,
    ) {}

    /**
     * Daftar booking appointment pelanggan.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status']);
        $bookings = $this->bookingService->getBookingsForUser($request->user(), $filters);

        return view('modules.booking-antrian.pelanggan.booking.index', compact('bookings', 'filters'));
    }

    /**
     * Form buat booking baru (pelanggan).
     */
    public function create(Request $request): View
    {
        $branches = $this->bookingService->getActiveBranches('appointment');
        $selectedBranchId = $request->query('branch_id');
        $selectedServiceId = $request->query('service_id');

        $services = ! empty($selectedBranchId)
            ? $this->bookingService->getActiveServices((int) $selectedBranchId)
            : collect();

        $employees = ! empty($selectedBranchId)
            ? $this->bookingService->getBranchEmployees((int) $selectedBranchId)
            : collect();

        $selectedBranch = $selectedBranchId ? Branch::find($selectedBranchId) : null;

        return view('modules.booking-antrian.pelanggan.booking.create', compact(
            'branches',
            'services',
            'employees',
            'selectedBranchId',
            'selectedServiceId',
            'selectedBranch',
        ));
    }

    /**
     * API: Layanan & karyawan berdasarkan cabang (dropdown dinamis).
     */
    public function branchData(Request $request): JsonResponse
    {
        $branchId = $request->query('branch_id');
        if (! $branchId) {
            return response()->json(['services' => [], 'employees' => []]);
        }

        $services = $this->bookingService->getActiveServices((int) $branchId);
        $employees = $this->bookingService->getBranchEmployees((int) $branchId);

        $branch = Branch::find($branchId);

        return response()->json([
            'services' => $services->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'price' => (float) $s->price,
                'duration' => (int) ($s->estimated_duration ?? 0),
                'duration_formatted' => $s->formatted_duration,
            ]),
            'employees' => $employees->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->name,
            ]),
            'operating_days' => $branch?->operating_days,
        ]);
    }

    /**
     * API: Slot waktu tersedia.
     */
    public function availableSlots(Request $request): JsonResponse
    {
        $branchId = $request->query('branch_id');
        $date = $request->query('date');
        $serviceId = $request->query('service_id');
        $employeeId = $request->query('employee_id');

        if (! $branchId || ! $date) {
            return response()->json([]);
        }

        $branch = Branch::find($branchId);
        if (! $branch) {
            return response()->json([]);
        }

        // Check operating days
        if (! $branch->isOpenOnDate($date)) {
            return response()->json([]);
        }

        $slots = $this->bookingService->getAvailableSlots(
            $branch,
            $date,
            null,
            $employeeId ? (int) $employeeId : null,
            $serviceId ? (int) $serviceId : null,
        );

        return response()->json($slots);
    }

    /**
     * Simpan booking pelanggan.
     */
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        try {
            // Validasi hari operasional
            $branch = Branch::findOrFail($request->validated()['branch_id']);
            if (! $branch->isOpenOnDate($request->validated()['booking_date'])) {
                throw ValidationException::withMessages([
                    'booking_date' => 'Cabang tidak beroperasi pada hari tersebut.',
                ]);
            }

            $booking = $this->bookingService->createBooking($request->user(), $request->validated());

            rescue(fn () => $this->notifikasiService->notifikasiBookingBaru($booking->fresh()));

            return redirect()->route('customer.booking.show', $booking)
                ->with('success', "Booking berhasil dibuat untuk {$booking->booking_date->format('d M Y')} pukul {$booking->booking_time}.");
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Detail booking pelanggan.
     */
    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['branch', 'service.category', 'employee', 'user', 'details.service']);

        return view('modules.booking-antrian.pelanggan.booking.show', compact('booking'));
    }

    /**
     * Batalkan booking pelanggan.
     */
    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        try {
            $this->bookingService->cancelBooking($request->user(), $booking);

            rescue(fn () => $this->notifikasiService->notifikasiStatusBooking($booking->fresh()));

            return redirect()->route('customer.booking.index')
                ->with('success', 'Booking berhasil dibatalkan.');
        } catch (\RuntimeException|AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
