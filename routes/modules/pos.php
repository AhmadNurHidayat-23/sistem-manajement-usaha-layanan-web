<?php

use App\Modules\KasirPOS\Controllers\PembayaranController;
use App\Modules\KasirPOS\Controllers\PosController;
use App\Modules\KasirPOS\Controllers\ShiftController;
use App\Modules\KasirPOS\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| POS / Kasir Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:owner,manager,cashier'])->group(function () {
    // POS Terminal & Source Data
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('pos/source-data', [PosController::class, 'sourceData'])->name('pos.source-data');

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
