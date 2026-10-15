<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingAntrian\StoreBookingRequest;
use App\Http\Requests\BookingAntrian\UpdateBookingRequest;
use App\Models\Booking;
use App\Services\BookingAntrian\BookingService;
use App\Services\NotifikasiService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected NotifikasiService $notifikasiService,
    ) {}

    /**
     * Daftar booking pengguna.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'branch_id', 'date']);
        $bookings = $this->bookingService->getBookingsForUser($request->user(), $filters);
        $branches = $this->bookingService->getActiveBranches();

        return view('manager.booking.index', compact('bookings', 'branches', 'filters'));
    }

    /**
     * Form tambah booking.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Booking::class);

        $branches = $this->bookingService->getActiveBranches('appointment');
        $services = $this->bookingService->getActiveServices();

        return view('manager.booking.create', compact('branches', 'services'));
    }

    /**
     * Simpan booking baru.
     */
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $this->authorize('create', Booking::class);

        try {
            $booking = $this->bookingService->createBooking($request->user(), $request->validated());

            rescue(fn () => $this->notifikasiService->notifikasiBookingBaru($booking->fresh()));

            return redirect()->route('bookings.show', $booking)
                ->with('success', "Booking berhasil dibuat untuk {$booking->booking_date->format('d M Y')} pukul {$booking->booking_time}.");
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Detail booking.
     */
    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['branch', 'service.category', 'employee', 'user', 'details.service']);

        return view('manager.booking.show', compact('booking'));
    }

    /**
     * Form edit booking.
     */
    public function edit(Request $request, Booking $booking): View
    {
        $this->authorize('update', $booking);

        $branches = $this->bookingService->getActiveBranches();
        $services = $this->bookingService->getActiveServices($booking->branch_id);
        $employees = $this->bookingService->getBranchEmployees($booking->branch_id);

        return view('manager.booking.edit', compact('booking', 'branches', 'services', 'employees'));
    }

    /**
     * Perbarui booking.
     */
    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('update', $booking);

        try {
            $updated = $this->bookingService->updateBooking($request->user(), $booking, $request->validated());

            return redirect()->route('bookings.show', $updated)
                ->with('success', 'Booking berhasil diperbarui.');
        } catch (\RuntimeException|AuthorizationException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Batalkan booking.
     */
    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        try {
            $this->bookingService->cancelBooking($request->user(), $booking);

            // Notify customer of cancellation
            rescue(fn () => $this->notifikasiService->notifikasiStatusBooking($booking->fresh()));

            return redirect()->route('bookings.index')
                ->with('success', 'Booking berhasil dibatalkan.');
        } catch (\RuntimeException|AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
