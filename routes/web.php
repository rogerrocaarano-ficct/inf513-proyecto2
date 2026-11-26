<?php

use App\Http\Controllers\Auth\InitialPasswordController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('contrasena-inicial', [InitialPasswordController::class, 'edit'])->name('initial-password.edit');
    Route::put('contrasena-inicial', [InitialPasswordController::class, 'update'])->name('initial-password.update');
});

require __DIR__.'/settings.php';
