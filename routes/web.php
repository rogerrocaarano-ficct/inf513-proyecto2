<?php

use App\Http\Controllers\Accounts\AccountController;
use App\Http\Controllers\Auth\InitialPasswordController;
use App\Http\Controllers\Committees\CommitteeController;
use App\Http\Controllers\Defenses\DefenseController;
use App\Http\Controllers\Defenses\DefenseReviewController;
use App\Http\Controllers\GraduationProcesses\GraduationProcessController;
use App\Http\Controllers\ProjectProfiles\ProjectProfileController;
use App\Http\Controllers\ProjectVersions\ProjectVersionController;
use App\Http\Controllers\TutorAssignments\TutorAssignmentController;
use App\Http\Controllers\TutorObservations\TutorObservationController;
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

// UC-004: Presentar versión del proyecto
Route::middleware(['auth', 'can:versiones.presentar'])->prefix('versiones')->name('project-versions.')->group(function () {
    Route::get('presentar', [ProjectVersionController::class, 'create'])->name('create');
    Route::post('/', [ProjectVersionController::class, 'store'])->name('store');
});

// UC-005: Evaluar perfil de proyecto
Route::middleware(['auth', 'can:perfiles.evaluar'])->prefix('perfiles')->name('project-profiles.')->group(function () {
    Route::get('/', [ProjectProfileController::class, 'index'])->name('index');
    Route::get('{projectVersion}', [ProjectProfileController::class, 'show'])->name('show');
    Route::put('{projectVersion}', [ProjectProfileController::class, 'update'])->name('update');
    Route::get('{projectVersion}/archivo', [ProjectProfileController::class, 'download'])->name('download');
});

// UC-006: Asignar tribunal evaluador
Route::middleware(['auth', 'can:tribunales.asignar'])->prefix('tribunales')->name('committees.')->group(function () {
    Route::get('/', [CommitteeController::class, 'index'])->name('index');
    Route::get('{graduationProcess}', [CommitteeController::class, 'edit'])->name('edit');
    Route::post('{graduationProcess}', [CommitteeController::class, 'store'])->name('store');
    Route::put('{graduationProcess}/reemplazo', [CommitteeController::class, 'replace'])->name('replace');
});

// UC-007: Observar versión del proyecto como tutor
Route::middleware(['auth', 'can:versiones.observar'])->prefix('tutelados')->name('tutor-observations.')->group(function () {
    Route::get('/', [TutorObservationController::class, 'index'])->name('index');
    Route::get('tramites/{graduationProcess}', [TutorObservationController::class, 'show'])->name('show');
    Route::get('versiones/{projectVersion}', [TutorObservationController::class, 'version'])->name('version');
    Route::post('versiones/{projectVersion}/observaciones', [TutorObservationController::class, 'store'])->name('store');
    Route::get('versiones/{projectVersion}/archivo', [TutorObservationController::class, 'download'])->name('download');
});

// UC-009: Programar pre-defensa y defensa formal
Route::middleware(['auth', 'can:defensas.programar'])->prefix('defensas')->name('defenses.')->group(function () {
    Route::get('/', [DefenseController::class, 'index'])->name('index');
    Route::post('/', [DefenseController::class, 'store'])->name('store');
});

// UC-010: Registrar observaciones de pre-defensa
Route::middleware(['auth', 'can:pre-defensas.registrar'])->prefix('pre-defensas')->name('defense-reviews.')->group(function () {
    Route::get('/', [DefenseReviewController::class, 'index'])->name('index');
    Route::get('{defense}', [DefenseReviewController::class, 'show'])->name('show');
    Route::get('{defense}/versiones/{projectVersion}/archivo', [DefenseReviewController::class, 'download'])->name('download');
    Route::post('{defense}', [DefenseReviewController::class, 'store'])->name('store');
});

require __DIR__.'/settings.php';
