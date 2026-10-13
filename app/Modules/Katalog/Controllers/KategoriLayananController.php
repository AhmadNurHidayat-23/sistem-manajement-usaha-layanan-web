<?php

namespace App\Modules\Katalog\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Modules\Katalog\Requests\StoreKategoriLayananRequest;
use App\Modules\Katalog\Requests\UpdateKategoriLayananRequest;
use App\Modules\Katalog\Services\LayananService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KategoriLayananController extends Controller
{
    public function __construct(
        protected LayananService $layananService
    ) {}

    /**
     * Display listing of categories.
     */
    public function index(): View
    {
        $categories = $this->layananService->getCategories();

        return view('modules.katalog.owner.categories.index', compact('categories'));
    }

    /**
     * Store newly created category.
     */
    public function store(StoreKategoriLayananRequest $request): RedirectResponse
    {
        $category = $this->layananService->createCategory($request->validated());

        return redirect()->route('catalog.categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    /**
     * Update specified category.
     */
    public function update(UpdateKategoriLayananRequest $request, ServiceCategory $category): RedirectResponse
    {
        $updatedCategory = $this->layananService->updateCategory($category, $request->validated());

        return redirect()->route('catalog.categories.index')
            ->with('success', "Kategori '{$updatedCategory->name}' berhasil diperbarui.");
    }

    /**
     * Toggle active status of category.
     */
    public function toggleStatus(ServiceCategory $category): RedirectResponse
    {
        $updatedCategory = $this->layananService->toggleCategoryStatus($category);

        $statusText = $updatedCategory->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kategori '{$updatedCategory->name}' berhasil {$statusText}.");
    }
}
