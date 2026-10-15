<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cabang\StoreCabangRequest;
use App\Http\Requests\Cabang\UpdateCabangRequest;
use App\Models\Branch;
use App\Services\AktivitasService;
use App\Services\Cabang\CabangService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CabangController extends Controller
{
    public function __construct(
        protected CabangService $cabangService,
        protected AktivitasService $aktivitasService,
    ) {}

    /**
     * Daftar accessible branches.
     */
    public function index(Request $request): View
    {
        $branches = $this->cabangService->getBranchesForUser($request->user());

        return view('owner.cabang.index', compact('branches'));
    }

    /**
     * Form tambah branch.
     */
    public function create(Request $request): View
    {
        if ($request->user()?->role?->name !== 'owner') {
            abort(403, 'Hanya Owner yang dapat menambah cabang baru.');
        }

        return view('owner.cabang.create');
    }

    /**
     * Simpan branch baru.
     */
    public function store(StoreCabangRequest $request): RedirectResponse
    {
        $branch = $this->cabangService->createBranch($request->validated());

        $this->aktivitasService->catatDariRequest($request, 'Tambah Cabang', 'cabang', "Cabang '{$branch->name}' ditambahkan.");

        return redirect()->route('branches.index')
            ->with('success', "Cabang '{$branch->name}' berhasil ditambahkan.");
    }

    /**
     * Tampilkan detail branch details.
     */
    public function show(Request $request, Branch $branch): View
    {
        $this->cabangService->authorizeBranchAccess($request->user(), $branch);

        $branch->loadCount(['employees', 'users', 'services']);

        return view('owner.cabang.show', compact('branch'));
    }

    /**
     * Form edit branch.
     */
    public function edit(Request $request, Branch $branch): View
    {
        $this->cabangService->authorizeBranchAccess($request->user(), $branch);

        return view('owner.cabang.edit', compact('branch'));
    }

    /**
     * Perbarui branch.
     */
    public function update(UpdateCabangRequest $request, Branch $branch): RedirectResponse
    {
        $this->cabangService->authorizeBranchAccess($request->user(), $branch);

        $updatedBranch = $this->cabangService->updateBranch($branch, $request->validated());

        $this->aktivitasService->catatDariRequest($request, 'Edit Cabang', 'cabang', "Cabang '{$updatedBranch->name}' diperbarui.");

        return redirect()->route('branches.show', $updatedBranch)
            ->with('success', "Data cabang '{$updatedBranch->name}' berhasil diperbarui.");
    }

    /**
     * Ubah status aktif cabang.
     */
    public function toggleStatus(Request $request, Branch $branch): RedirectResponse
    {
        $this->cabangService->authorizeBranchAccess($request->user(), $branch);

        $updatedBranch = $this->cabangService->toggleBranchStatus($branch);

        $statusText = $updatedBranch->is_active ? 'diaktifkan' : 'dinonaktifkan';

        $this->aktivitasService->catatDariRequest($request, 'Ubah Status Cabang', 'cabang', "Cabang '{$updatedBranch->name}' {$statusText}.");

        return back()->with('success', "Status cabang '{$updatedBranch->name}' berhasil {$statusText}.");
    }
}
