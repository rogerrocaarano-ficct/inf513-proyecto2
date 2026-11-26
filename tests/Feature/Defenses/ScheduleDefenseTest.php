<?php

use App\Enums\DefenseType;
use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\Notification;
use App\Models\ProjectVersion;
use App\Models\Teacher;
use Illuminate\Support\Carbon;

function approvedPreDefenseProcess(): GraduationProcess
{
    $process = GraduationProcess::factory()->withCommittee()->create();
    $version = ProjectVersion::factory()->for($process)->create([
        'phase' => Phase::PreDefense,
        'status' => ProjectVersionStatus::Approved,
        'evaluated_at' => now()->subDay(),
    ]);
    $version->approvers()->attach($process->currentCommitteeMembers->modelKeys(), ['approved_at' => now()->subDay()]);

    return $process;
}

/** @return array<string, mixed> */
function defenseSchedule(GraduationProcess $process, array $overrides = []): array
{
    return array_replace([
        'process_id' => $process->id,
        'type' => DefenseType::PreDefense->value,
        'scheduled_at' => '2026-12-02T10:00',
        'location' => 'Aula 101',
    ], $overrides);
}

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-12-01 14:00:00', 'UTC'));
    $this->director = Teacher::factory()->careerDirector()->create()->user;
});

test('UC-009 NFR-005: only the director can consult or change the calendar', function () {
    $this->get(route('defenses.index'))->assertRedirect(route('login'));

    $this->actingAs(Teacher::factory()->create()->user);
    $this->get(route('defenses.index'))->assertForbidden();
    $this->post(route('defenses.store'))->assertForbidden();
});

test('UC-009 steps 1 to 5: lists pending acts and only eligible processes with their current participants', function () {
    $preDefense = approvedPreDefenseProcess();
    $formal = GraduationProcess::factory()->withCommittee()->inPhase(Phase::FormalDefense)->create();
    Defense::factory()->for($formal)->held()->create();
    $inReview = GraduationProcess::factory()->withCommittee()->create();
    ProjectVersion::factory()->for($inReview)->create(['phase' => Phase::PreDefense]);
    $abandoned = GraduationProcess::factory()->abandoned()->create();
    $act = Defense::factory()->for($abandoned)->create([
        'scheduled_at' => '2026-12-02 14:00:00',
        'location' => 'Aula 101',
    ]);

    $this->actingAs($this->director)->get(route('defenses.index'))->assertOk()
        ->assertInertia(fn ($page) => $page->component('defenses/index')
            ->has('calendar', 1)
            ->where('calendar.0.defense_id', $act->id)
            ->where('calendar.0.scheduled_at', '2026-12-02T10:00')
            ->where('calendar.0.location', 'Aula 101')
            ->where('calendar.0.student', $abandoned->student->user->name)
            ->where('calendar.0.tutor', $abandoned->tutor->user->name)
            ->has('calendar.0.committee', 3)
            ->where('calendar.0.can_reschedule', false)
            ->has('waiting', 2)
            ->where('waiting.0.process_id', $preDefense->id)
            ->where('waiting.0.type', DefenseType::PreDefense->value)
            ->where('waiting.1.process_id', $formal->id)
            ->where('waiting.1.type', DefenseType::FormalDefense->value));
});

test('UC-009 A4: an empty calendar remains available without changing any records', function () {
    $this->actingAs($this->director)->get(route('defenses.index'))->assertOk()
        ->assertInertia(fn ($page) => $page->has('calendar', 0)->has('waiting', 0));

    $this->assertDatabaseCount('defenses', 0);
    $this->assertDatabaseCount('notifications', 0);
});

test('UC-009 BR-001: schedules a pre-defense and notifies exactly the student, tutor and current committee', function () {
    $process = approvedPreDefenseProcess();
    $former = CommitteeMember::factory()->for($process)->replaced()->create();

    $this->actingAs($this->director)->post(route('defenses.store'), defenseSchedule($process))
        ->assertRedirect(route('defenses.index'))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('defenses', [
        'graduation_process_id' => $process->id,
        'type' => DefenseType::PreDefense->value,
        'scheduled_at' => '2026-12-02 14:00:00',
        'location' => 'Aula 101',
        'is_held' => false,
    ]);

    $recipients = $process->currentCommitteeMembers()->with('teacher')->get()
        ->map(fn (CommitteeMember $member) => $member->teacher->user_id)
        ->push($process->student->user_id, $process->tutor->user_id);
    $notifications = Notification::query()->whereBelongsTo($process)->get();
    expect($notifications->pluck('user_id')->all())->toEqualCanonicalizing($recipients->all());
    expect($notifications->pluck('user_id'))->not->toContain($former->teacher->user_id);
    expect($notifications->firstOrFail()->message)->toContain('02/12/2026 10:00', 'Aula 101');
});

test('UC-009 BR-002: a held pre-defense permits scheduling without a corrected version or a room', function () {
    $process = GraduationProcess::factory()->withCommittee()->inPhase(Phase::FormalDefense)->create();
    Defense::factory()->for($process)->held()->create();

    $this->actingAs($this->director)->post(route('defenses.store'), defenseSchedule($process, [
        'type' => DefenseType::FormalDefense->value,
        'location' => null,
    ]))->assertRedirect(route('defenses.index'))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('defenses', [
        'graduation_process_id' => $process->id,
        'type' => DefenseType::FormalDefense->value,
        'location' => null,
    ]);
});

