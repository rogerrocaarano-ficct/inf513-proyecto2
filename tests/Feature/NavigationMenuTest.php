<?php

use App\Enums\PermissionKey;
use App\Enums\RoleName;
use App\Models\MenuItem;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Registers the routes the menu options lead to, as the use cases will build them.
 */
function registerMenuRoutes(string ...$names): void
{
    foreach ($names as $name) {
        Route::middleware('web')->get('/menu-test/'.$name, fn () => 'ok')->name($name);
    }

    Route::getRoutes()->refreshNameLookups();
}

test('the menu shows only the options the permissions of the user role enable (FR-029)', function () {
    registerMenuRoutes('accounts.index', 'graduation-processes.create');
    $administrator = User::factory()->systemAdministrator()->create();

    $this->actingAs($administrator)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('menu.0.title', 'Inicio')
            ->where('menu.1.title', 'Cuentas de usuario')
            ->count('menu', 2));
});

test('the menu of a user with several roles is the union of their options (UC-018 BR-006, ADR-005)', function () {
    registerMenuRoutes('tutor-assignments.index', 'workshop-teacher.edit', 'committees.index', 'accounts.index');
    $director = Teacher::factory()->careerDirector()->create()->user;

    $this->actingAs($director)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('menu', fn ($menu) => collect($menu)->pluck('title')->all() === [
                'Inicio', 'Asignar tutor', 'Docente de taller de grado', 'Tribunal evaluador', 'Mis estudiantes',
            ]));
});

test('a menu option whose page is not built yet is left out', function () {
    MenuItem::query()->create([
        'title' => 'Página futura',
        'route_name' => 'future-page.index',
        'sort_order' => 999,
        'permission_id' => Permission::query()->where('key', PermissionKey::ViewHome)->value('id'),
    ]);
    $teacher = User::factory()->withRoles(RoleName::Teacher)->create();

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('menu', fn ($menu) => collect($menu)->pluck('title')->all() === ['Inicio', 'Mis estudiantes']));
});

test('a change of roles changes the menu from the next page (UC-018 BR-006)', function () {
    registerMenuRoutes('graduation-processes.create');
    $user = User::factory()->withRoles(RoleName::Teacher)->create();

    $this->actingAs($user)->get(route('dashboard'))->assertInertia(fn ($page) => $page->count('menu', 2));

    $user->roles()->attach(Role::query()->where('name', RoleName::AdministrativeAssistant)->value('id'));

    $this->actingAs($user->fresh())
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('menu.1.title', 'Iniciar trámite'));
});

test('the permissions of every role of the user authorize its abilities (ADR-005)', function () {
    $director = Teacher::factory()->careerDirector()->create()->user;
    $assistant = User::factory()->administrativeAssistant()->create();

    expect($director->can(PermissionKey::AssignTutor->value))->toBeTrue()
        ->and($director->can(PermissionKey::ManageAccounts->value))->toBeFalse()
        ->and($assistant->can(PermissionKey::StartGraduationProcess->value))->toBeTrue()
        ->and($assistant->can(PermissionKey::AssignCommittee->value))->toBeFalse();
});
