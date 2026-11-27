<?php

use App\Actions\Defenses\FindPreDefenseDetail;
use App\Actions\Defenses\ListPendingPreDefenses;
use App\Actions\Defenses\RegisterPreDefense;
use App\Actions\ProjectVersions\FindVersionAdmission;
use App\Enums\Phase;
use App\Enums\PreDefenseDecision;
use App\Enums\ProjectVersionStatus;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\DefenseReview;
use App\Models\GraduationProcess;
use App\Models\Notification;
use App\Models\Observation;
use App\Models\ProjectVersion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * A pre-defense ready to be registered: started one hour ago, approved version and three current members.
 *
 * @return array{GraduationProcess, Defense, Collection<int, CommitteeMember>}
 */
function uc010ReadyProcess(): array
{
    $process = GraduationProcess::factory()->withCommittee()->create();
    $version = ProjectVersion::factory()->for($process)->create([
        'phase' => Phase::PreDefense,
        'status' => ProjectVersionStatus::Approved,
        'evaluated_at' => now()->subDay(),
    ]);
    $version->approvers()->attach($process->currentCommitteeMembers->modelKeys(), ['approved_at' => now()->subDay()]);
    $act = Defense::factory()->for($process)->create(['scheduled_at' => now()->subHour()]);

    return [$process, $act, $process->currentCommitteeMembers];
}

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-12-02 11:00:00', 'UTC'));
});

test('UC-010 step 2 and A4: lists only started, pending acts of current tribunals without the member registration', function () {
    [$process, $act, $members] = uc010ReadyProcess();
    $teacher = $members->first()->teacher;
    $list = app(ListPendingPreDefenses::class);

    expect($list->handle($teacher)->pluck('id')->all())->toBe([$act->id]);

    $futureProcess = GraduationProcess::factory()->withCommittee()->create();
    CommitteeMember::factory()->for($futureProcess)->for($teacher, 'teacher')->create();
    Defense::factory()->for($futureProcess)->create(['scheduled_at' => now()->addHour()]);

    expect($list->handle($teacher)->pluck('id')->all())->toBe([$act->id]);

    DefenseReview::factory()->create([
        'defense_id' => $act->id,
        'committee_member_id' => $members->first()->id,
    ]);

    expect($list->handle($teacher)->pluck('id')->all())->toBe([]);

    $futureAct = Defense::query()->where('graduation_process_id', $futureProcess->id)->firstOrFail();
    $futureAct->forceFill(['scheduled_at' => now()->subMinute()])->save();

    expect($list->handle($teacher)->pluck('id')->all())->toBe([$futureAct->id]);

    $futureAct->forceFill(['is_held' => true])->save();

    expect($list->handle($teacher)->pluck('id')->all())->toBe([]);
});

test('UC-010 step 4: shows the approved pre-defense version and the members who already registered', function () {
    [$process, $act, $members] = uc010ReadyProcess();
    $first = $members->get(0);
    $second = $members->get(1);

    app(RegisterPreDefense::class)->handle($first, $act, PreDefenseDecision::NoObservations, [], $act->schedule_revision);

    $detail = app(FindPreDefenseDetail::class)->handle($act, $second);

    expect($detail->version->id)->toBe($process->projectVersions()->where('status', ProjectVersionStatus::Approved)->value('id'))
        ->and($detail->reportedBy->pluck('id')->all())->toBe([$first->id]);

    $outsider = CommitteeMember::factory()->create();

    expect(fn () => app(FindPreDefenseDetail::class)->handle($act, $outsider))->toThrow(ValidationException::class);
});

