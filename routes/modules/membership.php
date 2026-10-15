<?php

use App\Http\Controllers\Kasir\AdminMemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Membership / Member & Loyalty Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Admin & Cashier management (Kasir & Owner sesuai SRS)
    Route::middleware(['role:owner,cashier'])->group(function () {
        Route::get('members', [AdminMemberController::class, 'index'])->name('members.index');
        Route::get('members/create', [AdminMemberController::class, 'create'])->name('members.create');
        Route::post('members', [AdminMemberController::class, 'store'])->name('members.store');
        Route::put('members/{membership}', [AdminMemberController::class, 'update'])->name('members.update');
        Route::get('members/{membership}', [AdminMemberController::class, 'show'])->name('members.show');
    });

    // Penukaran poin (Kasir di POS, Owner, atau Pelanggan mandiri)
    Route::middleware(['role:owner,cashier,customer'])->group(function () {
        Route::post('members/{membership}/redeem', [AdminMemberController::class, 'redeem'])->name('members.redeem');
    });

    // Member mandiri (Pelanggan)
    Route::middleware(['role:customer'])->group(function () {
        Route::get('my-membership', [AdminMemberController::class, 'myMembership'])->name('members.my-membership');
        Route::post('my-membership/register', [AdminMemberController::class, 'registerSelf'])->name('members.register-self');
        Route::get('membership-packages', [AdminMemberController::class, 'packages'])->name('members.packages');
    });
});
