<?php

use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\CustomerMobileController;
use App\Http\Controllers\Api\Mobile\EmployeeMobileController;
use App\Http\Controllers\Api\Mobile\PaymentMobileController;
use App\Http\Controllers\Api\Mobile\PublicMobileController;
/*
|--------------------------------------------------------------------------
| Mobile API Routes
|--------------------------------------------------------------------------
| Prefix: /api
*/

use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    // Auth
    Route::post('/login', [AuthController::class, 'login']);

    // Public Data
    Route::get('/branches', [PublicMobileController::class, 'branches']);
    Route::get('/services', [PublicMobileController::class, 'services']);

    // Authenticated Mobile Routes
    Route::middleware('mobile.auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Customer Only Routes
        Route::middleware('mobile.auth:customer')->prefix('customer')->group(function () {
            Route::get('/bookings', [CustomerMobileController::class, 'bookings']);
            Route::post('/bookings', [CustomerMobileController::class, 'storeBooking']);
            Route::get('/bookings/{id}', [CustomerMobileController::class, 'showBooking']);

            Route::get('/orders', [CustomerMobileController::class, 'orders']);
            Route::post('/orders', [CustomerMobileController::class, 'storeOrder']);
            Route::get('/orders/{id}', [CustomerMobileController::class, 'showOrder']);

            Route::get('/promos', [CustomerMobileController::class, 'promos']);
            Route::get('/membership', [CustomerMobileController::class, 'membership']);
            Route::post('/membership/register', [CustomerMobileController::class, 'registerMembership']);

            // Payments
            Route::post('/payments', [PaymentMobileController::class, 'store']);
            Route::get('/payments/{id}', [PaymentMobileController::class, 'show']);
            Route::get('/payments/{id}/status', [PaymentMobileController::class, 'status']);
            Route::post('/payments/{id}/simulate', [PaymentMobileController::class, 'simulate']);
        });

        // Employee Only Routes
        Route::middleware('mobile.auth:employee')->prefix('employee')->group(function () {
            Route::get('/dashboard', [EmployeeMobileController::class, 'dashboard']);
            Route::get('/tasks', [EmployeeMobileController::class, 'tasks']);
            Route::get('/tasks/{id}', [EmployeeMobileController::class, 'showTask']);
            Route::patch('/tasks/{id}/status', [EmployeeMobileController::class, 'updateStatus']);
        });
    });
});
