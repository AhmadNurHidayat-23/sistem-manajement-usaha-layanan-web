<?php

namespace App\Modules\BookingAntrian\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Modules\BookingAntrian\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingAvailabilityController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Slot ketersediaan booking.
     */
    public function availableSlots(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'date' => ['required', 'date'],
            'exclude' => ['nullable', 'integer'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'extra_service_ids' => ['nullable', 'array'],
            'extra_service_ids.*' => ['exists:services,id'],
        ]);

        $branch = Branch::findOrFail($request->input('branch_id'));
        $excludeId = $request->filled('exclude') ? (int) $request->input('exclude') : null;
        $employeeId = $request->filled('employee_id') ? (int) $request->input('employee_id') : null;
        $serviceId = $request->filled('service_id') ? (int) $request->input('service_id') : null;
        $extraServiceIds = (array) $request->input('extra_service_ids', []);

        $slots = $this->bookingService->getAvailableSlots(
            $branch,
            $request->input('date'),
            $excludeId,
            $employeeId,
            $serviceId,
            $extraServiceIds
        );

        $slotsDetailed = $this->bookingService->getSlotsWithStatus(
            $branch,
            $request->input('date'),
            $excludeId,
            $employeeId,
            $serviceId,
            $extraServiceIds
        );

        return response()->json([
            'slots' => $slots,
            'slots_detailed' => $slotsDetailed,
        ]);
    }

    /**
     * Daftar karyawan cabang.
     */
    public function branchEmployees(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $employees = $this->bookingService->getBranchEmployees((int) $request->input('branch_id'));

        return response()->json(['employees' => $employees]);
    }
}
