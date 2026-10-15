<?php

use App\Http\Controllers\Owner\CabangController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Cabang Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:owner,manager'])->group(function () {
    Route::get('branches', [CabangController::class, 'index'])->name('branches.index');
    Route::get('branches/create', [CabangController::class, 'create'])->name('branches.create');
    Route::post('branches', [CabangController::class, 'store'])->name('branches.store');
    Route::get('branches/{branch}', [CabangController::class, 'show'])->name('branches.show');
    Route::get('branches/{branch}/edit', [CabangController::class, 'edit'])->name('branches.edit');
    Route::put('branches/{branch}', [CabangController::class, 'update'])->name('branches.update');
    Route::patch('branches/{branch}/toggle-status', [CabangController::class, 'toggleStatus'])->name('branches.toggle-status');
});
