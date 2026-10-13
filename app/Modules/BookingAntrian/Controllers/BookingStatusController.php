<?php

namespace App\Modules\BookingAntrian\Controllers;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Modules\BookingAntrian\Requests\UpdateBookingStatusRequest;
use App\Modules\BookingAntrian\Services\BookingService;
use App\Modules\BookingAntrian\Services\StatusPekerjaanService;
use App\Services\NotifikasiService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class BookingStatusController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected StatusPekerjaanService $statusService,
        protected NotifikasiService $notifikasiService,
    ) {}

    /**
     * Update status booking.
     */
    public function update(UpdateBookingStatusRequest $request, Booking $booking): RedirectResponse
    {
        try {
            $this->statusService->updateBookingStatus($request->user(), $booking, $request->input('status'));

            $updatedBooking = $booking->fresh();

            // Notifikasi perubahan status ke pelanggan
            if ($updatedBooking->status === BookingStatus::Confirmed) {
                rescue(fn () => $this->notifikasiService->notifikasiBookingDikonfirmasi($updatedBooking));
            } else {
                rescue(fn () => $this->notifikasiService->notifikasiStatusBooking($updatedBooking));
            }

            return back()->with('success', 'Status booking berhasil diperbarui.');
        } catch (AuthorizationException|InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Alias update status booking.
     */
    public function updateStatus(UpdateBookingStatusRequest $request, Booking $booking): RedirectResponse
    {
        return $this->update($request, $booking);
    }
}
