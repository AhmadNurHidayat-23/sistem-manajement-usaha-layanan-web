<?php

namespace App\Http\Controllers\Owner;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreInternalUserRequest;
use App\Http\Requests\Owner\UpdateInternalUserRequest;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Services\AktivitasService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternalUserController extends Controller
{
    public function __construct(
        protected AktivitasService $aktivitasService,
    ) {}

    /**
     * Tampilkan daftar akun internal perusahaan.
     */
    public function index(Request $request): View
    {
        $internalRoles = [
            UserRole::Owner->value,
            UserRole::Manager->value,
            UserRole::Cashier->value,
            UserRole::Employee->value,
        ];

        $query = User::query()
            ->with(['role', 'branches'])
            ->whereHas('role', function (Builder $q) use ($internalRoles) {
                $q->whereIn('name', $internalRoles);
            });

        // Filter berdasarkan peran (role)
        if ($request->filled('role')) {
            $query->whereHas('role', function (Builder $q) use ($request) {
                $q->where('name', $request->input('role'));
            });
        }

        // Filter berdasarkan cabang penugasan
        if ($request->filled('branch_id')) {
            $branchId = (int) $request->input('branch_id');
            $query->whereHas('branches', function (Builder $q) use ($branchId) {
                $q->where('branches.id', $branchId);
            });
        }

        // Pencarian nama, email, nomor HP
        if ($request->filled('q')) {
            $search = '%'.$request->input('q').'%';
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search);
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $roles = Role::whereIn('name', $internalRoles)->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('owner.internal-users.index', compact('users', 'roles', 'branches'));
    }

    /**
     * Tampilkan form pembuatan akun internal baru (Kasir / Manager).
     */
    public function create(): View
    {
        $roles = Role::whereIn('name', [UserRole::Manager->value, UserRole::Cashier->value])->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('owner.internal-users.create', compact('roles', 'branches'));
    }

    /**
     * Simpan akun internal baru.
     */
    public function store(StoreInternalUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'role_id' => $request->input('role_id'),
            'password' => $request->input('password'),
        ]);

        $user->branches()->sync($request->input('branch_ids', []));

        $roleLabel = ucfirst($user->role?->name ?? 'Internal');
        $this->aktivitasService->catatDariRequest(
            $request,
            'Tambah Akun Internal',
            'user',
            "Akun {$roleLabel} '{$user->name}' ({$user->email}) berhasil dibuat dan ditugaskan ke cabang terkait."
        );

        return redirect()->route('owner.internal-users.index')
            ->with('success', "Akun internal '{$user->name}' berhasil dibuat.");
    }

    /**
     * Tampilkan form edit akun internal.
     */
    public function edit(User $internal_user): View
    {
        if ($internal_user->role?->name === UserRole::Customer->value) {
            abort(404, 'Akun pelanggan tidak dikelola di modul internal.');
        }

        $internal_user->load(['role', 'branches']);

        $allowedRoleNames = [UserRole::Manager->value, UserRole::Cashier->value];
        if ($internal_user->role?->name === UserRole::Owner->value) {
            $allowedRoleNames[] = UserRole::Owner->value;
        }

        $roles = Role::whereIn('name', $allowedRoleNames)->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('owner.internal-users.edit', [
            'user' => $internal_user,
            'roles' => $roles,
            'branches' => $branches,
        ]);
    }

    /**
     * Perbarui akun internal dan penugasan cabang.
     */
    public function update(UpdateInternalUserRequest $request, User $internal_user): RedirectResponse
    {
        if ($internal_user->role?->name === UserRole::Customer->value) {
            abort(404, 'Akun pelanggan tidak dikelola di modul internal.');
        }

        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
        ];

        // Owner tidak dapat diubah rolenya melalui form ini
        if ($internal_user->role?->name !== UserRole::Owner->value) {
            $data['role_id'] = $request->input('role_id');
        }

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        $internal_user->update($data);
        $internal_user->branches()->sync($request->input('branch_ids', []));

        $this->aktivitasService->catatDariRequest(
            $request,
            'Ubah Akun Internal',
            'user',
            "Akun internal '{$internal_user->name}' diperbarui."
        );

        return redirect()->route('owner.internal-users.index')
            ->with('success', "Data akun '{$internal_user->name}' berhasil diperbarui.");
    }
}
