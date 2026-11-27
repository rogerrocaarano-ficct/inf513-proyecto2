<?php

use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\Notification;
use App\Models\Observation;
use App\Models\ProjectVersion;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

/** @return array{GraduationProcess, Defense, Collection<int, CommitteeMember>} */
function uc011Defense(): array
{
    $process = GraduationProcess::factory()->withCommittee()->inPhase(Phase::FormalDefense)->create();
    $defense = Defense::factory()->for($process)->formalDefense()->create(['scheduled_at' => now()->subHour()]);

    return [$process, $defense, $process->currentCommitteeMembers()->orderBy('id')->get()];
}

beforeEach(function () {
    $this->withoutVite();
    $this->freezeTime();
});

test('UC-011 BR-003 and BR-004: the third grade closes the defense using the rounded result', function (array $grades, int $joint, string $result) {
    [$process, $defense, $members] = uc011Defense();

    foreach ($members as $index => $member) {
        $this->actingAs($member->teacher->user)
            ->post(route('formal-defense-reviews.store', $defense), [
                'schedule_revision' => $defense->schedule_revision,
                'absent' => false,
                'grade' => $grades[$index],
            ])
            ->assertRedirect(route('formal-defense-reviews.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('defense_reviews', [
            'defense_id' => $defense->id,
            'committee_member_id' => $member->id,
            'grade' => $grades[$index],
            'recorded_at' => now(),
        ]);

        if ($index < 2) {
            expect($defense->fresh()->is_held)->toBeFalse()
                ->and($defense->fresh()->grade)->toBeNull()
                ->and($process->fresh()->concluded_at)->toBeNull();
            $this->assertDatabaseCount('notifications', 0);
        }
    }

    expect($defense->fresh()->is_held)->toBeTrue()
        ->and($defense->fresh()->grade)->toBe($joint)
        ->and($process->fresh()->concluded_at)->not->toBeNull()
        ->and($process->fresh()->abandoned_at)->toBeNull()
        ->and(GraduationProcess::query()->approved()->whereKey($process->id)->exists())->toBe($joint >= 51);

    $recipients = $members->map(fn (CommitteeMember $member): int => $member->teacher->user_id)
        ->push($process->student->user_id, $process->tutor->user_id);
    $this->assertDatabaseCount('notifications', 5);
    foreach ($recipients as $recipient) {
        $this->assertDatabaseHas('notifications', [
            'user_id' => $recipient,
            'message' => "La defensa formal se realizó con {$joint} de 100. El trámite de titulación concluyó {$result}.",
        ]);
    }
})->with([
    'rounds up to passing' => [[0, 100, 52], 51, 'aprobado'],
    'rounds down to failing' => [[0, 100, 51], 50, 'reprobado'],
]);

test('UC-011 A2: absence records the member and abandons the process without grades', function () {
    [$process, $defense, $members] = uc011Defense();
    $member = $members[0];

    $this->actingAs($member->teacher->user)->post(route('formal-defense-reviews.store', $defense), [
        'schedule_revision' => $defense->schedule_revision,
        'absent' => true,
    ])->assertRedirect(route('formal-defense-reviews.index'))->assertSessionHasNoErrors();

    expect($defense->fresh()->is_held)->toBeTrue()
        ->and($defense->fresh()->is_student_absent)->toBeTrue()
        ->and($defense->fresh()->absence_recorded_by_id)->toBe($member->id)
        ->and($defense->fresh()->grade)->toBeNull()
        ->and($process->fresh()->abandoned_at)->not->toBeNull()
        ->and($process->fresh()->concluded_at)->toBeNull();
    $this->assertDatabaseCount('defense_reviews', 0);
    $this->assertDatabaseCount('notifications', 4);
    expect(Notification::query()->where('user_id', $member->teacher->user_id)->exists())->toBeFalse();
});

test('UC-011 A3: invalid grades are returned in Spanish without writes', function (mixed $grade) {
    [$process, $defense, $members] = uc011Defense();
    $form = route('formal-defense-reviews.show', $defense);

    $this->actingAs($members[0]->teacher->user)->from($form)
        ->post(route('formal-defense-reviews.store', $defense), [
            'schedule_revision' => $defense->schedule_revision,
            'absent' => false,
            'grade' => $grade,
        ])->assertRedirect($form)->assertSessionHasErrors(['grade' => 'Ingrese una calificación entera entre 0 y 100.']);

    $this->assertDatabaseCount('defense_reviews', 0);
    $this->assertDatabaseCount('notifications', 0);
    expect($process->fresh()->concluded_at)->toBeNull();
})->with([[null], [-1], [101], ['50.5'], [true], [['50']]]);

test('UC-011 A5: rejects duplicate, outdated and absence registrations after a grade', function () {
    [, $defense, $members] = uc011Defense();
    $submit = fn (CommitteeMember $member, int $revision, bool $absent = false) => $this->actingAs($member->teacher->user)
        ->post(route('formal-defense-reviews.store', $defense), ['schedule_revision' => $revision, 'absent' => $absent, 'grade' => 80]);

    $defense->forceFill(['schedule_revision' => 2])->save();
    $submit($members[0], 1)->assertRedirect(route('formal-defense-reviews.index'))
        ->assertInertiaFlash('toast.message', 'El registro de la defensa formal ya no está disponible.');
    $this->assertDatabaseCount('defense_reviews', 0);
    $submit($members[0], 2)->assertSessionHasNoErrors();
    $submit($members[0], 2)->assertRedirect(route('formal-defense-reviews.index'));
    $submit($members[1], 2, true)->assertRedirect(route('formal-defense-reviews.index'));
    $members[2]->update(['replaced_at' => now()]);
    $submit($members[2], 2)->assertRedirect(route('formal-defense-reviews.index'));

    $this->assertDatabaseCount('defense_reviews', 1);
    $this->assertDatabaseCount('notifications', 0);
    expect($defense->fresh()->is_held)->toBeFalse();
});

