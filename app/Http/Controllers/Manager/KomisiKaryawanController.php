<?php

namespace App\Http\Controllers\Manager;

use App\Enums\CommissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\StoreKomisiRequest;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\Service;
use App\Services\Karyawan\KaryawanService;
use App\Services\Karyawan\KomisiKaryawanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KomisiKaryawanController extends Controller
{
    public function __construct(
        protected KomisiKaryawanService $komisiService,
        protected KaryawanService $karyawanService
    ) {}

    /**
     * Daftar commissions and recap estimates.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->only(['employee_id', 'branch_id', 'status', 'start_date', 'end_date']);

        $commissions = $this->komisiService->getCommissions($user, $filters);
        $overview = $this->komisiService->getCommissionOverview(
            $user,
            ! empty($filters['branch_id']) ? (int) $filters['branch_id'] : null,
            ! empty($filters['employee_id']) ? (int) $filters['employee_id'] : null
        );

        $branches = $this->karyawanService->getAccessibleBranches($user);

        $employeesQuery = Employee::query()->where('status', 'active');
        if ($user->role?->name === 'manager') {
            $employeesQuery->whereIn('branch_id', $user->branches()->pluck('branches.id'));
        }
        $employees = $employeesQuery->orderBy('name')->get();

        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('manager.komisi.index', compact(
            'commissions',
            'overview',
            'branches',
            'employees',
            'services',
            'filters'
        ));
    }

    /**
     * Simpan commission record baru.
     */
    public function store(StoreKomisiRequest $request): RedirectResponse
    {
        $commission = $this->komisiService->createCommission($request->user(), $request->validated());

        return back()->with('success', 'Komisi sebesar Rp '.number_format($commission->commission_amount, 0, ',', '.')." berhasil dicatat untuk {$commission->employee->name}.");
    }

    /**
     * Update status komisi.
     */
    public function updateStatus(Request $request, EmployeeCommission $commission): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(CommissionStatus::class)],
        ]);

        $statusEnum = CommissionStatus::from($validated['status']);
        $this->komisiService->updateCommissionStatus($request->user(), $commission, $statusEnum);

        $statusLabels = [
            'pending' => 'Pending',
            'approved' => 'Disetujui',
            'paid' => 'Telah Dibayar (Cair)',
            'cancelled' => 'Dibatalkan',
        ];

        return back()->with('success', "Status komisi berhasil diubah menjadi: {$statusLabels[$statusEnum->value]}.");
    }

    /**
     * Tampilkan rekap komisi pribadi untuk karyawan login.
     */
    public function myCommissions(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee ?: Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            abort(404, 'Profil karyawan tidak ditemukan untuk akun ini.');
        }

        $commissions = $this->komisiService->getCommissions($user, ['employee_id' => $employee->id]);
        $overview = $this->komisiService->getCommissionOverview($user, null, $employee->id);

        return view('manager.komisi.index', [
            'commissions' => $commissions,
            'overview' => $overview,
            'branches' => collect([$employee->branch]),
            'employees' => collect([$employee]),
            'filters' => ['employee_id' => $employee->id],
            'isSelfView' => true,
        ]);
    }
}
