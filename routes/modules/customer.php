<?php

use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Modules\BookingAntrian\Controllers\PelangganBookingController;
use App\Modules\BookingAntrian\Controllers\PelangganPesananController;
use App\Modules\KasirPOS\Controllers\PelangganPembayaranController;
use App\Modules\Katalog\Controllers\PelangganLayananController;
use App\Modules\Katalog\Controllers\PelangganUlasanController;
use App\Modules\MembershipPromo\Controllers\PelangganMemberController;
use App\Modules\MembershipPromo\Controllers\PelangganPromoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Module Routes
|--------------------------------------------------------------------------
| Public customer routes are accessible without login.
| Authenticated customer routes require 'auth' + 'role:customer' middleware.
|
*/

// Public routes (accessible without login)
Route::prefix('customer')->group(function () {
    Route::redirect('/', '/');

    // Detail Layanan
    Route::get('/layanan/{service}', [PelangganLayananController::class, 'show'])->name('customer.layanan.show');

    // Promo
    Route::get('/promo', [PelangganPromoController::class, 'index'])->name('customer.promo');
});

// Authenticated customer routes (login required)
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    // Dashboard Pelanggan
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    // Booking (Appointment)
    Route::get('/booking', [PelangganBookingController::class, 'index'])->name('booking.index');
    Route::get('/booking/buat', [PelangganBookingController::class, 'create'])->name('booking.create');
    Route::get('/booking/branch-data', [PelangganBookingController::class, 'branchData'])->name('booking.branch-data');
    Route::get('/booking/available-slots', [PelangganBookingController::class, 'availableSlots'])->name('booking.available-slots');
    Route::post('/booking', [PelangganBookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/{booking}', [PelangganBookingController::class, 'show'])->name('booking.show');
    Route::patch('/booking/{booking}/batal', [PelangganBookingController::class, 'cancel'])->name('booking.cancel');

    // Pesanan / Order (Drop-off)
    Route::get('/pesanan', [PelangganPesananController::class, 'orders'])->name('pesanan.index');
    Route::get('/pesanan/buat', [PelangganPesananController::class, 'createOrder'])->name('pesanan.create');
    Route::get('/pesanan/branch-services', [PelangganPesananController::class, 'branchServices'])->name('pesanan.branch-services');
    Route::post('/pesanan/konfirmasi', [PelangganPesananController::class, 'confirmOrder'])->name('pesanan.confirm');
    Route::post('/pesanan', [PelangganPesananController::class, 'storeOrder'])->name('pesanan.store');
    Route::post('/pesanan/select', [PelangganPesananController::class, 'selectOrder'])->name('pesanan.select');
    Route::get('/pesanan/detail', [PelangganPesananController::class, 'showDetail'])->name('pesanan.detail');
    Route::get('/pesanan/{order}', [PelangganPesananController::class, 'showOrder'])->name('pesanan.show');
    Route::patch('/pesanan/{order}/batal', [PelangganPesananController::class, 'cancelOrder'])->name('pesanan.cancel');

    // Pembayaran
    Route::get('/pesanan/{order}/pembayaran', [PelangganPembayaranController::class, 'show'])->name('pembayaran.show');

    // Member & Poin
    Route::get('/member', [PelangganMemberController::class, 'index'])->name('member');
    Route::post('/member/daftar', [PelangganMemberController::class, 'register'])->name('member.register');

    // Ulasan
    Route::get('/ulasan', [PelangganUlasanController::class, 'index'])->name('ulasan.index');
    Route::get('/ulasan/tulis', [PelangganUlasanController::class, 'create'])->name('ulasan.create');
    Route::post('/ulasan', [PelangganUlasanController::class, 'store'])->name('ulasan.store');
    Route::get('/ulasan/{ulasan}/edit', [PelangganUlasanController::class, 'edit'])->name('ulasan.edit');
    Route::put('/ulasan/{ulasan}', [PelangganUlasanController::class, 'update'])->name('ulasan.update');
});
