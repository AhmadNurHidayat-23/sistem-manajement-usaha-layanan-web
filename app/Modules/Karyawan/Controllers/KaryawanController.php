<?php

namespace App\Modules\Karyawan\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Service;
use App\Modules\Karyawan\Requests\StoreKaryawanRequest;
use App\Modules\Karyawan\Requests\UpdateKaryawanRequest;
use App\Modules\Karyawan\Services\KaryawanService;
use App\Modules\Karyawan\Services\KomisiKaryawanService;
use App\Services\AktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KaryawanController extends Controller
{
    public function __construct(
        protected KaryawanService $karyawanService,
        protected KomisiKaryawanService $komisiService,
        protected AktivitasService $aktivitasService,
    ) {}

    /**
     * Daftar employees.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->only(['branch_id', 'status', 'skill', 'q']);

        $employees = $this->karyawanService->getEmployees($user, $filters);
        $branches = $this->karyawanService->getAccessibleBranches($user);

        // Ringkasan data
        $totalEmployees = $employees->total();
        $activeEmployeesCount = Employee::query()
            ->when($user->role?->name === 'manager', function ($q) use ($user) {
                $q->whereIn('branch_id', $user->branches()->pluck('branches.id'));
            })
            ->where('status', 'active')
            ->count();

        $commissionOverview = $this->komisiService->getCommissionOverview(
            $user,
            ! empty($filters['branch_id']) ? (int) $filters['branch_id'] : null
        );

        $availableUsers = $this->karyawanService->getAvailableUsersForEmployee($user);

        return view('modules.karyawan.data.index', compact(
            'employees',
            'branches',
            'availableUsers',
            'filters',
            'totalEmployees',
            'activeEmployeesCount',
            'commissionOverview'
        ));
    }

    /**
     * Form tambah employee.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $branches = $this->karyawanService->getAccessibleBranches($user);
        $availableUsers = $this->karyawanService->getAvailableUsersForEmployee($user);

        return view('modules.karyawan.data.create', compact('branches', 'availableUsers'));
    }

    /**
     * Simpan employee baru.
     */
    public function store(StoreKaryawanRequest $request): RedirectResponse
    {
        $employee = $this->karyawanService->createEmployee($request->user(), $request->validated());

        $this->aktivitasService->catatDariRequest($request, 'Tambah Karyawan', 'karyawan', "Karyawan '{$employee->name}' ditambahkan.");

        return redirect()->route('employees.show', $employee)
            ->with('success', "Data karyawan '{$employee->name}' berhasil ditambahkan.");
    }

    /**
     * Tampilkan detail employee profile, skills, shifts, and performance summary.
     */
    public function show(Request $request, Employee $employee): View
    {
        $user = $request->user();
        $this->karyawanService->authorizeEmployeeAccess($user, $employee);

        $employee->load(['branch', 'user', 'skills']);
        $performance = $this->karyawanService->getEmployeePerformanceSummary($employee);
        $accessibleBranches = $this->karyawanService->getAccessibleBranches($user);
        $services = Service::where('is_active', true)
            ->when($employee->branch_id, function ($q) use ($employee) {
                $q->where(function ($sub) use ($employee) {
                    $sub->where('branch_id', $employee->branch_id)
                        ->orWhereNull('branch_id');
                });
            })
            ->orderBy('name')
            ->get();

        return view('modules.karyawan.data.show', compact('employee', 'performance', 'accessibleBranches', 'services'));
    }

    /**
     * Form edit employee.
     */
    public function edit(Request $request, Employee $employee): View
    {
        $user = $request->user();
        $this->karyawanService->authorizeEmployeeManagement($user, $employee);

        $employee->load(['branch', 'user', 'skills']);
        $branches = $this->karyawanService->getAccessibleBranches($user);
        $availableUsers = $this->karyawanService->getAvailableUsersForEmployee($user, $employee);

        return view('modules.karyawan.data.edit', compact('employee', 'branches', 'availableUsers'));
    }

    /**
     * Perbarui employee.
     */
    public function update(UpdateKaryawanRequest $request, Employee $employee): RedirectResponse
    {
        $updatedEmployee = $this->karyawanService->updateEmployee($request->user(), $employee, $request->validated());

        $this->aktivitasService->catatDariRequest($request, 'Edit Karyawan', 'karyawan', "Karyawan '{$updatedEmployee->name}' diperbarui.");

        return redirect()->route('employees.show', $updatedEmployee)
            ->with('success', "Data karyawan '{$updatedEmployee->name}' berhasil diperbarui.");
    }

    /**
     * Ubah status aktif karyawan.
     */
    public function toggleStatus(Request $request, Employee $employee): RedirectResponse
    {
        $updated = $this->karyawanService->toggleEmployeeStatus($request->user(), $employee);
        $statusText = $updated->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        $this->aktivitasService->catatDariRequest($request, 'Ubah Status Karyawan', 'karyawan', "Karyawan '{$updated->name}' {$statusText}.");

        return back()->with('success', "Status karyawan '{$updated->name}' berhasil {$statusText}.");
    }
}