test('UC-010 steps 6 to 9 and BR-003: three registrations hold the act, move the process to formal defense and notify', function () {
    [$process, $act, $members] = uc010ReadyProcess();
    $register = app(RegisterPreDefense::class);

    foreach ($members->take(2) as $index => $member) {
        $register->handle($member, $act, PreDefenseDecision::Observations, ["Observación {$index}"], $act->schedule_revision);
    }

    expect($act->fresh()->is_held)->toBeFalse()
        ->and($process->fresh()->phase)->toBe(Phase::PreDefense)
        ->and(Notification::query()->count())->toBe(0);

    $last = $members->get(2);
    $register->handle($last, $act, PreDefenseDecision::Observations, ['Observación 2'], $act->schedule_revision);

    expect($act->fresh()->is_held)->toBeTrue()
        ->and($process->fresh()->phase)->toBe(Phase::FormalDefense)
        ->and($process->phaseChanges()->where('phase', Phase::FormalDefense)->count())->toBe(1)
        ->and($act->reviews()->count())->toBe(3)
        ->and($act->observations()->count())->toBe(3)
        ->and($act->reviews()->first()->recorded_at)->not->toBeNull()
        ->and($act->observations()->first()->recorded_at)->not->toBeNull();

    $authors = $act->observations()->orderBy('id')->pluck('author_id')->all();
    expect($authors)->toBe($members->map(fn (CommitteeMember $member) => $member->teacher->user_id)->all())
        ->and($act->observations()->firstOrFail()->defense_id)->toBe($act->id)
        ->and($act->observations()->firstOrFail()->project_version_id)->toBeNull()
        ->and($authors)->not->toContain($last->id);

    $notifications = Notification::query()->get();
    expect($notifications)->toHaveCount(2)
        ->and($notifications->pluck('user_id')->all())->toBe([
            $process->student->user_id,
            $process->tutor->user_id,
        ])
        ->and($notifications->first()->message)->toBe(
            "Se registró la pre-defensa de {$process->student->user->name}. Observaciones del tribunal: "
            .'Observación 0; Observación 1; Observación 2. El trámite pasó a la fase defensa formal.',
        );
});

test('UC-010 step 10 (OQ-058): notifies that there were no observations with the agreed text', function () {
    [$process, $act, $members] = uc010ReadyProcess();
    $register = app(RegisterPreDefense::class);

    foreach ($members as $member) {
        $register->handle($member, $act, PreDefenseDecision::NoObservations, [], $act->schedule_revision);
    }

    $notifications = Notification::query()->get();

    expect($notifications)->toHaveCount(2)
        ->and($notifications->pluck('message')->unique()->all())->toBe([
            "Se registró la pre-defensa de {$process->student->user->name}. Observaciones del tribunal: "
            .'No hubo observaciones. El trámite pasó a la fase defensa formal.',
        ])
        ->and($act->observations()->count())->toBe(0);
});

test('UC-010 step 11 and UC-004 BR-006: after the third registration with observations a formal defense version is admitted', function () {
    [$process, $act, $members] = uc010ReadyProcess();
    $admission = app(FindVersionAdmission::class);
    $student = $process->student->user;

    expect($admission->handle($student)->isAdmitted())->toBeFalse();

    foreach ($members as $member) {
        app(RegisterPreDefense::class)->handle(
            $member,
            $act,
            PreDefenseDecision::Observations,
            ['Revise el documento y el prototipo.'],
            $act->schedule_revision,
        );
    }

    expect($process->fresh()->phase)->toBe(Phase::FormalDefense)
        ->and($admission->handle($student)->isAdmitted())->toBeTrue();
});

test('UC-010 A1 and BR-003: fewer than three registrations leave the act pending and send no notification', function () {
    [$process, $act, $members] = uc010ReadyProcess();

    app(RegisterPreDefense::class)->handle(
        $members->first(),
        $act,
        PreDefenseDecision::NoObservations,
        [],
        $act->schedule_revision,
    );

    expect($act->fresh()->is_held)->toBeFalse()
        ->and($act->reviews()->count())->toBe(1)
        ->and($act->observations()->count())->toBe(0)
        ->and($process->fresh()->phase)->toBe(Phase::PreDefense)
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-010 A2 and BR-004: an absence abandons the process, notifies the participants and leaves no review', function () {
    [$process, $act, $members] = uc010ReadyProcess();
    $member = $members->first();

    app(RegisterPreDefense::class)->handle($member, $act, PreDefenseDecision::Absence, [], $act->schedule_revision);

    $freshAct = $act->fresh();
    $freshProcess = $process->fresh();

    expect($freshAct->is_held)->toBeTrue()
        ->and($freshAct->is_student_absent)->toBeTrue()
        ->and($freshAct->absence_recorded_by_id)->toBe($member->id)
        ->and($freshProcess->abandoned_at)->not->toBeNull()
        ->and($freshProcess->phase)->toBe(Phase::PreDefense)
        ->and($freshAct->reviews()->count())->toBe(0)
        ->and($freshAct->observations()->count())->toBe(0);

    $notifications = Notification::query()->get();
    expect($notifications)->toHaveCount(4)
        ->and($notifications->pluck('user_id')->all())->toBe([
            $process->student->user_id,
            $process->tutor->user_id,
            $members->get(1)->teacher->user_id,
            $members->get(2)->teacher->user_id,
        ])
        ->and($notifications->first()->message)->toBe(
            "Se registró la inasistencia de {$process->student->user->name} a la pre-defensa. "
            .'El trámite de titulación quedó abandonado.',
        );
});

