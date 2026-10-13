<?php

namespace App\Http\Controllers;

use App\Services\AktivitasService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AktivitasController extends Controller
{
    public function __construct(protected AktivitasService $aktivitasService) {}

    /**
     * Display paginated activity log.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['modul', 'user_id', 'q']);
        $logs = $this->aktivitasService->getLogs($request->user(), $filters);
        $modulList = $this->aktivitasService->getModulList();

        return view('aktivitas.index', compact('logs', 'filters', 'modulList'));
    }
}
