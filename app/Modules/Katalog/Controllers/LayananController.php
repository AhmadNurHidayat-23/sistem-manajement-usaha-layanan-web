<?php

namespace App\Modules\Katalog\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Service;
use App\Modules\Cabang\Services\CabangService;
use App\Modules\Katalog\Requests\StoreLayananRequest;
use App\Modules\Katalog\Requests\UpdateLayananRequest;
use App\Modules\Katalog\Services\LayananService;
use App\Modules\Katalog\Services\UlasanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LayananController extends Controller
{
    public function __construct(
        protected LayananService $layananService,
        protected CabangService $cabangService
    ) {}

    /**
     * Display admin listing of services.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'category_id', 'branch_id', 'service_type']);
        $services = $this->layananService->getServicesForUser($request->user(), $filters);
        $categories = $this->layananService->getCategories();
        $branches = $this->cabangService->getBranchesForUser($request->user(), 100);

        return view('modules.katalog.owner.services.index', compact('services', 'categories', 'branches', 'filters'));
    }

    /**
     * Form tambah layanan.
     */
    public function create(Request $request): View
    {
        $categories = $this->layananService->getCategories(true);
        $branches = $this->cabangService->getBranchesForUser($request->user(), 100);

        return view('modules.katalog.owner.services.create', compact('categories', 'branches'));
    }

    /**
     * Store newly created service.
     */
    public function store(StoreLayananRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $service = $this->layananService->createService($data, $request->file('image'));

        return redirect()->route('catalog.services.index')
            ->with('success', "Layanan '{$service->name}' berhasil ditambahkan.");
    }

    /**
     * Display detail of specified service.
     */
    public function show(Request $request, Service $service): View
    {
        $this->layananService->authorizeServiceAccess($request->user(), $service);

        $service->load(['category', 'branch']);
        $categories = $this->layananService->getCategories();
        $branches = $this->cabangService->getBranchesForUser($request->user(), 100);
        $ratingStats = app(UlasanService::class)->getServiceRatingStats($service->id);
        $latestUlasans = $service->ulasans()->with('user')->latest()->take(3)->get();

        return view('modules.katalog.owner.services.show', compact('service', 'categories', 'branches', 'ratingStats', 'latestUlasans'));
    }

    /**
     * Form edit layanan.
     */
    public function edit(Request $request, Service $service): View
    {
        $this->layananService->authorizeServiceAccess($request->user(), $service);

        $categories = $this->layananService->getCategories(true);
        $branches = $this->cabangService->getBranchesForUser($request->user(), 100);

        return view('modules.katalog.owner.services.edit', compact('service', 'categories', 'branches'));
    }

    /**
     * Update specified service.
     */
    public function update(UpdateLayananRequest $request, Service $service): RedirectResponse
    {
        $this->layananService->authorizeServiceAccess($request->user(), $service);

        $updatedService = $this->layananService->updateService($service, $request->validated(), $request->file('image'));

        return redirect()->route('catalog.services.index')
            ->with('success', "Layanan '{$updatedService->name}' berhasil diperbarui.");
    }

    /**
     * Toggle active status of service.
     */
    public function toggleStatus(Request $request, Service $service): RedirectResponse
    {
        $this->layananService->authorizeServiceAccess($request->user(), $service);

        $updatedService = $this->layananService->toggleServiceStatus($service);
        $statusText = $updatedService->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Layanan '{$updatedService->name}' berhasil {$statusText}.");
    }

    /**
     * Public catalog marketplace page for customers.
     */
    public function publicCatalog(Request $request): View
    {
        $filters = $request->only(['q', 'category_id', 'branch_id', 'service_type']);
        $services = $this->layananService->getPublicCatalog($filters, 12);
        $categories = $this->layananService->getCategories(true);
        $branches = Branch::where('is_active', true)->get();

        return view('modules.katalog.public.index', compact('services', 'categories', 'branches', 'filters'));
    }
}
