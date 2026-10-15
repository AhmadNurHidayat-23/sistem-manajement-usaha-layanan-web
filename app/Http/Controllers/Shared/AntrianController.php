<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Services\BookingAntrian\AntrianLayananService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AntrianController extends Controller
{
    public function __construct(
        protected AntrianLayananService $antrianService
    ) {}

    /**
     * Tampilkan antrean dan daftar tugas terpadu.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $date = $request->input('date', now()->toDateString());
        $isHistory = $request->query('view') === 'riwayat';
        $statusFilter = strtolower((string) $request->query('status', 'semua'));
        $filters = $request->only(['branch_id']);

        $branches = $this->antrianService->getActiveBranches();

        if ($isHistory) {
            $pastTasks = $this->antrianService->getPastTasks($user);

            return view('shared.antrian.index', [
                'tasks' => $pastTasks,
                'isHistory' => true,
                'currentFilter' => 'riwayat',
                'counts' => [
                    'semua' => 0,
                    'menunggu' => 0,
                    'dikerjakan' => 0,
                    'selesai' => $pastTasks->count(),
                ],
                'date' => $date,
                'branches' => $branches,
                'filters' => $filters,
                'bookings' => collect(),
                'orders' => collect(),
            ]);
        }

        // Ambil daftar tugas terpadu hari berjalan
        $allTasks = $this->antrianService->getUnifiedDailyTasks($user, $date, $filters);

        $counts = [
            'semua' => $allTasks->count(),
            'menunggu' => $allTasks->where('status_ui', 'Menunggu')->count(),
            'dikerjakan' => $allTasks->where('status_ui', 'Dikerjakan')->count(),
            'selesai' => $allTasks->where('status_ui', 'Selesai')->count(),
        ];

        $tasks = match ($statusFilter) {
            'menunggu' => $allTasks->filter(fn ($t) => $t['status_ui'] === 'Menunggu')->values(),
            'dikerjakan' => $allTasks->filter(fn ($t) => $t['status_ui'] === 'Dikerjakan')->values(),
            'selesai' => $allTasks->filter(fn ($t) => $t['status_ui'] === 'Selesai')->values(),
            default => $allTasks,
        };

        // Query model mentah untuk backward compatibility
        $bookings = $this->antrianService->getDailyQueue($user, $date, $filters);
        $orders = $this->antrianService->getDailyOrderQueue($user, $date, $filters);

        return view('shared.antrian.index', [
            'tasks' => $tasks,
            'isHistory' => false,
            'currentFilter' => $statusFilter,
            'counts' => $counts,
            'date' => $date,
            'branches' => $branches,
            'filters' => $filters,
            'bookings' => $bookings,
            'orders' => $orders,
        ]);
    }
}