test('UC-010 A3 and BR-005: rejects a missing decision, blank observations and observations over 2000 characters', function () {
    [$process, $act, $members] = uc010ReadyProcess();
    $member = $members->first();
    $register = app(RegisterPreDefense::class);

    expect(fn () => $register->handle($member, $act, null, [], $act->schedule_revision))
        ->toThrow(ValidationException::class, 'Elija si registra observaciones, que no las hubo o la inasistencia del estudiante.');
    expect(fn () => $register->handle($member, $act, PreDefenseDecision::Observations, ['  '], $act->schedule_revision))
        ->toThrow(ValidationException::class, 'Registre al menos una observación.');
    expect(fn () => $register->handle($member, $act, PreDefenseDecision::Observations, [str_repeat('a', 2001)], $act->schedule_revision))
        ->toThrow(ValidationException::class, 'Cada observación admite como máximo 2000 caracteres.');

    expect($act->reviews()->count())->toBe(0)
        ->and(Observation::query()->count())->toBe(0)
        ->and(Notification::query()->count())->toBe(0)
        ->and($act->fresh()->is_held)->toBeFalse();
});

test('UC-010 A5 and BR-001: unavailable registrations report it without writing anything', function () {
    $scenarios = [
        'stale revision' => fn (Defense $act, CommitteeMember $member, GraduationProcess $process) => [$act, $member, 2, []],
        // UC-010 BR-002: the same guard also rejects an act whose start is still in the future.
        'future act' => function (Defense $act, CommitteeMember $member, GraduationProcess $process): array {
            $act->forceFill(['scheduled_at' => now()->addHour()])->save();

            return [$act, $member, $act->schedule_revision, []];
        },
        'held act' => function (Defense $act, CommitteeMember $member, GraduationProcess $process): array {
            $act->forceFill(['is_held' => true])->save();

            return [$act, $member, $act->schedule_revision, []];
        },
        'replaced member' => function (Defense $act, CommitteeMember $member, GraduationProcess $process): array {
            $member->forceFill(['replaced_at' => now()->subMinute()])->save();

            return [$act, $member, $act->schedule_revision, []];
        },
        'abandoned process' => function (Defense $act, CommitteeMember $member, GraduationProcess $process): array {
            $process->forceFill(['abandoned_at' => now()->subMinute()])->save();

            return [$act, $member, $act->schedule_revision, []];
        },
        'already registered' => function (Defense $act, CommitteeMember $member, GraduationProcess $process): array {
            DefenseReview::factory()->create([
                'defense_id' => $act->id,
                'committee_member_id' => $member->id,
            ]);

            return [$act, $member, $act->schedule_revision, []];
        },
        'absence after a registration' => function (Defense $act, CommitteeMember $member, GraduationProcess $process): array {
            DefenseReview::factory()->create([
                'defense_id' => $act->id,
                'committee_member_id' => $process->currentCommitteeMembers->get(1)->id,
            ]);

            return [$act, $member, $act->schedule_revision, [PreDefenseDecision::Absence]];
        },
    ];

    foreach ($scenarios as $scenario => $prepare) {
        [$process, $act, $members] = uc010ReadyProcess();
        $member = $members->first();
        [$act, $member, $revision, $decision] = $prepare($act, $member, $process);
        $reviews = $act->reviews()->count();
        $notifications = Notification::query()->count();

        $action = fn () => app(RegisterPreDefense::class)->handle(
            $member,
            $act,
            $decision === [] ? PreDefenseDecision::NoObservations : $decision[0],
            [],
            $revision,
        );

        try {
            $action();
            $this->fail("El escenario {$scenario} debería haber fallado.");
        } catch (ValidationException $exception) {
            expect($exception->errors())->toHaveKey('availability');
        }

        expect($act->reviews()->count())->toBe($reviews, $scenario)
            ->and(Notification::query()->count())->toBe($notifications, $scenario)
            ->and($process->fresh()->phase)->toBe(Phase::PreDefense, $scenario);
    }
});
