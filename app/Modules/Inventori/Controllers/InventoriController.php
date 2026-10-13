<?php

namespace App\Modules\Inventori\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Modules\Inventori\Requests\StokKeluarRequest;
use App\Modules\Inventori\Requests\StokMasukRequest;
use App\Modules\Inventori\Requests\StoreBahanRequest;
use App\Modules\Inventori\Requests\UpdateBahanRequest;
use App\Modules\Inventori\Services\InventoriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoriController extends Controller
{
    public function __construct(
        protected InventoriService $inventoriService
    ) {}

    /**
     * Daftar inventory materials.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->only(['search', 'branch_id', 'status']);

        $items = $this->inventoriService->getItems($user, $filters);
        $metrics = $this->inventoriService->getMetrics($user, $filters);

        $branches = ($user->role?->name === UserRole::Owner->value)
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        $managerBranch = ($user->role?->name === UserRole::Manager->value)
            ? $user->branches()->first()
            : null;

        return view('modules.inventori.index', compact('items', 'metrics', 'branches', 'managerBranch', 'filters'));
    }

    /**
     * Form tambah material.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        $branches = ($user->role?->name === UserRole::Owner->value)
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        $managerBranch = ($user->role?->name === UserRole::Manager->value)
            ? $user->branches()->first()
            : null;

        return view('modules.inventori.create', compact('branches', 'managerBranch'));
    }

    /**
     * Simpan material baru.
     */
    public function store(StoreBahanRequest $request): RedirectResponse
    {
        $item = $this->inventoriService->createItem($request->user(), $request->validated());

        return redirect()->route('inventori.show', $item)
            ->with('success', "Bahan '{$item->name}' berhasil ditambahkan ke inventori.");
    }

    /**
     * Tampilkan detail material details and its stock mutation history.
     */
    public function show(Request $request, InventoryItem $item): View
    {
        $item = $this->inventoriService->getItem($request->user(), $item->id);

        $mutations = $item->mutations()->with('user')->paginate(10);

        return view('modules.inventori.show', compact('item', 'mutations'));
    }

    /**
     * Form edit material.
     */
    public function edit(Request $request, InventoryItem $item): View
    {
        $item = $this->inventoriService->getItem($request->user(), $item->id);

        return view('modules.inventori.edit', compact('item'));
    }

    /**
     * Perbarui material.
     */
    public function update(UpdateBahanRequest $request, InventoryItem $item): RedirectResponse
    {
        $item = $this->inventoriService->updateItem($request->user(), $item, $request->validated());

        return redirect()->route('inventori.show', $item)
            ->with('success', "Informasi bahan '{$item->name}' berhasil diperbarui.");
    }

    /**
     * Proses stok masuk.
     */
    public function stockIn(StokMasukRequest $request, InventoryItem $item): RedirectResponse
    {
        $this->inventoriService->recordStockIn($request->user(), $item, $request->validated());

        return redirect()->route('inventori.show', $item)
            ->with('success', "Stok masuk sebanyak {$request->input('quantity')} {$item->unit} berhasil dicatat.");
    }

    /**
     * Process stock-out (usage / write-off).
     */
    public function stockOut(StokKeluarRequest $request, InventoryItem $item): RedirectResponse
    {
        $this->inventoriService->recordStockOut($request->user(), $item, $request->validated());

        return redirect()->route('inventori.show', $item)
            ->with('success', "Stok keluar sebanyak {$request->input('quantity')} {$item->unit} berhasil dicatat.");
    }

    /**
     * Riwayat mutasi stok.
     */
    public function history(Request $request): View
    {
        $user = $request->user();
        $filters = $request->only(['search', 'branch_id', 'type']);

        $mutations = $this->inventoriService->getMutationsHistory($user, $filters);

        $branches = ($user->role?->name === UserRole::Owner->value)
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('modules.inventori.riwayat', compact('mutations', 'branches', 'filters'));
    }
}
