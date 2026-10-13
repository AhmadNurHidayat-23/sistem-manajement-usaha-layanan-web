<?php

namespace App\Modules\LaporanKeuangan\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\LaporanKeuangan\Requests\FilterLaporanRequest;
use App\Modules\LaporanKeuangan\Services\LaporanService;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function __construct(
        protected LaporanService $laporanService
    ) {}

    /**
     * Laporan manajemen & analitik.
     */
    public function index(FilterLaporanRequest $request): View
    {
        $report = $this->laporanService->getComprehensiveReport($request->user(), $request->validated());

        return view('modules.laporan-keuangan.index', compact('report'));
    }

    /**
     * Export report data as downloadable Excel spreadsheet (.xlsx).
     */
    public function exportExcel(FilterLaporanRequest $request): StreamedResponse
    {
        return $this->laporanService->exportExcel($request->user(), $request->validated());
    }

    /**
     * Export report data as downloadable PDF (.pdf) generated server-side via Dompdf.
     */
    public function exportPdf(FilterLaporanRequest $request): Response
    {
        return $this->laporanService->exportPdf($request->user(), $request->validated());
    }
}
