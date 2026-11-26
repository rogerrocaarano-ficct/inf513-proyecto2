<?php

use App\Http\Controllers\Accounts\AccountController;
use App\Http\Controllers\Auth\InitialPasswordController;
use App\Http\Controllers\GraduationProcesses\GraduationProcessController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('contrasena-inicial', [InitialPasswordController::class, 'edit'])->name('initial-password.edit');
    Route::put('contrasena-inicial', [InitialPasswordController::class, 'update'])->name('initial-password.update');
});

Route::middleware(['auth', 'can:cuentas.gestionar'])->prefix('cuentas')->name('accounts.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::post('/', [AccountController::class, 'store'])->name('store');
    Route::put('{account}', [AccountController::class, 'update'])->name('update');
    Route::patch('{account}/desactivar', [AccountController::class, 'deactivate'])->name('deactivate');
    Route::patch('{account}/reactivar', [AccountController::class, 'reactivate'])->name('reactivate');
});

Route::middleware(['auth', 'can:tramites.iniciar'])->group(function () {
    Route::get('tramites/nuevo', [GraduationProcessController::class, 'create'])->name('graduation-processes.create');
    Route::post('tramites', [GraduationProcessController::class, 'store'])->name('graduation-processes.store');
});

require __DIR__.'/settings.php';
