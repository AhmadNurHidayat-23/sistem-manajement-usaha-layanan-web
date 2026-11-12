<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingAntrian\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Branch;
use App\Services\BookingAntrian\BookingService;
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

        return view('pelanggan.booking.index', compact('bookings', 'filters'));
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
            ? $this->bookingService->getBranchEmployees((int) $selectedBranchId, ! empty($selectedServiceId) ? (int) $selectedServiceId : null)
            : collect();

        $selectedBranch = $selectedBranchId ? Branch::find($selectedBranchId) : null;

        return view('pelanggan.booking.create', compact(
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
        $serviceId = $request->query('service_id');
        if (! $branchId) {
            return response()->json(['services' => [], 'employees' => []]);
        }

        $services = $this->bookingService->getActiveServices((int) $branchId);
        $employees = $this->bookingService->getBranchEmployees((int) $branchId, $serviceId ? (int) $serviceId : null);

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
            return response()->json([
                'is_open' => false,
                'slots' => [],
            ]);
        }

        $branch = Branch::find($branchId);
        if (! $branch) {
            return response()->json([
                'is_open' => false,
                'slots' => [],
            ]);
        }

        // Check operating days
        if (! $branch->isOpenOnDate($date)) {
            return response()->json([
                'is_open' => false,
                'reason' => 'branch_closed',
                'label' => 'Cabang Tutup',
                'message' => 'Cabang tidak beroperasi pada hari tersebut.',
                'slots' => [],
            ]);
        }

        if ($request->query('format') === 'simple') {
            $simpleSlots = $this->bookingService->getAvailableSlots(
                $branch,
                $date,
                null,
                $employeeId ? (int) $employeeId : null,
                $serviceId ? (int) $serviceId : null,
            );

            return response()->json($simpleSlots);
        }

        $slots = $this->bookingService->getSlotsWithStatus(
            $branch,
            $date,
            null,
            $employeeId ? (int) $employeeId : null,
            $serviceId ? (int) $serviceId : null,
        );

        $employeeOffDuty = false;
        $employeeMessage = null;
        if ($employeeId && count($slots) > 0) {
            $allNoShift = collect($slots)->every(fn ($s) => in_array($s['reason'] ?? '', ['employee_no_shift', 'employee_wrong_branch'], true));
            if ($allNoShift) {
                $employeeOffDuty = true;
                $employeeMessage = 'Petugas yang dipilih tidak memiliki jadwal kerja pada tanggal ini. Silakan pilih tanggal lain atau petugas lainnya.';
            }
        }

        return response()->json([
            'is_open' => true,
            'employee_off_duty' => $employeeOffDuty,
            'employee_message' => $employeeMessage,
            'slots' => $slots,
        ]);
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

        return view('pelanggan.booking.show', compact('booking'));
    }

    /**
     * Batalkan booking pelanggan.
     */
    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        $isPresetFormat = $request->has('reason_preset') || $request->has('_from_modal');

        if ($isPresetFormat) {
            $validated = $request->validate([
                'reason_preset' => ['required', 'string'],
                'reason_custom' => ['nullable', 'string', 'max:500'],
            ], [
                'reason_preset.required' => 'Silakan pilih alasan pembatalan.',
            ]);

            $selectedReason = $validated['reason_preset'];
            if ($selectedReason === 'Alasan lainnya') {
                $customReason = trim($request->input('reason_custom', ''));
                if ($customReason === '') {
                    return back()->withInput()->withErrors([
                        'reason_custom' => 'Alasan pembatalan lainnya wajib diisi.',
                    ]);
                }
                $finalReason = $customReason;
            } else {
                $finalReason = $selectedReason;
            }
        } else {
            $validated = $request->validate([
                'cancellation_reason' => ['required', 'string', 'max:500'],
                'other_cancellation_reason' => ['nullable', 'string', 'max:1000'],
            ], [
                'cancellation_reason.required' => 'Pilih alasan pembatalan janji temu.',
            ]);

            $finalReason = trim($validated['cancellation_reason']);
            if (in_array(strtolower($finalReason), ['alasan lainnya', 'other', 'lainnya'], true)) {
                $other = trim($request->input('other_cancellation_reason', ''));
                if ($other === '') {
                    return back()->withInput()->withErrors([
                        'other_cancellation_reason' => 'Silakan tuliskan alasan pembatalan Anda.',
                    ]);
                }
                $finalReason = $other;
            }
        }

        try {
            $this->bookingService->cancelBooking($request->user(), $booking, $finalReason);

            rescue(fn () => $this->notifikasiService->notifikasiStatusBooking($booking->fresh()));

            if ($request->has('_from_modal')) {
                return redirect()->route('customer.booking.index')
                    ->with('success', 'Booking berhasil dibatalkan.');
            }

            return redirect()->route('customer.booking.show', $booking)
                ->with('success', 'Janji temu berhasil dibatalkan.');
        } catch (\RuntimeException|AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
