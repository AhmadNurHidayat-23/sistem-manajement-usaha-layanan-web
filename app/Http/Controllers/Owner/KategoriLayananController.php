<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Katalog\StoreKategoriLayananRequest;
use App\Http\Requests\Katalog\UpdateKategoriLayananRequest;
use App\Models\ServiceCategory;
use App\Services\Katalog\LayananService;
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

        return view('owner.kategori.index', compact('categories'));
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
