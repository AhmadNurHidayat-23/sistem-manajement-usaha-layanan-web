<?php

namespace App\Services;

use App\Models\User;
use App\Services\Dashboard\ManagerDashboardService;
use App\Services\Dashboard\OwnerDashboardService;

class DashboardService
{
    public function __construct(
        protected OwnerDashboardService $ownerService,
        protected ManagerDashboardService $managerService
    ) {}

    /**
     * Data analitik dashboard owner.
     *
     * @return array<string, mixed>
     */
    public function getOwnerDashboardData(): array
    {
        return $this->ownerService->getOwnerDashboardData();
    }

    /**
     * Data operasional dashboard manager.
     *
     * @return array<string, mixed>
     */
    public function getManagerDashboardData(User $manager): array
    {
        return $this->managerService->getManagerDashboardData($manager);
    }
}
