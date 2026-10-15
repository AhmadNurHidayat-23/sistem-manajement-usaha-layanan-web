<?php

use App\Http\Controllers\Karyawan\KaryawanDashboardController;
use App\Http\Controllers\Kasir\KasirDashboardController;
use App\Http\Controllers\Kasir\PaymentGatewayController;
use App\Http\Controllers\Kasir\TransaksiController;
use App\Http\Controllers\Manager\ManagerDashboardController;
use App\Http\Controllers\Owner\InternalUserController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Shared\AktivitasController;
use App\Http\Controllers\Shared\NotificationFeedController;
use App\Http\Controllers\Shared\NotifikasiController;
use Illuminate\Support\Facades\Route;

// Halaman Customer / Public Landing Page
Route::get('/', [HomeController::class, 'home'])->name('customer.home');

require __DIR__.'/modules/auth.php';
require __DIR__.'/modules/branch.php';
require __DIR__.'/modules/catalog.php';
require __DIR__.'/modules/booking.php';
require __DIR__.'/modules/pos.php';
require __DIR__.'/modules/employee.php';
require __DIR__.'/modules/membership.php';
require __DIR__.'/modules/promotion.php';
require __DIR__.'/modules/report.php';
require __DIR__.'/modules/inventory.php';
require __DIR__.'/modules/ulasan.php';
require __DIR__.'/modules/customer.php';

Route::middleware(['auth', 'role:owner,manager'])->group(function () {
    Route::get('aktivitas', [AktivitasController::class, 'index'])->name('aktivitas.index');
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
});

// In-App Notification Feed & Actions (Lonceng Notifikasi)
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('feed', [NotificationFeedController::class, 'feed'])->name('feed');
    Route::post('{id}/read', [NotificationFeedController::class, 'markAsRead'])->name('read');
    Route::post('read-all', [NotificationFeedController::class, 'markAllAsRead'])->name('read-all');
});

// Halaman Pembayaran Kasir (URL semantik netral status, aman tanpa ID internal)
Route::middleware('auth')->group(function () {
    Route::get('kasir/pembayaran/{identifier}', [PaymentGatewayController::class, 'waiting'])
        ->name('kasir.pembayaran');
    Route::get('kasir/pembayaran/{identifier}/metode', [PaymentGatewayController::class, 'show'])
        ->name('kasir.pembayaran.show');
    Route::get('kasir/pembayaran/{identifier}/poll', [PaymentGatewayController::class, 'poll'])
        ->name('kasir.pembayaran.poll');
    Route::get('kasir/struk/{transaction}', [TransaksiController::class, 'receipt'])
        ->name('kasir.struk');

    // Kompatibilitas route lama kasir
    Route::get('kasir/pembayaran/menunggu/{identifier}', [PaymentGatewayController::class, 'waiting'])
        ->name('kasir.pembayaran.waiting');
    Route::get('kasir/pembayaran/metode/{identifier}', [PaymentGatewayController::class, 'show']);
});

// Gateway payment routes (dengan kompatibilitas route legacy dan polling)
Route::middleware('auth')->prefix('payment')->name('payment.')->group(function () {
    Route::get('{identifier}/gateway', [PaymentGatewayController::class, 'show'])->name('gateway.show');
    Route::post('{identifier}/gateway/initiate', [PaymentGatewayController::class, 'initiate'])->name('gateway.initiate');
    Route::get('waiting/{identifier}', [PaymentGatewayController::class, 'waitingRedirect']);
    Route::get('poll/{identifier}', [PaymentGatewayController::class, 'poll'])->name('gateway.poll');

    // Rute sandbox pembayaran
    Route::post('simulate-success/{identifier}', [PaymentGatewayController::class, 'simulateSuccess'])->name('gateway.simulate');
});

// Public webhook endpoint — no auth, no CSRF (excluded in bootstrap/app.php)
Route::post('payment/webhook', [PaymentGatewayController::class, 'webhook'])->name('payment.webhook');

Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/owner/dashboard', [OwnerDashboardController::class, 'index'])->name('owner.dashboard');
    Route::resource('owner/internal-users', InternalUserController::class)
        ->names('owner.internal-users')
        ->parameters(['internal-users' => 'internal_user'])
        ->except(['show', 'destroy']);
});

Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index'])->name('manager.dashboard');
});

Route::middleware(['auth', 'role:cashier'])->group(function () {
    Route::get('/cashier/dashboard', [KasirDashboardController::class, 'index'])->name('cashier.dashboard');
    Route::get('/kasir/dashboard', fn () => redirect()->route('cashier.dashboard'))->name('kasir.dashboard');
});

Route::middleware(['auth', 'role:employee'])->group(function () {
    Route::get('/employee/dashboard', [KaryawanDashboardController::class, 'index'])->name('employee.dashboard');
});
