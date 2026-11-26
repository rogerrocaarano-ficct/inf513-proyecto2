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

/**
 * A process in profile evaluation with an approved project profile, as UC-005 leaves it.
 */
function processWithApprovedProfile(): GraduationProcess
{
    $process = GraduationProcess::factory()->inPhase(Phase::ProfileEvaluation)->create([
        'project_title' => 'Sistema de gestión de titulación',
    ]);
    ProjectVersion::factory()->for($process)->profileApproved()->create();

    return $process;
}

beforeEach(function () {
    $this->director = Teacher::factory()->careerDirector()->create();
    $this->directorUser = $this->director->user;
    $this->process = processWithApprovedProfile();
    $this->teachers = Teacher::factory()->count(4)->create();
});

test('UC-006 NFR-005: a user without the permission cannot open the page and a guest is sent to login', function () {
    $teacher = Teacher::factory()->create();

    $this->get(route('committees.index'))->assertRedirect(route('login'));
    $this->actingAs($teacher->user)->get(route('committees.index'))->assertForbidden();
    $this->actingAs($teacher->user)
        ->post(route('committees.store', $this->process), ['teacher_ids' => $this->teachers->take(3)->modelKeys()])
        ->assertForbidden();
});

test('UC-006 step 2: the list shows processes in progress with approved profile, those without committee first', function () {
    $withCommittee = GraduationProcess::factory()->withCommittee()->create();
    ProjectVersion::factory()->for($withCommittee)->profileApproved()->create();
    GraduationProcess::factory()->inPhase(Phase::ProfileEvaluation)->create();
    $notApproved = GraduationProcess::factory()->inPhase(Phase::ProfileEvaluation)->create();
    ProjectVersion::factory()->for($notApproved)->profileInReview()->create();
    $abandoned = GraduationProcess::factory()->abandoned()->create();
    ProjectVersion::factory()->for($abandoned)->profileApproved()->create();

    $this->actingAs($this->directorUser)
        ->get(route('committees.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('committees/index')
            ->has('processes', 2)
            ->where('processes.0.id', $this->process->id)
            ->where('processes.0.student', $this->process->student->user->name)
            ->where('processes.0.project_title', 'Sistema de gestión de titulación')
            ->where('processes.0.tutor', $this->process->tutor->user->name)
            ->where('processes.0.committee', [])
            ->where('processes.1.id', $withCommittee->id)
            ->has('processes.1.committee', 3));
});

test('UC-006 step 4 BR-001: the eligible teachers exclude the director, the tutor, inactive and former members', function () {
    $workshopTeacher = Teacher::query()->workshopTeacher()->firstOrFail();
    $inactive = Teacher::factory()->inactive()->create();
    $former = Teacher::factory()->create();
    CommitteeMember::factory()->for($this->process)->for($former)->replaced()->create();

    $this->actingAs($this->directorUser)
        ->get(route('committees.edit', $this->process))
        ->assertOk()
        ->assertInertia(function ($page) use ($workshopTeacher, $inactive, $former) {
            $page->component('committees/edit')->has('members', 0);
            $ids = collect($page->toArray()['props']['eligibleTeachers'])->pluck('id');

            expect($ids)->toContain($workshopTeacher->id, ...$this->teachers->modelKeys())
                ->not->toContain($this->director->id, $this->process->tutor_id, $inactive->id, $former->id);
        });
});

test('UC-006 steps 6 to 10: three teachers are assigned, the phase advances and everyone is notified', function () {
    $chosen = $this->teachers->take(3)->values();
    $student = $this->process->student->user;
    [$one, $two, $three] = $chosen->map(fn (Teacher $teacher) => $teacher->user->name)->all();

    $this->actingAs($this->directorUser)
        ->post(route('committees.store', $this->process), ['teacher_ids' => $chosen->modelKeys()])
        ->assertRedirect(route('committees.index'));

    $members = $this->process->currentCommitteeMembers()->get();
    expect($members->pluck('teacher_id')->all())->toEqualCanonicalizing($chosen->modelKeys())
        ->and($members->every(fn (CommitteeMember $member) => $member->assigned_at !== null))->toBeTrue()
        ->and($this->process->refresh()->phase)->toBe(Phase::PreDefense);

    $change = $this->process->phaseChanges()->get()->last();
    expect($change->phase)->toBe(Phase::PreDefense)
        ->and($change->changed_by)->toBe($this->directorUser->id);

    foreach ($chosen as $teacher) {
        expect(Notification::query()->where('user_id', $teacher->user_id)->value('message'))
            ->toBe("Se le asignó como miembro del tribunal evaluador del trámite de titulación de {$student->name}.");
    }
    expect(Notification::query()->where('user_id', $student->id)->value('message'))
        ->toBe("Su trámite de titulación pasó a la fase pre-defensa. Su tribunal evaluador: {$one}, {$two} y {$three}.");

    $this->get(route('committees.index'))->assertInertia(fn ($page) => $page->hasFlash('toast', [
        'type' => 'success',
        'message' => "Tribunal evaluador asignado: {$one}, {$two} y {$three}. Fase: pre-defensa.",
    ]));
});

test('UC-006 A1 BR-002: fewer or more than three teachers, or repeated ones, are refused and nothing changes', function (array $count) {
    $ids = $this->teachers->modelKeys();
    $choice = array_map(fn (int $position) => $ids[$position], $count);

    $this->actingAs($this->directorUser)
        ->from(route('committees.edit', $this->process))
        ->post(route('committees.store', $this->process), ['teacher_ids' => $choice])
        ->assertRedirect(route('committees.edit', $this->process))
        ->assertSessionHasErrors(['teacher_ids' => 'El tribunal evaluador se compone de exactamente tres docentes distintos.']);

    expect($this->process->committeeMembers()->count())->toBe(0)
        ->and($this->process->refresh()->phase)->toBe(Phase::ProfileEvaluation)
        ->and(Notification::query()->count())->toBe(0);
})->with([
    'two teachers' => [[0, 1]],
    'four teachers' => [[0, 1, 2, 3]],
    'a repeated teacher' => [[0, 1, 1]],
    'none' => [[]],
]);

test('UC-006 A1: after the error the director corrects the choice and the assignment succeeds', function () {
    $this->actingAs($this->directorUser)
        ->post(route('committees.store', $this->process), ['teacher_ids' => $this->teachers->take(2)->modelKeys()])
        ->assertSessionHasErrors('teacher_ids');

    $this->post(route('committees.store', $this->process), ['teacher_ids' => $this->teachers->take(3)->modelKeys()])
        ->assertRedirect(route('committees.index'))
        ->assertSessionHasNoErrors();

    expect($this->process->currentCommitteeMembers()->count())->toBe(3);
});

test('UC-006 BR-001: a chosen teacher who is the tutor, the director or inactive is refused', function (string $kind) {
    $ineligible = match ($kind) {
        'tutor' => $this->process->tutor,
        'director' => $this->director,
        'inactive' => Teacher::factory()->inactive()->create(),
    };
    $baseMembers = $this->process->committeeMembers()->count();

    $this->actingAs($this->directorUser)
        ->post(route('committees.store', $this->process), [
            'teacher_ids' => [$ineligible->id, ...$this->teachers->take(2)->modelKeys()],
        ])
        ->assertSessionHasErrors('teacher_ids');

    expect($this->process->committeeMembers()->count())->toBe($baseMembers)
        ->and($this->process->refresh()->phase)->toBe(Phase::ProfileEvaluation);
})->with(['tutor', 'director', 'inactive']);

test('UC-006 BR-001: the workshop teacher can be a committee member', function () {
    $workshopTeacher = Teacher::query()->workshopTeacher()->firstOrFail();

    $this->actingAs($this->directorUser)
        ->post(route('committees.store', $this->process), [
            'teacher_ids' => [$workshopTeacher->id, ...$this->teachers->take(2)->modelKeys()],
        ])
        ->assertSessionHasNoErrors();

    expect($this->process->currentCommitteeMembers()->pluck('teacher_id'))->toContain($workshopTeacher->id);
});

test('UC-006 BR-003: a committee is assigned only to a process in progress in profile evaluation with approved profile', function (string $case) {
    $process = match ($case) {
        'profile not approved' => tap(GraduationProcess::factory()->inPhase(Phase::ProfileEvaluation)->create(),
            fn ($p) => ProjectVersion::factory()->for($p)->profileInReview()->create()),
        'other phase' => tap(GraduationProcess::factory()->inPhase(Phase::PreDefense)->create(),
            fn ($p) => ProjectVersion::factory()->for($p)->profileApproved()->create()),
        'abandoned' => tap(GraduationProcess::factory()->inPhase(Phase::ProfileEvaluation)->create(['abandoned_at' => now()]),
            fn ($p) => ProjectVersion::factory()->for($p)->profileApproved()->create()),
    };

    $this->actingAs($this->directorUser)
        ->post(route('committees.store', $process), ['teacher_ids' => $this->teachers->take(3)->modelKeys()])
        ->assertSessionHasErrors('graduation_process');

    $phase = $process->phase;

    expect($process->committeeMembers()->count())->toBe(0)
        ->and($process->refresh()->phase)->toBe($phase);
})->with(['profile not approved', 'other phase', 'abandoned']);

test('UC-006 A4: opening the assignment page and not confirming changes nothing', function () {
    $this->actingAs($this->directorUser)->get(route('committees.edit', $this->process))->assertOk();

    expect($this->process->committeeMembers()->count())->toBe(0)
        ->and($this->process->refresh()->phase)->toBe(Phase::ProfileEvaluation)
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-006 step 8: a failure while recording leaves no member, phase change or notification', function () {
    $chosen = $this->teachers->take(3)->modelKeys();
    Notification::creating(fn () => throw new RuntimeException('fallo'));

    $this->withoutExceptionHandling();
    expect(fn () => $this->actingAs($this->directorUser)
        ->post(route('committees.store', $this->process), ['teacher_ids' => $chosen]))
        ->toThrow(RuntimeException::class);

    expect($this->process->committeeMembers()->count())->toBe(0)
        ->and($this->process->refresh()->phase)->toBe(Phase::ProfileEvaluation)
        ->and($this->process->phaseChanges()->count())->toBe(1);
});

describe('replacement of a member', function () {
    beforeEach(function () {
        $this->replacedProcess = GraduationProcess::factory()->withCommittee()->create();
        ProjectVersion::factory()->for($this->replacedProcess)->profileApproved()->create();
        $this->members = $this->replacedProcess->currentCommitteeMembers()->with('teacher.user')->get();
        $this->newTeacher = $this->teachers->first();
    });

    test('UC-006 A2: the page shows the current members and the eligible teachers', function () {
        $this->actingAs($this->directorUser)
            ->get(route('committees.edit', $this->replacedProcess))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('committees/edit')
                ->has('members', 3)
                ->where('members.0.name', $this->members[0]->teacher->user->name)
                ->where('eligibleTeachers', fn ($teachers) => collect($teachers)->pluck('id')->contains($this->newTeacher->id)
                    && ! collect($teachers)->pluck('id')->contains($this->members[0]->teacher_id)));
    });

    test('UC-006 A2 BR-004: one member is replaced, phase and acts are kept and the three are notified', function () {
        $defense = Defense::factory()->for($this->replacedProcess)->create(['scheduled_at' => now()->addWeek()]);
        $old = $this->members[0];
        $student = $this->replacedProcess->student->user;

        $this->actingAs($this->directorUser)
            ->put(route('committees.replace', $this->replacedProcess), [
                'committee_member_id' => $old->id,
                'teacher_id' => $this->newTeacher->id,
            ])
            ->assertRedirect(route('committees.index'));

        expect($old->refresh()->replaced_at)->not->toBeNull()
            ->and($this->replacedProcess->currentCommitteeMembers()->count())->toBe(3)
            ->and($this->replacedProcess->currentCommitteeMembers()->pluck('teacher_id'))->toContain($this->newTeacher->id)
            ->and($this->replacedProcess->currentCommitteeMembers()->pluck('teacher_id'))->not->toContain($old->teacher_id)
            ->and($this->replacedProcess->refresh()->phase)->toBe(Phase::PreDefense)
            ->and($this->replacedProcess->phaseChanges()->count())->toBe(1)
            ->and($defense->refresh()->scheduled_at->isFuture())->toBeTrue();

        expect(Notification::query()->where('user_id', $this->newTeacher->user_id)->value('message'))
            ->toBe("Se le asignó como miembro del tribunal evaluador del trámite de titulación de {$student->name}.")
            ->and(Notification::query()->where('user_id', $old->teacher->user_id)->value('message'))
            ->toBe("Dejó de ser miembro del tribunal evaluador del trámite de titulación de {$student->name}.")
            ->and(Notification::query()->where('user_id', $student->id)->value('message'))
            ->toBe("Su tribunal evaluador tiene un nuevo miembro: {$this->newTeacher->user->name}, en lugar de {$old->teacher->user->name}.");

        $names = $this->replacedProcess->currentCommitteeMembers()->with('teacher.user')->orderBy('id')->get()
            ->map(fn ($member) => $member->teacher->user->name);
        $this->get(route('committees.index'))->assertInertia(fn ($page) => $page->hasFlash('toast', [
            'type' => 'success',
            'message' => "Tribunal evaluador asignado: {$names[0]}, {$names[1]} y {$names[2]}. Fase: pre-defensa.",
        ]));
    });

    test('UC-006 A3 BR-004: once the formal defense was held the committee cannot change', function (string $outcome) {
        $process = GraduationProcess::factory()->{$outcome}()->create();
        ProjectVersion::factory()->for($process)->profileApproved()->create();
        CommitteeMember::factory()->count(3)->for($process)->create();
        $member = $process->currentCommitteeMembers()->first();
        $message = 'El tribunal evaluador ya no se puede cambiar porque la defensa formal se realizó.';

        $this->actingAs($this->directorUser)
            ->get(route('committees.edit', $process))
            ->assertRedirect(route('committees.index'));
        $this->get(route('committees.index'))->assertInertia(fn ($page) => $page->hasFlash('toast', [
            'type' => 'error',
            'message' => $message,
        ]));

        $this->put(route('committees.replace', $process), [
            'committee_member_id' => $member->id,
            'teacher_id' => $this->newTeacher->id,
        ])->assertSessionHasErrors(['graduation_process' => $message]);

        expect($member->refresh()->replaced_at)->toBeNull();
    })->with(['approved', 'failed']);

    test('UC-006 A5 BR-006: every overlapping act is reported, one sentence per act', function () {
        $at = CarbonImmutable::parse('2026-12-10 10:00');
        Defense::factory()->for($this->replacedProcess)->create(['scheduled_at' => $at]);
        $firstOther = GraduationProcess::factory()->withTutor($this->newTeacher)->inPhase(Phase::PreDefense)->create();
        Defense::factory()->for($firstOther)->create(['scheduled_at' => $at->addHour()]);
        $secondOther = GraduationProcess::factory()->inPhase(Phase::PreDefense)->create();
        CommitteeMember::factory()->for($secondOther)->for($this->newTeacher)->create();
        Defense::factory()->for($secondOther)->formalDefense()->create(['scheduled_at' => $at->subHour()]);
        $name = $this->newTeacher->user->name;

        $this->actingAs($this->directorUser)
            ->put(route('committees.replace', $this->replacedProcess), [
                'committee_member_id' => $this->members[0]->id,
                'teacher_id' => $this->newTeacher->id,
            ])
            ->assertSessionHasErrors(['teacher_id' => "{$name} tiene la defensa formal de {$secondOther->student->user->name} el 10/12/2026 05:00, que se cruza con la pre-defensa de este trámite. Reprograme antes el acto o elija otro docente. "
                ."{$name} tiene la pre-defensa de {$firstOther->student->user->name} el 10/12/2026 07:00, que se cruza con la pre-defensa de este trámite. Reprograme antes el acto o elija otro docente."]);

        expect($this->replacedProcess->currentCommitteeMembers()->pluck('teacher_id'))->not->toContain($this->newTeacher->id);
    });

    test('UC-006 A5 BR-006: a schedule conflict of the new teacher prevents the replacement', function () {
        $at = CarbonImmutable::parse('2026-12-10 10:00');
        $own = Defense::factory()->for($this->replacedProcess)->create(['scheduled_at' => $at]);
        $other = GraduationProcess::factory()->withTutor($this->newTeacher)->inPhase(Phase::PreDefense)->create();
        Defense::factory()->for($other)->formalDefense()->create(['scheduled_at' => $at->addMinutes(90)]);
        $old = $this->members[0];

        $this->actingAs($this->directorUser)
            ->put(route('committees.replace', $this->replacedProcess), [
                'committee_member_id' => $old->id,
                'teacher_id' => $this->newTeacher->id,
            ])
            ->assertSessionHasErrors(['teacher_id' => "{$this->newTeacher->user->name} tiene la defensa formal de {$other->student->user->name} el 10/12/2026 07:30, que se cruza con la pre-defensa de este trámite. Reprograme antes el acto o elija otro docente."]);

        expect($old->refresh()->replaced_at)->toBeNull()
            ->and($this->replacedProcess->currentCommitteeMembers()->count())->toBe(3)
            ->and($own->refresh()->scheduled_at->equalTo($at))->toBeTrue()
            ->and(Notification::query()->count())->toBe(0);
    });

    test('UC-006 A5: an act of the process that does not overlap allows the replacement and keeps the schedule', function () {
        $at = CarbonImmutable::parse('2026-12-10 10:00');
        Defense::factory()->for($this->replacedProcess)->create(['scheduled_at' => $at]);
        $other = GraduationProcess::factory()->withTutor($this->newTeacher)->inPhase(Phase::PreDefense)->create();
        Defense::factory()->for($other)->create(['scheduled_at' => $at->addHours(2)]);

        $this->actingAs($this->directorUser)
            ->put(route('committees.replace', $this->replacedProcess), [
                'committee_member_id' => $this->members[0]->id,
                'teacher_id' => $this->newTeacher->id,
            ])
            ->assertSessionHasNoErrors();

        expect($this->replacedProcess->defenses()->first()->scheduled_at->equalTo($at))->toBeTrue();
    });

    test('UC-006 BR-001: a replaced member cannot enter again and a non-current member cannot be replaced', function () {
        $old = $this->members[0];

        $this->actingAs($this->directorUser)
            ->put(route('committees.replace', $this->replacedProcess), [
                'committee_member_id' => $old->id,
                'teacher_id' => $this->newTeacher->id,
            ])->assertSessionHasNoErrors();

        $this->put(route('committees.replace', $this->replacedProcess), [
            'committee_member_id' => $this->members[1]->id,
            'teacher_id' => $old->teacher_id,
        ])->assertSessionHasErrors('teacher_id');

        $this->put(route('committees.replace', $this->replacedProcess), [
            'committee_member_id' => $old->id,
            'teacher_id' => $this->teachers[1]->id,
        ])->assertSessionHasErrors('committee_member_id');

        expect($this->replacedProcess->currentCommitteeMembers()->count())->toBe(3);
    });

    test('UC-006 BR-004: a process without committee cannot have a member replaced', function () {
        $this->actingAs($this->directorUser)
            ->put(route('committees.replace', $this->process), [
                'committee_member_id' => $this->members[0]->id,
                'teacher_id' => $this->newTeacher->id,
            ])
            ->assertSessionHasErrors('graduation_process');
    });
});

test('UC-006 NFR-005: the career director user used by the tests holds the assign committee permission', function () {
    expect(User::query()->whereKey($this->directorUser->id)->first()->hasPermission('tribunales.asignar'))->toBeTrue();
});
