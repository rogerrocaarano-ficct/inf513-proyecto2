<?php

use App\Enums\RoleName;
use App\Models\CommitteeMember;
use App\Models\GraduationProcess;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia;

function administrator(): User
{
    return User::factory()->systemAdministrator()->create();
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function accountData(array $overrides = []): array
{
    return [
        'name' => 'Ana Pérez Rojas',
        'email' => 'ana.perez@ejemplo.test',
        'roles' => [RoleName::Teacher->value],
        'password' => 'clave-inicial-1',
        ...$overrides,
    ];
}

test('UC-018 step 1: a user without the manage accounts permission cannot open the page nor register', function () {
    $assistant = User::factory()->administrativeAssistant()->create();

    $this->actingAs($assistant)->get(route('accounts.index'))->assertForbidden();
    $this->actingAs($assistant)->post(route('accounts.store'), accountData())->assertForbidden();

    expect(User::query()->where('email', 'ana.perez@ejemplo.test')->exists())->toBeFalse();
});

test('UC-018 step 1: a guest is sent to log in', function () {
    $this->get(route('accounts.index'))->assertRedirect(route('login'));
});

test('UC-018 step 2: the list shows teachers, the director and assistants but no students nor administrator', function () {
    $administrator = administrator();
    $teacher = Teacher::factory()->create();
    $director = Teacher::factory()->careerDirector()->create();
    $assistant = User::factory()->administrativeAssistant()->inactive()->create(['name' => 'Asistente Inactiva']);
    $student = User::factory()->withRoles(RoleName::Student)->create();

    $this->actingAs($administrator)
        ->get(route('accounts.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('accounts/index')
            ->count('accounts', 3)
            ->where('accounts', fn ($accounts) => collect($accounts)->pluck('id')->sort()->values()->all()
                === collect([$teacher->user_id, $director->user_id, $assistant->id])->sort()->values()->all())
            ->where('accounts', fn ($accounts) => ! collect($accounts)->pluck('id')->contains($student->id)
                && ! collect($accounts)->pluck('id')->contains($administrator->id))
            ->where('accounts', fn ($accounts) => collect($accounts)->firstWhere('id', $assistant->id)['is_active'] === false)
            ->where('accounts', fn ($accounts) => collect($accounts)->every(fn (array $row) => $row['name'] === User::find($row['id'])->name
                && $row['email'] === User::find($row['id'])->email))
            ->where('accounts', fn ($accounts) => collect($accounts)->firstWhere('id', $assistant->id)['name'] === 'Asistente Inactiva'
                && collect($accounts)->firstWhere('id', $teacher->user_id)['email'] === $teacher->user->email)
            ->where('accounts', fn ($accounts) => collect($accounts)->firstWhere('id', $director->user_id)['roles'] === [
                RoleName::CareerDirector->value, RoleName::Teacher->value,
            ] || collect($accounts)->firstWhere('id', $director->user_id)['roles'] === [
                RoleName::Teacher->value, RoleName::CareerDirector->value,
            ])
            ->where('assignableRoles', [
                RoleName::Teacher->value, RoleName::CareerDirector->value, RoleName::AdministrativeAssistant->value,
            ]));
});

test('UC-018 steps 3 to 8: registering an account creates it active with its roles and initial password', function () {
    $this->actingAs(administrator())
        ->post(route('accounts.store'), accountData(['roles' => [RoleName::AdministrativeAssistant->value]]))
        ->assertRedirect(route('accounts.index'))
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Cuenta registrada.']);

    $account = User::query()->where('email', 'ana.perez@ejemplo.test')->sole();

    expect($account->name)->toBe('Ana Pérez Rojas')
        ->and($account->is_active)->toBeTrue()
        ->and($account->hasRole(RoleName::AdministrativeAssistant))->toBeTrue()
        ->and($account->hasRole(RoleName::Teacher))->toBeFalse()
        ->and($account->teacher)->toBeNull()
        ->and(Hash::check('clave-inicial-1', $account->password))->toBeTrue();
});

test('UC-018 BR-005: a registered account must change the initial password and has its email verified', function () {
    $this->actingAs(administrator())->post(route('accounts.store'), accountData());

    $account = User::query()->where('email', 'ana.perez@ejemplo.test')->sole();

    expect($account->must_change_password)->toBeTrue()
        ->and($account->email_verified_at)->not->toBeNull();

    $this->actingAs($account)->get(route('dashboard'))->assertRedirect(route('initial-password.edit'));
});

test('UC-018 step 7: registering with the teacher role also registers an active teacher', function () {
    $this->actingAs(administrator())->post(route('accounts.store'), accountData());

    $account = User::query()->where('email', 'ana.perez@ejemplo.test')->sole();

    expect($account->hasRole(RoleName::Teacher))->toBeTrue()
        ->and($account->teacher)->not->toBeNull()
        ->and($account->teacher->is_active)->toBeTrue();
});

test('UC-018 BR-001: registering a career director also assigns the teacher role and teacher record', function () {
    $this->actingAs(administrator())
        ->post(route('accounts.store'), accountData(['roles' => [RoleName::CareerDirector->value]]))
        ->assertRedirect(route('accounts.index'));

    $account = User::query()->where('email', 'ana.perez@ejemplo.test')->sole();

    expect($account->hasRole(RoleName::CareerDirector))->toBeTrue()
        ->and($account->hasRole(RoleName::Teacher))->toBeTrue()
        ->and($account->teacher->is_active)->toBeTrue();
});

test('UC-018 BR-001: the administrator cannot assign the student nor the administrator role', function (string $role) {
    $this->actingAs(administrator())
        ->post(route('accounts.store'), accountData(['roles' => [$role]]))
        ->assertSessionHasErrors('roles.0');

    expect(User::query()->where('email', 'ana.perez@ejemplo.test')->exists())->toBeFalse();
})->with([RoleName::Student->value, RoleName::SystemAdministrator->value]);

test('UC-018 A6: a missing field is reported and the entered data is kept except the password', function () {
    $this->actingAs(administrator())
        ->from(route('accounts.index'))
        ->post(route('accounts.store'), accountData(['name' => '', 'roles' => []]))
        ->assertRedirect(route('accounts.index'))
        ->assertSessionHasErrors([
            'name' => 'El campo nombre completo es obligatorio.',
            'roles' => 'Seleccione al menos un rol.',
        ])
        ->assertSessionHasInput('email', 'ana.perez@ejemplo.test')
        ->assertSessionMissing('_old_input.password');

    expect(User::query()->where('email', 'ana.perez@ejemplo.test')->exists())->toBeFalse();
});

test('UC-018 A6: an email without a valid format is refused', function () {
    $this->actingAs(administrator())
        ->post(route('accounts.store'), accountData(['email' => 'no-es-un-correo']))
        ->assertSessionHasErrors(['email' => 'El campo correo electrónico debe ser una dirección de correo electrónico válida.']);
});

test('UC-018 BR-005 A6: an initial password shorter than 8 characters is refused', function () {
    $this->actingAs(administrator())
        ->post(route('accounts.store'), accountData(['password' => '1234567']))
        ->assertSessionHasErrors(['password' => 'El campo contraseña inicial debe tener al menos 8 caracteres.']);

    expect(User::query()->where('email', 'ana.perez@ejemplo.test')->exists())->toBeFalse();
});

test('UC-018 A5: an email of another user is refused with its message, whatever its letter case', function () {
    User::factory()->create(['email' => 'ana.perez@ejemplo.test']);

    $this->actingAs(administrator())
        ->post(route('accounts.store'), accountData(['email' => 'Ana.Perez@Ejemplo.TEST']))
        ->assertSessionHasErrors(['email' => 'El correo electrónico ya pertenece a otro usuario.']);

    expect(User::query()->whereRaw('lower(email) = ?', ['ana.perez@ejemplo.test'])->count())->toBe(1);
});

test('UC-018 A7 BR-002: a second active career director is refused naming the current one', function () {
    $current = Teacher::factory()->careerDirector()->create();

    $this->actingAs(administrator())
        ->post(route('accounts.store'), accountData(['roles' => [RoleName::CareerDirector->value]]))
        ->assertSessionHasErrors(['roles' => "Ya hay un director de carrera activo: {$current->user->name}."]);

    expect(User::query()->where('email', 'ana.perez@ejemplo.test')->exists())->toBeFalse();
});

test('UC-018 BR-002: an inactive career director does not prevent registering a new one', function () {
    $former = Teacher::factory()->careerDirector()->create();
    $former->user->update(['is_active' => false]);

    $this->actingAs(administrator())
        ->post(route('accounts.store'), accountData(['roles' => [RoleName::CareerDirector->value]]))
        ->assertSessionHasNoErrors();

    expect(User::query()->where('email', 'ana.perez@ejemplo.test')->exists())->toBeTrue();
});

test('UC-018 A1: changing an account saves its name, email and roles', function () {
    $teacher = Teacher::factory()->create();

    $this->actingAs(administrator())
        ->put(route('accounts.update', $teacher->user), [
            'name' => 'Nombre Nuevo',
            'email' => 'Nuevo@Ejemplo.test',
            'roles' => [RoleName::Teacher->value, RoleName::AdministrativeAssistant->value],
        ])
        ->assertRedirect(route('accounts.index'))
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Cuenta actualizada.']);

    $account = $teacher->user->fresh();

    expect($account->name)->toBe('Nombre Nuevo')
        ->and($account->email)->toBe('nuevo@ejemplo.test')
        ->and($account->hasRole(RoleName::AdministrativeAssistant))->toBeTrue()
        ->and($account->hasRole(RoleName::Teacher))->toBeTrue();
});

test('UC-018 A1 BR-006: a change of roles changes the permissions from the next request', function () {
    $account = User::factory()->administrativeAssistant()->create();

    expect($account->can('tutores.asignar'))->toBeFalse();

    $this->actingAs(administrator())->put(route('accounts.update', $account), [
        'name' => $account->name,
        'email' => $account->email,
        'roles' => [RoleName::CareerDirector->value],
    ]);

    $this->actingAs($account->fresh())
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('auth.permissions', fn ($permissions) => collect($permissions)->contains('tutores.asignar')
                && ! collect($permissions)->contains('tramites.iniciar')));
});

test('UC-018 A1: the account keeps its own email when saving without changing it', function () {
    $teacher = Teacher::factory()->create();

    $this->actingAs(administrator())
        ->put(route('accounts.update', $teacher->user), [
            'name' => 'Otro Nombre',
            'email' => $teacher->user->email,
            'roles' => [RoleName::Teacher->value],
        ])
        ->assertSessionHasNoErrors();
});

test('UC-018 A1 A5: changing the email to one of another user is refused', function () {
    $teacher = Teacher::factory()->create();
    $other = User::factory()->create();

    $this->actingAs(administrator())
        ->put(route('accounts.update', $teacher->user), [
            'name' => $teacher->user->name,
            'email' => strtoupper($other->email),
            'roles' => [RoleName::Teacher->value],
        ])
        ->assertSessionHasErrors(['email' => 'El correo electrónico ya pertenece a otro usuario.']);
});

test('UC-018 A1 A6: an account cannot be left without a role', function () {
    $teacher = Teacher::factory()->create();

    $this->actingAs(administrator())
        ->put(route('accounts.update', $teacher->user), [
            'name' => $teacher->user->name,
            'email' => $teacher->user->email,
            'roles' => [],
        ])
        ->assertSessionHasErrors('roles');

    expect($teacher->user->fresh()->hasRole(RoleName::Teacher))->toBeTrue();
});

test('UC-018 A1 A7: giving the career director role while another is active is refused', function () {
    $current = Teacher::factory()->careerDirector()->create();
    $teacher = Teacher::factory()->create();

    $this->actingAs(administrator())
        ->put(route('accounts.update', $teacher->user), [
            'name' => $teacher->user->name,
            'email' => $teacher->user->email,
            'roles' => [RoleName::CareerDirector->value],
        ])
        ->assertSessionHasErrors(['roles' => "Ya hay un director de carrera activo: {$current->user->name}."]);

    expect($teacher->user->fresh()->hasRole(RoleName::CareerDirector))->toBeFalse();
});

test('UC-018 BR-002: the director keeps being a teacher and the tutored processes when changing roles', function () {
    $director = Teacher::factory()->careerDirector()->create();
    $process = GraduationProcess::factory()->withTutor($director)->create();

    $this->actingAs(administrator())
        ->put(route('accounts.update', $director->user), [
            'name' => $director->user->name,
            'email' => $director->user->email,
            'roles' => [RoleName::Teacher->value],
        ])
        ->assertSessionHasNoErrors();

    expect($director->user->fresh()->hasRole(RoleName::CareerDirector))->toBeFalse()
        ->and($director->user->fresh()->hasRole(RoleName::Teacher))->toBeTrue()
        ->and($process->fresh()->tutor_id)->toBe($director->id);
});

test('UC-018 A1 OQ-038: removing the teacher role leaves the teacher record inactive and giving it back reuses it', function () {
    $teacher = Teacher::factory()->create();
    $roles = fn (array $roles) => $this->actingAs(administrator())->put(route('accounts.update', $teacher->user), [
        'name' => $teacher->user->name,
        'email' => $teacher->user->email,
        'roles' => $roles,
    ]);

    $roles([RoleName::AdministrativeAssistant->value])->assertSessionHasNoErrors();

    expect($teacher->fresh()->is_active)->toBeFalse()
        ->and($teacher->user->fresh()->hasRole(RoleName::Teacher))->toBeFalse();

    $roles([RoleName::Teacher->value])->assertSessionHasNoErrors();

    expect($teacher->fresh()->is_active)->toBeTrue()
        ->and(Teacher::query()->where('user_id', $teacher->user_id)->count())->toBe(1);
});

test('UC-018 A1 OQ-038: giving the teacher role to an account that never had it creates an active teacher', function () {
    $assistant = User::factory()->administrativeAssistant()->create();

    $this->actingAs(administrator())->put(route('accounts.update', $assistant), [
        'name' => $assistant->name,
        'email' => $assistant->email,
        'roles' => [RoleName::AdministrativeAssistant->value, RoleName::Teacher->value],
    ]);

    expect($assistant->fresh()->teacher->is_active)->toBeTrue();
});

test('UC-018 A2 step 3: deactivating an account marks it and its teacher inactive without deleting it', function () {
    $teacher = Teacher::factory()->create();

    $this->actingAs(administrator())
        ->patch(route('accounts.deactivate', $teacher->user))
        ->assertRedirect(route('accounts.index'))
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Cuenta desactivada.']);

    expect($teacher->user->fresh()->is_active)->toBeFalse()
        ->and($teacher->fresh()->is_active)->toBeFalse();
});

test('UC-018 A2: a deactivated account can no longer log in', function () {
    $teacher = Teacher::factory()->create();

    $this->actingAs(administrator())->patch(route('accounts.deactivate', $teacher->user));
    auth()->logout();

    $this->post(route('login.store'), ['email' => $teacher->user->email, 'password' => 'password'])
        ->assertSessionHasErrors();
    $this->assertGuest();
});

test('UC-018 A2: an assistant account without teacher record is deactivated', function () {
    $assistant = User::factory()->administrativeAssistant()->create();

    $this->actingAs(administrator())->patch(route('accounts.deactivate', $assistant))->assertSessionHasNoErrors();

    expect($assistant->fresh()->is_active)->toBeFalse();
});

test('UC-018 A3 BR-003: an account whose teacher tutors a process in progress is not deactivated', function () {
    $teacher = Teacher::factory()->create();
    $process = GraduationProcess::factory()->withTutor($teacher)->create();

    $this->actingAs(administrator())
        ->patch(route('accounts.deactivate', $teacher->user))
        ->assertRedirect(route('accounts.index'))
        ->assertSessionHas('blocked', [
            'message' => 'No se puede desactivar la cuenta ni quitarle el rol docente. El director de carrera debe reasignar antes:',
            'assignments' => [['student' => $process->student->user->name, 'role' => 'tutor']],
        ]);

    expect($teacher->user->fresh()->is_active)->toBeTrue()
        ->and($teacher->fresh()->is_active)->toBeTrue();
});

test('UC-018 A3 BR-003: the blocked list is shown on the page after the redirect', function () {
    $teacher = Teacher::factory()->create();
    $process = GraduationProcess::factory()->withTutor($teacher)->create();
    $administrator = administrator();

    $this->actingAs($administrator)->patch(route('accounts.deactivate', $teacher->user));

    $this->actingAs($administrator)
        ->get(route('accounts.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('blocked.assignments.0.student', $process->student->user->name)
            ->where('blocked.assignments.0.role', 'tutor'));
});

test('UC-018 A3 BR-003: a current committee member of a process in progress is not deactivated', function () {
    $process = GraduationProcess::factory()->withCommittee()->create();
    $member = $process->currentCommitteeMembers()->first()->teacher;

    $this->actingAs(administrator())
        ->patch(route('accounts.deactivate', $member->user))
        ->assertSessionHas('blocked.assignments', [
            ['student' => $process->student->user->name, 'role' => 'miembro del tribunal'],
        ]);

    expect($member->user->fresh()->is_active)->toBeTrue();
});

test('UC-018 A3 BR-003: the workshop teacher is not deactivated', function () {
    $workshopTeacher = Teacher::factory()->workshopTeacher()->create();

    $this->actingAs(administrator())
        ->patch(route('accounts.deactivate', $workshopTeacher->user))
        ->assertSessionHas('blocked.assignments', [['student' => null, 'role' => 'docente de taller de grado']]);

    expect($workshopTeacher->user->fresh()->is_active)->toBeTrue();
});

test('UC-018 A3 BR-003: every assignment is listed with its process and role', function () {
    $teacher = Teacher::factory()->workshopTeacher()->create();
    $tutored = GraduationProcess::factory()->withTutor($teacher)->create();
    $committee = GraduationProcess::factory()->withCommittee()->create();
    CommitteeMember::factory()->for($committee)->create(['teacher_id' => $teacher->id]);

    $this->actingAs(administrator())
        ->patch(route('accounts.deactivate', $teacher->user))
        ->assertSessionHas('blocked.assignments', [
            ['student' => $tutored->student->user->name, 'role' => 'tutor'],
            ['student' => $committee->student->user->name, 'role' => 'miembro del tribunal'],
            ['student' => null, 'role' => 'docente de taller de grado'],
        ]);
});

test('UC-018 BR-003: processes concluded or abandoned and replaced members do not block the deactivation', function () {
    $tutor = Teacher::factory()->create();
    GraduationProcess::factory()->withTutor($tutor)->approved()->create();
    $replaced = Teacher::factory()->create();
    $process = GraduationProcess::factory()->withCommittee()->create();
    CommitteeMember::factory()->replaced()->for($process)->create(['teacher_id' => $replaced->id]);
    $abandonedTutor = Teacher::factory()->create();
    GraduationProcess::factory()->abandoned()->create(['tutor_id' => $abandonedTutor->id]);

    foreach ([$tutor, $replaced, $abandonedTutor] as $teacher) {
        $this->actingAs(administrator())
            ->patch(route('accounts.deactivate', $teacher->user))
            ->assertSessionMissing('blocked');

        expect($teacher->user->fresh()->is_active)->toBeFalse();
    }
});

test('UC-018 A3 A1 BR-003: the teacher role is not removed from a tutor of a process in progress', function () {
    $teacher = Teacher::factory()->create();
    GraduationProcess::factory()->withTutor($teacher)->create();

    $this->actingAs(administrator())
        ->put(route('accounts.update', $teacher->user), [
            'name' => 'Nombre Cambiado',
            'email' => $teacher->user->email,
            'roles' => [RoleName::AdministrativeAssistant->value],
        ])
        ->assertSessionHas('blocked.assignments.0.role', 'tutor');

    expect($teacher->user->fresh()->hasRole(RoleName::Teacher))->toBeTrue()
        ->and($teacher->user->fresh()->name)->not->toBe('Nombre Cambiado')
        ->and($teacher->fresh()->is_active)->toBeTrue();
});

test('UC-018 A4: reactivating an account marks it and its teacher active', function () {
    $teacher = Teacher::factory()->inactive()->create();

    $this->actingAs(administrator())
        ->patch(route('accounts.reactivate', $teacher->user))
        ->assertRedirect(route('accounts.index'))
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Cuenta reactivada.']);

    expect($teacher->user->fresh()->is_active)->toBeTrue()
        ->and($teacher->fresh()->is_active)->toBeTrue();
});

test('UC-018 A4 OQ-037: an inactive account that keeps the director role is not reactivated while another is active', function () {
    $former = Teacher::factory()->careerDirector()->inactive()->create();
    $former->user->roles()->syncWithoutDetaching(
        Role::query()->where('name', RoleName::CareerDirector)->pluck('id'),
    );
    $current = Teacher::factory()->careerDirector()->create();

    $this->actingAs(administrator())
        ->patch(route('accounts.reactivate', $former->user))
        ->assertSessionHasErrors(['account' => "Ya hay un director de carrera activo: {$current->user->name}."]);

    expect($former->user->fresh()->is_active)->toBeFalse();
});

test('UC-018 A4 OQ-037: the former director is reactivated after losing the director role', function () {
    $former = Teacher::factory()->careerDirector()->inactive()->create();
    Teacher::factory()->careerDirector()->create();

    $this->actingAs(administrator())->put(route('accounts.update', $former->user), [
        'name' => $former->user->name,
        'email' => $former->user->email,
        'roles' => [RoleName::Teacher->value],
    ])->assertSessionHasNoErrors();

    $this->actingAs(administrator())->patch(route('accounts.reactivate', $former->user))->assertSessionHasNoErrors();

    expect($former->user->fresh()->is_active)->toBeTrue();
});

test('UC-018 BR-004: there is no way to delete an account', function () {
    $teacher = Teacher::factory()->create();

    $this->actingAs(administrator())->delete('/cuentas/'.$teacher->user_id)->assertMethodNotAllowed();

    expect(collect(Route::getRoutes()->getRoutes())->filter(
        fn ($route) => str_starts_with($route->getName() ?? '', 'accounts.') && in_array('DELETE', $route->methods(), true),
    ))->toBeEmpty()
        ->and(User::query()->whereKey($teacher->user_id)->exists())->toBeTrue();
});

test('UC-018 step 2: the administrator and students cannot be changed or deactivated through the page', function () {
    $student = User::factory()->withRoles(RoleName::Student)->create();
    $administrator = administrator();

    $this->actingAs($administrator)->patch(route('accounts.deactivate', $student))->assertForbidden();
    $this->actingAs($administrator)->patch(route('accounts.deactivate', $administrator))->assertForbidden();
    $this->actingAs($administrator)->put(route('accounts.update', $student), [
        'name' => 'X Y', 'email' => 'x@y.test', 'roles' => [RoleName::Teacher->value],
    ])->assertForbidden();

    expect($student->fresh()->is_active)->toBeTrue()->and($administrator->fresh()->is_active)->toBeTrue();
});

test('UC-018 A2 A4: an active account cannot be reactivated nor an inactive one deactivated', function () {
    $active = User::factory()->administrativeAssistant()->create();
    $inactive = User::factory()->administrativeAssistant()->inactive()->create();

    $this->actingAs(administrator())->patch(route('accounts.reactivate', $active))->assertForbidden();
    $this->actingAs(administrator())->patch(route('accounts.deactivate', $inactive))->assertForbidden();
});

test('UC-018 NFR-005: another role cannot change, deactivate nor reactivate an account', function () {
    $assistant = User::factory()->administrativeAssistant()->create();
    $active = Teacher::factory()->create();
    $inactive = Teacher::factory()->inactive()->create();

    $this->actingAs($assistant)->put(route('accounts.update', $active->user), [
        'name' => 'X Y', 'email' => 'x@y.test', 'roles' => [RoleName::Teacher->value],
    ])->assertForbidden();
    $this->actingAs($assistant)->patch(route('accounts.deactivate', $active->user))->assertForbidden();
    $this->actingAs($assistant)->patch(route('accounts.reactivate', $inactive->user))->assertForbidden();

    expect($active->user->fresh()->is_active)->toBeTrue()->and($inactive->user->fresh()->is_active)->toBeFalse();
});

test('UC-018 A7: granting the director role to an inactive account while another is active is refused', function () {
    $current = Teacher::factory()->careerDirector()->create();
    $inactive = Teacher::factory()->inactive()->create();

    $this->actingAs(administrator())
        ->put(route('accounts.update', $inactive->user), [
            'name' => $inactive->user->name,
            'email' => $inactive->user->email,
            'roles' => [RoleName::CareerDirector->value],
        ])
        ->assertSessionHasErrors(['roles' => "Ya hay un director de carrera activo: {$current->user->name}."]);

    expect($inactive->user->fresh()->hasRole(RoleName::CareerDirector))->toBeFalse();
});

test('UC-018 A7: renaming an inactive former director who keeps the role is not blocked', function () {
    $former = Teacher::factory()->careerDirector()->inactive()->create();
    $former->user->roles()->syncWithoutDetaching(Role::query()->where('name', RoleName::CareerDirector)->pluck('id'));
    Teacher::factory()->careerDirector()->create();

    $this->actingAs(administrator())
        ->put(route('accounts.update', $former->user), [
            'name' => 'Nombre Corregido',
            'email' => $former->user->email,
            'roles' => [RoleName::CareerDirector->value],
        ])
        ->assertSessionHasNoErrors();

    expect($former->user->fresh()->name)->toBe('Nombre Corregido');
});

test('UC-018 A2 step 1: the list exposes the blocking assignments of an account before any deactivation', function () {
    $blocked = Teacher::factory()->workshopTeacher()->create();
    $process = GraduationProcess::factory()->withTutor($blocked)->create();
    $free = Teacher::factory()->create();

    $this->actingAs(administrator())
        ->get(route('accounts.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('accounts', fn ($accounts) => collect($accounts)->firstWhere('id', $blocked->user_id)['blocking_assignments'] === [
                ['student' => $process->student->user->name, 'role' => 'tutor'],
                ['student' => null, 'role' => 'docente de taller de grado'],
            ] && collect($accounts)->firstWhere('id', $free->user_id)['blocking_assignments'] === []));

    expect($blocked->user->fresh()->is_active)->toBeTrue();
});

test('UC-018 A2 step 1: the list computes the blocking assignments in a bounded number of queries', function () {
    Teacher::factory()->count(6)->create()->each(
        fn (Teacher $teacher) => GraduationProcess::factory()->withTutor($teacher)->create(),
    );
    $administrator = administrator();

    DB::enableQueryLog();
    $this->actingAs($administrator)->get(route('accounts.index'))->assertOk();
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queries)->toBeLessThan(20);
});
