<?php

use App\Http\Controllers\Accounts\AccountController;
use App\Http\Controllers\Auth\InitialPasswordController;
use App\Http\Controllers\GraduationProcesses\GraduationProcessController;
use App\Http\Controllers\TutorAssignments\TutorAssignmentController;
use App\Http\Controllers\WorkshopTeacher\WorkshopTeacherController;
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

Route::middleware(['auth', 'can:tutores.asignar'])->prefix('tutores')->name('tutor-assignments.')->group(function () {
    Route::get('/', [TutorAssignmentController::class, 'index'])->name('index');
    Route::get('{graduationProcess}/asignar', [TutorAssignmentController::class, 'edit'])->name('edit');
    Route::put('{graduationProcess}', [TutorAssignmentController::class, 'update'])->name('update');
});

Route::middleware(['auth', 'can:taller.designar'])->group(function () {
    Route::get('taller-de-grado', [WorkshopTeacherController::class, 'edit'])->name('workshop-teacher.edit');
    Route::put('taller-de-grado', [WorkshopTeacherController::class, 'update'])->name('workshop-teacher.update');
});

require __DIR__.'/settings.php';
