<?php

use App\Enums\Phase;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\Notification;
use App\Models\ProjectVersion;
use App\Models\Teacher;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->director = Teacher::factory()->careerDirector()->create();
    $this->directorUser = $this->director->user;
});

function inertiaProps($response): array
{
    return $response->viewData('page')['props'];
}

test('UC-002 step 1: the career director opens the tutor assignment page', function () {
    $this->actingAs($this->directorUser)
        ->get(route('tutor-assignments.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('tutor-assignments/index'));
});

test('UC-002 step 1: a user without the permission tutores.asignar cannot open it', function () {
    $this->actingAs(User::factory()->administrativeAssistant()->create())
        ->get(route('tutor-assignments.index'))
        ->assertForbidden();

    $process = GraduationProcess::factory()->create();
    $teacher = Teacher::factory()->create();

    $this->actingAs(User::factory()->administrativeAssistant()->create())
        ->put(route('tutor-assignments.update', $process), ['teacher_id' => $teacher->id])
        ->assertForbidden();

    expect($process->refresh()->tutor_id)->toBeNull();
});

test('UC-002 step 1: a guest is sent to log in', function () {
    $this->get(route('tutor-assignments.index'))->assertRedirect(route('login'));
});

test('UC-002 step 2: lists the processes in progress with student, phase and tutor, those without tutor first', function () {
    $withTutor = GraduationProcess::factory()->withTutor()->create(['started_at' => now()->subDays(10)]);
    $withoutTutor = GraduationProcess::factory()->create([
        'started_at' => now()->subDay(),
        'proposed_tutor_name' => 'Ing. Propuesto Ejemplo',
    ]);

    $response = $this->actingAs($this->directorUser)->get(route('tutor-assignments.index'));

    $processes = inertiaProps($response)['processes'];

    expect($processes)->toHaveCount(2)
        ->and($processes[0])->toBe([
            'id' => $withoutTutor->id,
            'student' => $withoutTutor->student->user->name,
            'phase' => 'Requisitos',
            'tutor' => null,
            'proposedTutor' => 'Ing. Propuesto Ejemplo',
        ])
        ->and($processes[1])->toBe([
            'id' => $withTutor->id,
            'student' => $withTutor->student->user->name,
            'phase' => 'Evaluación de perfil',
            'tutor' => $withTutor->tutor->user->name,
            'proposedTutor' => null,
        ]);
});

test('UC-002 step 2 BR-002: concluded and abandoned processes are not listed', function () {
    GraduationProcess::factory()->concluded()->create();
    GraduationProcess::factory()->abandoned()->create();
    $inProgress = GraduationProcess::factory()->create();

    $response = $this->actingAs($this->directorUser)->get(route('tutor-assignments.index'));

    expect(collect(inertiaProps($response)['processes'])->pluck('id')->all())->toBe([$inProgress->id]);
});

test('UC-002 A3: with no process in progress the list is empty', function () {
    GraduationProcess::factory()->concluded()->create();

    $response = $this->actingAs($this->directorUser)->get(route('tutor-assignments.index'));

    expect(inertiaProps($response)['processes'])->toBeEmpty();
});

test('UC-002 step 4 BR-001: shows the eligible teachers and the proposed tutor name of the process', function () {
    $process = GraduationProcess::factory()->create(['proposed_tutor_name' => 'Ing. Propuesto Ejemplo']);
    $eligible = Teacher::factory()->create();
    $inactive = Teacher::factory()->inactive()->create();
    $workshop = Teacher::factory()->workshopTeacher()->create();
    $member = Teacher::factory()->create();
    CommitteeMember::factory()->for($process)->for($member)->create();
    $replaced = Teacher::factory()->create();
    CommitteeMember::factory()->for($process)->for($replaced)->replaced()->create();

    $response = $this->actingAs($this->directorUser)->get(route('tutor-assignments.edit', $process));

    $response->assertOk()->assertInertia(fn ($page) => $page->component('tutor-assignments/edit'));
    $props = inertiaProps($response);
    $ids = collect($props['teachers'])->pluck('id');

    expect($props['process']['proposedTutor'])->toBe('Ing. Propuesto Ejemplo')
        ->and($ids)->toContain($eligible->id)
        ->and($ids)->not->toContain($inactive->id)
        ->and($ids)->not->toContain($workshop->id)
        ->and($ids)->not->toContain($member->id);
});

test('UC-002 BR-001 OQ-041: a replaced committee member and the career director are eligible', function () {
    $process = GraduationProcess::factory()->create();
    $replaced = Teacher::factory()->create();
    CommitteeMember::factory()->for($process)->for($replaced)->replaced()->create();

    $response = $this->actingAs($this->directorUser)->get(route('tutor-assignments.edit', $process));

    $ids = collect(inertiaProps($response)['teachers'])->pluck('id');
    expect($ids)->toContain($replaced->id)->toContain($this->director->id);
});

test('UC-002 A1 step 1: on a process with tutor shows the current tutor and excludes it from the eligible teachers', function () {
    $process = GraduationProcess::factory()->withTutor()->create();

    $response = $this->actingAs($this->directorUser)->get(route('tutor-assignments.edit', $process));

    $props = inertiaProps($response);
    expect($props['process']['tutor'])->toBe($process->tutor->user->name)
        ->and(collect($props['teachers'])->pluck('id'))->not->toContain($process->tutor_id);
});

test('UC-002 BR-002: a concluded or abandoned process cannot be edited nor updated', function (string $state) {
    $process = GraduationProcess::factory()->{$state}()->create();
    $teacher = Teacher::factory()->create();
    $tutorId = $process->tutor_id;

    $this->actingAs($this->directorUser)->get(route('tutor-assignments.edit', $process))->assertNotFound();
    $this->actingAs($this->directorUser)
        ->put(route('tutor-assignments.update', $process), ['teacher_id' => $teacher->id])
        ->assertNotFound();

    expect($process->refresh()->tutor_id)->toBe($tutorId);
})->with(['concluded', 'abandoned']);

test('UC-002 steps 7-9 BR-003: assigns the tutor, moves the process to profile evaluation and confirms', function () {
    $process = GraduationProcess::factory()->create();
    $tutor = Teacher::factory()->create();

    $this->actingAs($this->directorUser)
        ->put(route('tutor-assignments.update', $process), ['teacher_id' => $tutor->id])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('tutor-assignments.index'))
        ->assertInertiaFlash('toast', [
            'type' => 'success',
            'message' => "Tutor asignado: {$tutor->user->name}. Fase: Evaluación de perfil.",
        ]);

    $process->refresh();
    $change = $process->phaseChanges()->get()->last();
    expect($process->tutor_id)->toBe($tutor->id)
        ->and($process->phase)->toBe(Phase::ProfileEvaluation)
        ->and($change->phase)->toBe(Phase::ProfileEvaluation)
        ->and($change->changed_by)->toBe($this->directorUser->id);
});

test('UC-002 step 8: notifies the tutor and the student', function () {
    $process = GraduationProcess::factory()->create();
    $tutor = Teacher::factory()->create();
    $student = $process->student->user;

    $this->actingAs($this->directorUser)
        ->put(route('tutor-assignments.update', $process), ['teacher_id' => $tutor->id]);

    $forTutor = Notification::query()->where('user_id', $tutor->user_id)->sole();
    $forStudent = Notification::query()->where('user_id', $student->id)->sole();

    expect($forTutor->message)->toBe("Se le asignó como tutor del trámite de titulación de {$student->name}.")
        ->and($forTutor->graduation_process_id)->toBe($process->id)
        ->and($forStudent->message)->toBe("Su trámite de titulación pasó a la fase evaluación de perfil con {$tutor->user->name} como tutor.");
});

test('UC-002 step 6 BR-001: an ineligible teacher is rejected and nothing changes', function (string $kind) {
    $process = GraduationProcess::factory()->create();
    $teacher = match ($kind) {
        'inactive' => Teacher::factory()->inactive()->create(),
        'workshop' => Teacher::factory()->workshopTeacher()->create(),
        'committee' => tap(Teacher::factory()->create(), fn ($teacher) => CommitteeMember::factory()->for($process)->for($teacher)->create()),
    };

    $this->actingAs($this->directorUser)
        ->put(route('tutor-assignments.update', $process), ['teacher_id' => $teacher->id])
        ->assertSessionHasErrors(['teacher_id' => 'El docente elegido no es elegible como tutor de este trámite de titulación.']);

    expect($process->refresh()->tutor_id)->toBeNull()
        ->and($process->phase)->toBe(Phase::Requirements)
        ->and(Notification::query()->count())->toBe(0);
})->with(['inactive', 'workshop', 'committee']);

test('UC-002 BR-001: the current tutor cannot be chosen again', function () {
    $process = GraduationProcess::factory()->withTutor()->create();

    $this->actingAs($this->directorUser)
        ->put(route('tutor-assignments.update', $process), ['teacher_id' => $process->tutor_id])
        ->assertSessionHasErrors(['teacher_id' => 'El docente elegido no es elegible como tutor de este trámite de titulación.']);

    expect(Notification::query()->count())->toBe(0);
});

test('UC-002 step 6 NFR-009: the teacher is required, an integer and must exist, with Spanish messages', function (mixed $value, string $message) {
    $process = GraduationProcess::factory()->create();

    $this->actingAs($this->directorUser)
        ->put(route('tutor-assignments.update', $process), ['teacher_id' => $value])
        ->assertSessionHasErrors(['teacher_id' => $message]);

    expect($process->refresh()->tutor_id)->toBeNull();
})->with([
    'missing' => [null, 'El campo docente es obligatorio.'],
    'unknown' => [999999, 'El valor seleccionado para docente no es válido.'],
    'not a number' => ['abc', 'El campo docente debe ser un número entero.'],
]);

test('UC-002 A2: opening the assignment page without confirming changes nothing', function () {
    $process = GraduationProcess::factory()->create();
    $withTutor = GraduationProcess::factory()->withTutor()->create();
    $phaseChanges = $process->phaseChanges()->count() + $withTutor->phaseChanges()->count();

    $this->actingAs($this->directorUser)->get(route('tutor-assignments.edit', $process))->assertOk();
    $this->actingAs($this->directorUser)->get(route('tutor-assignments.edit', $withTutor))->assertOk();

    $tutorId = $withTutor->tutor_id;
    expect($process->refresh()->tutor_id)->toBeNull()
        ->and($process->phase)->toBe(Phase::Requirements)
        ->and($withTutor->refresh()->tutor_id)->toBe($tutorId)
        ->and($withTutor->phase)->toBe(Phase::ProfileEvaluation)
        ->and($process->phaseChanges()->count() + $withTutor->phaseChanges()->count())->toBe($phaseChanges)
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-002 A1 BR-003 BR-005 Post-S-4: changes the tutor keeping phase, defenses and project versions (observations have no table yet), notifies the new tutor and the student only', function () {
    $previous = Teacher::factory()->create();
    $process = GraduationProcess::factory()->withTutor($previous)->withCommittee()->create();
    $versions = collect([
        ProjectVersion::factory()->profileRejected($previous)->for($process)->create(['number' => 1]),
        ProjectVersion::factory()->profileInReview($previous)->for($process)->create(['number' => 2]),
    ]);
    $versionsBefore = ProjectVersion::query()->where('graduation_process_id', $process->id)->orderBy('id')->get()->toArray();
    $defense = Defense::factory()->for($process)->create(['scheduled_at' => CarbonImmutable::parse('2026-12-10 10:00')]);
    $phaseChanges = $process->phaseChanges()->count();
    $newTutor = Teacher::factory()->create();
    $student = $process->student->user;

    $this->actingAs($this->directorUser)
        ->put(route('tutor-assignments.update', $process), ['teacher_id' => $newTutor->id])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('tutor-assignments.index'))
        ->assertInertiaFlash('toast', [
            'type' => 'success',
            'message' => "Tutor asignado: {$newTutor->user->name}. Fase: Pre-defensa.",
        ]);

    $process->refresh();
    expect($process->tutor_id)->toBe($newTutor->id)
        ->and($process->phase)->toBe(Phase::PreDefense)
        ->and($process->phaseChanges()->count())->toBe($phaseChanges)
        ->and($defense->refresh()->scheduled_at->toDateTimeString())->toBe('2026-12-10 10:00:00')
        ->and(ProjectVersion::query()->where('graduation_process_id', $process->id)->orderBy('id')->get()->toArray())
        ->toBe($versionsBefore)
        ->and($versions)->toHaveCount(2);

    expect(Notification::query()->where('user_id', $newTutor->user_id)->sole()->message)
        ->toBe("Se le asignó como tutor del trámite de titulación de {$student->name}.")
        ->and(Notification::query()->where('user_id', $student->id)->sole()->message)
        ->toBe("Su trámite de titulación tiene un nuevo tutor: {$newTutor->user->name}.")
        ->and(Notification::query()->where('user_id', $previous->user_id)->exists())->toBeFalse();
});

