<?php

namespace App\Modules\Karyawan\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karyawan\Services\KaryawanService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected KaryawanService $karyawanService,
    ) {}

    /**
     * Tampilkan dashboard ringkasan kerja dan antrian untuk karyawan login.
     */
    public function index(Request $request): View
    {
        $dashboardData = $this->karyawanService->getDashboardSummaryForEmployee($request->user());

        return view('modules.karyawan.dashboard.index', $dashboardData);
    }
}