test('UC-009 BR-001: an approval by a replaced member does not let the current committee schedule', function () {
    $process = approvedPreDefenseProcess();
    $process->currentCommitteeMembers->firstOrFail()->forceFill(['replaced_at' => now()])->save();
    CommitteeMember::factory()->for($process)->create(['assigned_at' => now()]);

    $this->actingAs($this->director)->post(route('defenses.store'), defenseSchedule($process))
        ->assertRedirect(route('defenses.index'))->assertInertiaFlash('toast.type', 'error');
    $this->assertDatabaseCount('defenses', 0);
    $this->assertDatabaseCount('notifications', 0);
});

test('UC-009 A1 and BR-003: rejects cross-role and room conflicts, preserving input, and allows adjacent slots', function () {
    $process = approvedPreDefenseProcess();
    $other = GraduationProcess::factory()->inPhase(Phase::FormalDefense)->create();
    CommitteeMember::factory()->count(2)->for($other)->create();
    CommitteeMember::factory()->for($other)->for($process->tutor, 'teacher')->create();
    Defense::factory()->for($other)->formalDefense()->create([
        'scheduled_at' => '2026-12-02 13:00:00',
        'location' => 'aula 101',
    ]);

    $payload = defenseSchedule($process);
    $this->actingAs($this->director)->from(route('defenses.index'))
        ->post(route('defenses.store'), $payload)
        ->assertRedirect(route('defenses.index'))
        ->assertSessionHasErrors(['scheduled_at'])
        ->assertSessionHasInput('scheduled_at', $payload['scheduled_at'])
        ->assertSessionHasInput('location', $payload['location']);
    $message = session('errors')->first('scheduled_at');
    expect($message)->toContain($process->tutor->user->name, 'Aula 101', $other->student->user->name, 'defensa formal');
    $this->assertDatabaseCount('defenses', 1);
    $this->assertDatabaseCount('notifications', 0);

    $this->post(route('defenses.store'), defenseSchedule($process, ['scheduled_at' => '2026-12-02T11:00']))
        ->assertRedirect(route('defenses.index'))->assertSessionHasNoErrors();
    $this->assertDatabaseCount('defenses', 2);
});

test('UC-009 A2 and BR-004: rejects the current minute, past dates and invalid form inputs', function () {
    $process = approvedPreDefenseProcess();
    $this->actingAs($this->director)->from(route('defenses.index'));

    foreach (['2026-12-01T10:00', '2026-12-01T09:59'] as $date) {
        $this->post(route('defenses.store'), defenseSchedule($process, ['scheduled_at' => $date]))
            ->assertSessionHasErrors(['scheduled_at' => 'La fecha y hora deben ser futuras.'])
            ->assertSessionHasInput('scheduled_at', $date);
    }

    $this->post(route('defenses.store'), defenseSchedule($process, [
        'scheduled_at' => '2026-02-30T10:00',
        'location' => str_repeat('a', 101),
        'defense_id' => 1,
    ]))->assertSessionHasErrors(['scheduled_at', 'location', 'schedule_revision']);
    $this->assertDatabaseCount('defenses', 0);
    $this->assertDatabaseCount('notifications', 0);
});

test('UC-009 A3 and A6: updates the same act once, excluding itself, and rejects a stale revision', function () {
    $process = approvedPreDefenseProcess();
    $act = Defense::factory()->for($process)->create([
        'scheduled_at' => '2026-12-02 13:00:00',
        'location' => 'Aula 101',
    ]);
    $payload = defenseSchedule($process, ['defense_id' => $act->id, 'schedule_revision' => 1]);

    $this->actingAs($this->director)->post(route('defenses.store'), $payload)
        ->assertRedirect(route('defenses.index'));
    expect($act->fresh()->schedule_revision)->toBe(2);

    $this->post(route('defenses.store'), array_replace($payload, ['scheduled_at' => '2026-12-02T11:00']))
        ->assertRedirect(route('defenses.index'))
        ->assertInertiaFlash('toast.type', 'error');
    expect($act->fresh()->scheduled_at->toDateTimeString())->toBe('2026-12-02 14:00:00');
    $this->assertDatabaseCount('defenses', 1);
    $this->assertDatabaseCount('notifications', 5);
    expect(Notification::query()->firstOrFail()->message)->toContain('reprogramó');
});

test('UC-009 A6: unavailable requests return to the current calendar without writes', function () {
    $process = approvedPreDefenseProcess();
    $other = GraduationProcess::factory()->withCommittee()->inPhase(Phase::FormalDefense)->create();
    $pending = Defense::factory()->for($process)->create();
    $held = Defense::factory()->for($process)->held()->create();

    $this->actingAs($this->director);
    foreach ([
        defenseSchedule($process),
        defenseSchedule($other, ['type' => DefenseType::FormalDefense->value]),
        defenseSchedule($process, ['defense_id' => $held->id, 'schedule_revision' => 1]),
    ] as $payload) {
        $this->post(route('defenses.store'), $payload)
            ->assertRedirect(route('defenses.index'))->assertInertiaFlash('toast.type', 'error');
    }

    $process->forceFill(['abandoned_at' => now()])->save();
    $this->post(route('defenses.store'), defenseSchedule($process, [
        'defense_id' => $pending->id,
        'schedule_revision' => 1,
    ]))->assertRedirect(route('defenses.index'))->assertInertiaFlash('toast.type', 'error');
    $this->assertDatabaseCount('defenses', 2);
    $this->assertDatabaseCount('notifications', 0);
});
