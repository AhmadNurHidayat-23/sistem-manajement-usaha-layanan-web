<?php

namespace App\Modules\Katalog\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Service;
use App\Modules\Katalog\Services\UlasanService;
use Illuminate\View\View;

class PelangganLayananController extends Controller
{
    public function __construct(
        protected UlasanService $ulasanService
    ) {}

    /**
     * Detail layanan pelanggan.
     */
    public function show(Service $service): View
    {
        if (! $service->is_active) {
            abort(404, 'Layanan tidak ditemukan.');
        }

        $service->load(['category', 'branch']);

        // Ambil cabang penyedia layanan
        $branches = $service->branch_id
            ? Branch::where('id', $service->branch_id)->where('is_active', true)->get()
            : Branch::where('is_active', true)->orderBy('name')->get();

        // Ambil ulasan layanan
        $reviewStats = $this->ulasanService->getServiceRatingStats($service->id);

        return view('modules.katalog.customer.show', compact('service', 'branches', 'reviewStats'));
    }
}
