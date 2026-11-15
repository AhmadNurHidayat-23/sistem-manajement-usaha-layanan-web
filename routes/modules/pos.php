<?php

use App\Http\Controllers\Kasir\OrderReceiveController;
use App\Http\Controllers\Kasir\PembayaranController;
use App\Http\Controllers\Kasir\PosController;
use App\Http\Controllers\Kasir\ShiftController;
use App\Http\Controllers\Kasir\TransaksiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| POS / Kasir Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:owner,manager,cashier'])->group(function () {
    // POS Terminal & Source Data
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('pos/services', [PosController::class, 'services'])->name('pos.services');
    Route::get('pos/source-data', [PosController::class, 'sourceData'])->name('pos.source-data');
    Route::post('pos/bookings/{booking}/check-in', [PosController::class, 'checkInBooking'])->name('pos.bookings.check-in');
    Route::post('pos/orders/{order}/weigh', [PosController::class, 'recordWeight'])->name('pos.orders.weigh');
    Route::post('pos/orders/{order}/receive', [OrderReceiveController::class, 'receiveOrder'])->name('pos.orders.receive');
    Route::get('pos/branches/{branch}/employees', [OrderReceiveController::class, 'branchEmployees'])->name('pos.branch.employees');

    // Shifts
    Route::get('pos/shift', [ShiftController::class, 'index'])->name('pos.shift.index');
    Route::post('pos/shift/open', [ShiftController::class, 'openShift'])->name('pos.shift.open');
    Route::post('pos/shift/close', [ShiftController::class, 'closeShift'])->name('pos.shift.close');

    // Transaksi kasir
    Route::get('pos/transactions', [TransaksiController::class, 'index'])->name('pos.transactions.index');
    Route::post('pos/transactions', [TransaksiController::class, 'store'])->name('pos.transactions.store');
    Route::get('pos/transactions/{transaction}', [TransaksiController::class, 'show'])->name('pos.transactions.show');
    Route::get('pos/transactions/{transaction}/receipt', [TransaksiController::class, 'receipt'])->name('pos.transactions.receipt');
    Route::post('pos/transactions/{transaction}/send-receipt', [TransaksiController::class, 'sendReceipt'])->name('pos.transactions.send-receipt');

    // Payments
    Route::post('pos/payments', [PembayaranController::class, 'store'])->name('pos.payments.store');
});
