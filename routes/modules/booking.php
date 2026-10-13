<?php

use App\Modules\BookingAntrian\Controllers\AntrianController;
use App\Modules\BookingAntrian\Controllers\BookingAvailabilityController;
use App\Modules\BookingAntrian\Controllers\BookingController;
use App\Modules\BookingAntrian\Controllers\BookingStatusController;
use App\Modules\BookingAntrian\Controllers\ManagerPesananController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Booking Module Routes
|--------------------------------------------------------------------------
*/

// ── Appointment Bookings ──────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Slot booking
    Route::get('bookings/available-slots', [BookingAvailabilityController::class, 'availableSlots'])->name('bookings.available-slots');
    Route::get('bookings/branch-employees', [BookingAvailabilityController::class, 'branchEmployees'])->name('bookings.branch-employees');

    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::patch('bookings/{booking}/status', [BookingStatusController::class, 'updateStatus'])->name('bookings.update-status');
    Route::patch('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});

// ── Drop-off Orders / Pesanan ─────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('orders', [ManagerPesananController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [ManagerPesananController::class, 'create'])->name('orders.create');
    Route::post('orders', [ManagerPesananController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}', [ManagerPesananController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [ManagerPesananController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('orders/{order}/cancel', [ManagerPesananController::class, 'cancel'])->name('orders.cancel');
});

// ── Daily Queue / Antrian ─────────────────────────────────────────────────
Route::middleware(['auth', 'role:owner,manager,employee,cashier'])->group(function () {
    Route::get('queue', [AntrianController::class, 'index'])->name('queue.index');
});
