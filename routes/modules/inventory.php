<?php

use App\Http\Controllers\Manager\InventoriController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Inventory / Inventori Bahan Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:owner,manager'])->group(function () {
    Route::get('inventori', [InventoriController::class, 'index'])->name('inventori.index');
    Route::get('inventori/riwayat', [InventoriController::class, 'history'])->name('inventori.history');
    Route::get('inventori/create', [InventoriController::class, 'create'])->name('inventori.create');
    Route::post('inventori', [InventoriController::class, 'store'])->name('inventori.store');
    Route::get('inventori/{item}', [InventoriController::class, 'show'])->name('inventori.show');
    Route::get('inventori/{item}/edit', [InventoriController::class, 'edit'])->name('inventori.edit');
    Route::put('inventori/{item}', [InventoriController::class, 'update'])->name('inventori.update');
    Route::post('inventori/{item}/stock-in', [InventoriController::class, 'stockIn'])->name('inventori.stock-in');
    Route::post('inventori/{item}/stock-out', [InventoriController::class, 'stockOut'])->name('inventori.stock-out');
});
