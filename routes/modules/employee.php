<?php

use App\Modules\Karyawan\Controllers\DetailTugasController;
use App\Modules\Karyawan\Controllers\KaryawanController;
use App\Modules\Karyawan\Controllers\KomisiKaryawanController;
use App\Modules\Karyawan\Controllers\ShiftKaryawanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Employee / Karyawan Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Owner & Manager employee management
    Route::middleware(['role:owner,manager'])->group(function () {
        Route::get('employees', [KaryawanController::class, 'index'])->name('employees.index');
        Route::get('employees/create', [KaryawanController::class, 'create'])->name('employees.create');
        Route::post('employees', [KaryawanController::class, 'store'])->name('employees.store');
        Route::get('employees/{employee}/edit', [KaryawanController::class, 'edit'])->name('employees.edit');
        Route::put('employees/{employee}', [KaryawanController::class, 'update'])->name('employees.update');
        Route::post('employees/{employee}/toggle-status', [KaryawanController::class, 'toggleStatus'])->name('employees.toggle-status');

        // Shifts Management
        Route::get('employee-shifts', [ShiftKaryawanController::class, 'index'])->name('employees.shifts.index');
        Route::post('employee-shifts', [ShiftKaryawanController::class, 'store'])->name('employees.shifts.store');
        Route::put('employee-shifts/{shift}', [ShiftKaryawanController::class, 'update'])->name('employees.shifts.update');
        Route::patch('employee-shifts/{shift}/status', [ShiftKaryawanController::class, 'updateStatus'])->name('employees.shifts.status');
        Route::delete('employee-shifts/{shift}', [ShiftKaryawanController::class, 'destroy'])->name('employees.shifts.destroy');

        // Commissions Management
        Route::get('employee-commissions', [KomisiKaryawanController::class, 'index'])->name('employees.commissions.index');
        Route::post('employee-commissions', [KomisiKaryawanController::class, 'store'])->name('employees.commissions.store');
        Route::patch('employee-commissions/{commission}/status', [KomisiKaryawanController::class, 'updateStatus'])->name('employees.commissions.status');
    });

    // Detail karyawan
    Route::get('employees/{employee}', [KaryawanController::class, 'show'])->name('employees.show');

    // Employee self-service
    Route::middleware(['role:employee'])->group(function () {
        Route::get('my-shifts', [ShiftKaryawanController::class, 'myShifts'])->name('employees.my-shifts');
        Route::get('my-commissions', [KomisiKaryawanController::class, 'myCommissions'])->name('employees.my-commissions');
        Route::get('employee/tugas/detail', [DetailTugasController::class, 'show'])->name('employee.tugas.detail');
        Route::get('karyawan/tugas/detail', [DetailTugasController::class, 'show']);
        Route::get('karyawan/tugas/{identifier}', [DetailTugasController::class, 'showTask'])->name('employee.tugas.show');
        Route::get('employee/tugas/{identifier}', [DetailTugasController::class, 'showTask']);
    });
});