test('UC-002 A4 BR-006: a new tutor with an overlapping act is refused and nothing changes', function () {
    $previous = Teacher::factory()->create();
    $process = GraduationProcess::factory()->withTutor($previous)->withCommittee()->create();
    Defense::factory()->for($process)->create(['scheduled_at' => CarbonImmutable::parse('2026-12-10 10:00')]);

    $newTutor = Teacher::factory()->create();
    $other = GraduationProcess::factory()->withTutor($newTutor)->withCommittee()->create();
    Defense::factory()->for($other)->formalDefense()->create(['scheduled_at' => CarbonImmutable::parse('2026-12-10 11:00')]);

    $this->actingAs($this->directorUser)
        ->put(route('tutor-assignments.update', $process), ['teacher_id' => $newTutor->id])
        ->assertSessionHasErrors([
            'teacher_id' => "{$newTutor->user->name} tiene la defensa formal de {$other->student->user->name} el 10/12/2026 11:00, "
                .'que se cruza con la pre-defensa de este trámite. Reprograme antes el acto o elija otro docente.',
        ]);

    expect($process->refresh()->tutor_id)->toBe($previous->id)
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-002 A1 BR-006: a new tutor whose acts do not overlap keeps the schedule', function () {
    $process = GraduationProcess::factory()->withTutor()->withCommittee()->create();
    Defense::factory()->for($process)->create(['scheduled_at' => CarbonImmutable::parse('2026-12-10 10:00')]);

    $newTutor = Teacher::factory()->create();
    $other = GraduationProcess::factory()->withTutor($newTutor)->withCommittee()->create();
    Defense::factory()->for($other)->create(['scheduled_at' => CarbonImmutable::parse('2026-12-10 12:00')]);

    $this->actingAs($this->directorUser)
        ->put(route('tutor-assignments.update', $process), ['teacher_id' => $newTutor->id])
        ->assertSessionHasNoErrors();

    expect($process->refresh()->tutor_id)->toBe($newTutor->id)
        ->and($process->defenses()->count())->toBe(1);
});