test('UC-011 BR-003 counts only grades of current committee members', function () {
    [$process, $defense, $members] = uc011Defense();
    $record = function (CommitteeMember $member) use ($defense): void {
        $this->actingAs($member->teacher->user)->post(route('formal-defense-reviews.store', $defense), [
            'schedule_revision' => 1,
            'absent' => false,
            'grade' => 60,
        ])->assertSessionHasNoErrors();
    };

    $record($members[0]);
    $members[0]->update(['replaced_at' => now()]);
    $replacement = CommitteeMember::factory()->for($process)->create();
    $record($members[1]);
    $record($members[2]);

    expect($defense->fresh()->is_held)->toBeFalse();
    $record($replacement);
    expect($defense->fresh()->grade)->toBe(60);
    $this->assertDatabaseCount('defense_reviews', 4);
    $this->assertDatabaseMissing('notifications', ['user_id' => $members[0]->teacher->user_id]);
});

test('UC-011 A4 and NFR-004: the pending list and pages enforce participation and start time', function () {
    [$process, $defense, $members] = uc011Defense();
    $index = route('formal-defense-reviews.index');
    $show = route('formal-defense-reviews.show', $defense);

    $this->get($index)->assertRedirect(route('login'));
    $this->actingAs($process->student->user)->get($index)->assertForbidden();
    $this->actingAs(Teacher::factory()->create()->user)->get($show)->assertForbidden();
    $this->post(route('formal-defense-reviews.store', $defense))->assertForbidden();

    $this->actingAs($members[0]->teacher->user)->get($index)->assertInertia(fn (AssertableInertia $page) => $page
        ->component('formal-defense-reviews/index')->has('defenses', 1)->where('defenses.0.id', $defense->id));

    $defense->update(['scheduled_at' => now()->addHour()]);
    $this->get($index)->assertInertia(fn (AssertableInertia $page) => $page->has('defenses', 0));
    $this->get($show)->assertRedirect($index);
    $this->post(route('formal-defense-reviews.store', $defense), [
        'schedule_revision' => 1,
        'absent' => false,
        'grade' => 80,
    ])->assertRedirect($index);
    $this->assertDatabaseCount('defense_reviews', 0);
});

test('UC-011 step 4 shows status without individual grades and serves only the defended PDF', function (bool $withObservations) {
    [$process, $defense, $members] = uc011Defense();
    Storage::fake('local');
    $preDefense = Defense::factory()->for($process)->held()->create(['scheduled_at' => now()->subWeek()]);
    $approved = ProjectVersion::factory()->for($process)->create([
        'number' => 2,
        'phase' => Phase::PreDefense,
        'status' => ProjectVersionStatus::Approved,
        'submitted_at' => now()->subWeeks(2),
        'evaluated_at' => now()->subWeek(),
    ]);

    $version = $approved;
    $other = ProjectVersion::factory()->create();
    if ($withObservations) {
        Observation::query()->create([
            'defense_id' => $preDefense->id,
            'author_id' => $members[0]->teacher->user_id,
            'body' => 'Corregir el documento.',
            'recorded_at' => now()->subWeek(),
        ]);
        foreach ([3 => now()->subDays(3), 4 => now()->subDays(2), 5 => now()] as $number => $submittedAt) {
            $candidate = ProjectVersion::factory()->for($process)->create([
                'number' => $number,
                'phase' => Phase::FormalDefense,
                'status' => ProjectVersionStatus::Submitted,
                'submitted_at' => $submittedAt,
            ]);
            if ($number === 4) {
                $version = $candidate;
            }
        }
    }

    Storage::disk('local')->put($version->file_path, 'PDF');
    $this->actingAs($members[0]->teacher->user)->post(route('formal-defense-reviews.store', $defense), [
        'schedule_revision' => 1,
        'absent' => false,
        'grade' => 76,
    ])->assertSessionHasNoErrors();
    $this->get(route('formal-defense-reviews.index'))->assertInertia(fn (AssertableInertia $page) => $page->has('defenses', 0));

    $this->actingAs($members[1]->teacher->user)->get(route('formal-defense-reviews.show', $defense))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('formal-defense-reviews/show')
            ->where('version.number', $version->number)
            ->where('canReportAbsence', false)
            ->has('members', 3)
            ->where('members.0.graded', true)
            ->where('members.1.graded', false)
            ->missing('members.0.grade'));

    $this->get(route('formal-defense-reviews.download', [$defense, $version]))->assertDownload($version->original_file_name);
    $this->get(route('formal-defense-reviews.download', [$defense, $other]))->assertNotFound();
    $this->actingAs(Teacher::factory()->create()->user)
        ->get(route('formal-defense-reviews.download', [$defense, $version]))->assertForbidden();
})->with([true, false]);
