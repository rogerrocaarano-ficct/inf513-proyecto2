<?php

use App\Actions\Defenses\FindScheduleConflicts;
use App\Enums\Phase;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\Teacher;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->actAt = CarbonImmutable::parse('2026-12-10 10:00');
    $this->process = GraduationProcess::factory()->withCommittee()->create();
    Defense::factory()->for($this->process)->create(['scheduled_at' => $this->actAt]);
    $this->teacher = Teacher::factory()->create();
});

test('an act of another process within two hours, where the teacher is a committee member, conflicts (UC-009 BR-003)', function () {
    $other = GraduationProcess::factory()->inPhase(Phase::PreDefense)->create();
    CommitteeMember::factory()->for($other)->for($this->teacher)->create();
    $otherAct = Defense::factory()->for($other)->create(['scheduled_at' => $this->actAt->addMinutes(90)]);

    $conflicts = app(FindScheduleConflicts::class)->handle($this->teacher, $this->process);

    expect($conflicts)->toHaveCount(1)
        ->and($conflicts->first()['conflictsWith']->is($otherAct))->toBeTrue();
});

test('an act of another process the teacher tutors conflicts too (UC-009 BR-003)', function () {
    $other = GraduationProcess::factory()->withTutor($this->teacher)->inPhase(Phase::PreDefense)->create();
    Defense::factory()->for($other)->create(['scheduled_at' => $this->actAt->subHour()]);

    expect(app(FindScheduleConflicts::class)->handle($this->teacher, $this->process))->toHaveCount(1);
});

test('acts two hours apart do not conflict', function () {
    $other = GraduationProcess::factory()->withTutor($this->teacher)->inPhase(Phase::PreDefense)->create();
    Defense::factory()->for($other)->create(['scheduled_at' => $this->actAt->addHours(2)]);

    expect(app(FindScheduleConflicts::class)->handle($this->teacher, $this->process))->toBeEmpty();
});

test('a replaced committee membership and a held act do not conflict', function () {
    $replacedIn = GraduationProcess::factory()->inPhase(Phase::PreDefense)->create();
    CommitteeMember::factory()->for($replacedIn)->for($this->teacher)->replaced()->create();
    Defense::factory()->for($replacedIn)->create(['scheduled_at' => $this->actAt]);

    $held = GraduationProcess::factory()->withTutor($this->teacher)->inPhase(Phase::PreDefense)->create();
    Defense::factory()->for($held)->held()->create(['scheduled_at' => $this->actAt]);

    expect(app(FindScheduleConflicts::class)->handle($this->teacher, $this->process))->toBeEmpty();
});

test('a process without pending acts has no conflicts', function () {
    $withoutActs = GraduationProcess::factory()->withCommittee()->create();
    $other = GraduationProcess::factory()->withTutor($this->teacher)->inPhase(Phase::PreDefense)->create();
    Defense::factory()->for($other)->create(['scheduled_at' => $this->actAt]);

    expect(app(FindScheduleConflicts::class)->handle($this->teacher, $withoutActs))->toBeEmpty();
});
