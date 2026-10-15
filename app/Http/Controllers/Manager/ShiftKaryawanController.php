<?php

namespace App\Http\Controllers\Manager;

use App\Enums\StatusShiftKaryawan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\StoreShiftRequest;
use App\Http\Requests\Karyawan\UpdateShiftRequest;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Services\Karyawan\KaryawanService;
use App\Services\Karyawan\ShiftKaryawanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShiftKaryawanController extends Controller
{
    public function __construct(
        protected ShiftKaryawanService $shiftService,
        protected KaryawanService $karyawanService
    ) {}

    /**
     * Daftar employee shifts.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->only(['date', 'branch_id', 'employee_id', 'status', 'scope']);

        $shifts = $this->shiftService->getShifts($user, $filters);
        $branches = $this->karyawanService->getAccessibleBranches($user);

        $employeesQuery = Employee::query()->where('status', 'active');
        if ($user->role?->name === 'manager') {
            $employeesQuery->whereIn('branch_id', $user->branches()->pluck('branches.id'));
        }
        $employees = $employeesQuery->orderBy('name')->get();

        return view('manager.shift.index', compact('shifts', 'branches', 'employees', 'filters'));
    }

    /**
     * Simpan shift baru.
     */
    public function store(StoreShiftRequest $request): RedirectResponse
    {
        try {
            $shift = $this->shiftService->createShift($request->user(), $request->validated());

            return back()->with('success', "Shift kerja berhasil dijadwalkan untuk {$shift->employee->name}.");
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Perbarui shift.
     */
    public function update(UpdateShiftRequest $request, EmployeeShift $shift): RedirectResponse
    {
        try {
            $this->shiftService->updateShift($request->user(), $shift, $request->validated());

            return back()->with('success', 'Jadwal shift berhasil diperbarui.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Update status kehadiran shift.
     */
    public function updateStatus(Request $request, EmployeeShift $shift): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(StatusShiftKaryawan::class)],
        ]);

        try {
            $this->shiftService->updateShiftStatus(
                $request->user(),
                $shift,
                StatusShiftKaryawan::from($validated['status'])
            );

            return back()->with('success', "Status kehadiran shift berhasil diubah menjadi {$validated['status']}.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus shift.
     */
    public function destroy(Request $request, EmployeeShift $shift): RedirectResponse
    {
        $this->shiftService->deleteShift($request->user(), $shift);

        return back()->with('success', 'Jadwal shift berhasil dihapus.');
    }

    /**
     * Tampilkan jadwal shift pribadi untuk karyawan login.
     */
    public function myShifts(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee ?: Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            abort(404, 'Profil karyawan tidak ditemukan untuk akun ini.');
        }

        $shifts = $this->shiftService->getShifts($user, ['employee_id' => $employee->id]);

        return view('manager.shift.index', [
            'shifts' => $shifts,
            'branches' => collect([$employee->branch]),
            'employees' => collect([$employee]),
            'filters' => ['employee_id' => $employee->id],
            'isSelfView' => true,
        ]);
    }
}
