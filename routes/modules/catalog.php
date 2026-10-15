<?php

use App\Http\Controllers\Owner\KategoriLayananController;
use App\Http\Controllers\Owner\LayananController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Layanan Module Routes
|--------------------------------------------------------------------------
*/

// Public Marketplace / Catalog Route
Route::get('catalog', [LayananController::class, 'publicCatalog'])->name('public.catalog');

// Admin & Manager catalog management routes
Route::middleware(['auth', 'role:owner,manager'])->group(function () {
    // Categories
    Route::get('catalog/categories', [KategoriLayananController::class, 'index'])->name('catalog.categories.index');
    Route::post('catalog/categories', [KategoriLayananController::class, 'store'])->name('catalog.categories.store');
    Route::put('catalog/categories/{category}', [KategoriLayananController::class, 'update'])->name('catalog.categories.update');
    Route::patch('catalog/categories/{category}/toggle-status', [KategoriLayananController::class, 'toggleStatus'])->name('catalog.categories.toggle-status');

    // Services CRUD
    Route::get('catalog/services', [LayananController::class, 'index'])->name('catalog.services.index');
    Route::get('catalog/services/create', [LayananController::class, 'create'])->name('catalog.services.create');
    Route::post('catalog/services', [LayananController::class, 'store'])->name('catalog.services.store');
    Route::get('catalog/services/{service}', [LayananController::class, 'show'])->name('catalog.services.show');
    Route::get('catalog/services/{service}/edit', [LayananController::class, 'edit'])->name('catalog.services.edit');
    Route::put('catalog/services/{service}', [LayananController::class, 'update'])->name('catalog.services.update');
    Route::patch('catalog/services/{service}/toggle-status', [LayananController::class, 'toggleStatus'])->name('catalog.services.toggle-status');
});
