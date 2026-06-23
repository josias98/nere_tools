<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Leaves\Admin\LeaveAdminController;
use App\Http\Controllers\Leaves\Admin\LeaveImportController;
use App\Http\Controllers\Leaves\Admin\LeaveValidatorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin-area'])->group(function (): void {
    Route::view('/admin', 'admin.index')->name('admin.index');

    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    Route::get('/admin/conges', [LeaveAdminController::class, 'index'])->name('admin.leaves.index');
    Route::put('/admin/conges/collaborateurs/{employee}', [LeaveAdminController::class, 'updateEmployee'])->name('admin.leaves.employees.update');
    Route::put('/admin/conges/parametres', [LeaveAdminController::class, 'updateSettings'])->name('admin.leaves.settings.update');
    Route::post('/admin/conges/validateurs', [LeaveValidatorController::class, 'store'])->name('admin.leaves.validators.store');
    Route::put('/admin/conges/validateurs/{validator}', [LeaveValidatorController::class, 'update'])->name('admin.leaves.validators.update');
    Route::delete('/admin/conges/validateurs/{validator}', [LeaveValidatorController::class, 'destroy'])->name('admin.leaves.validators.destroy');
    Route::post('/admin/conges/import', LeaveImportController::class)->name('admin.leaves.import');
});
