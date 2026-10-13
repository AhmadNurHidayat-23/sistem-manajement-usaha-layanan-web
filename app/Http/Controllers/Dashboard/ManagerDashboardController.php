<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManagerDashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request): View
    {
        $dashboardData = $this->dashboardService->getManagerDashboardData($request->user());

        return view('dashboard.manager.index', $dashboardData);
    }
}
