<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Services\Karyawan\KaryawanService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KaryawanDashboardController extends Controller
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

        return view('karyawan.dashboard.index', $dashboardData);
    }
}
