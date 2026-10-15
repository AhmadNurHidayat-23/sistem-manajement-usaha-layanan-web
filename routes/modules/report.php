<?php

use App\Http\Controllers\Owner\LaporanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Report / Laporan Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:owner,manager'])->group(function () {
    Route::get('reports', [LaporanController::class, 'index'])->name('reports.index');
    Route::get('reports/export/excel', [LaporanController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('reports/export/pdf', [LaporanController::class, 'exportPdf'])->name('reports.export.pdf');
});
