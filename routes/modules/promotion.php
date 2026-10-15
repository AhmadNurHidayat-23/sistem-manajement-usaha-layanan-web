<?php

use App\Http\Controllers\Owner\OwnerPromoController;
use App\Http\Controllers\Pelanggan\PelangganPromoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Promotion / Promo & Voucher Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Owner & Manager promo management
    Route::middleware(['role:owner,manager'])->group(function () {
        Route::get('promotions', [OwnerPromoController::class, 'index'])->name('promotions.index');
        Route::get('promotions/create', [OwnerPromoController::class, 'create'])->name('promotions.create');
        Route::post('promotions', [OwnerPromoController::class, 'store'])->name('promotions.store');
        Route::get('promotions/{promotion}/edit', [OwnerPromoController::class, 'edit'])->name('promotions.edit');
        Route::put('promotions/{promotion}', [OwnerPromoController::class, 'update'])->name('promotions.update');
        Route::post('promotions/{promotion}/toggle-status', [OwnerPromoController::class, 'toggleStatus'])->name('promotions.toggle-status');
    });

    // Persetujuan pengajuan promo khusus Owner
    Route::middleware(['role:owner'])->group(function () {
        Route::post('promotions/{promotion}/approve', [OwnerPromoController::class, 'approve'])->name('promotions.approve');
        Route::post('promotions/{promotion}/reject', [OwnerPromoController::class, 'reject'])->name('promotions.reject');
    });

    // Validasi promo
    Route::post('promotions/validate', [OwnerPromoController::class, 'validatePromo'])->name('promotions.validate');
});

// Customer public voucher catalog (Dapat diakses publik/pelanggan secara sah)
Route::get('voucher-promo', [PelangganPromoController::class, 'publicPromos'])->name('promotions.public');
