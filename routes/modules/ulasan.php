<?php

use App\Http\Controllers\Owner\OwnerUlasanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ulasan (Rating & Review) Module Routes
|--------------------------------------------------------------------------
*/

// Owner & Manager: view all reviews
Route::middleware(['auth', 'role:owner,manager'])->group(function () {
    Route::get('ulasan', [OwnerUlasanController::class, 'index'])->name('ulasan.index');
    Route::get('ulasan/layanan/{serviceId}', [OwnerUlasanController::class, 'forService'])->name('ulasan.by-service');
});

// Ulasan pelanggan
Route::middleware('auth')->group(function () {
    Route::get('ulasan/tulis', [OwnerUlasanController::class, 'create'])->name('ulasan.create');
    Route::post('ulasan', [OwnerUlasanController::class, 'store'])->name('ulasan.store');
    Route::get('ulasan/{ulasan}/edit', [OwnerUlasanController::class, 'edit'])->name('ulasan.edit');
    Route::put('ulasan/{ulasan}', [OwnerUlasanController::class, 'update'])->name('ulasan.update');
});
