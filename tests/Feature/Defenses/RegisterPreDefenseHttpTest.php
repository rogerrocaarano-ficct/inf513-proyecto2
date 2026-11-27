<?php

use App\Actions\Defenses\RegisterPreDefense;
use App\Enums\Phase;
use App\Enums\PreDefenseDecision;
use App\Enums\ProjectVersionStatus;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\DefenseReview;
use App\Models\GraduationProcess;
use App\Models\Notification;
use App\Models\ProjectVersion;
use App\Models\Teacher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * A started pre-defense with an approved version and three current committee members.
 *
 * @return array{GraduationProcess, Defense, Collection<int, CommitteeMember>, ProjectVersion}
 */
function uc010HttpProcess(): array
{
    $process = GraduationProcess::factory()->withCommittee()->create();
    $version = ProjectVersion::factory()->for($process)->create([
        'phase' => Phase::PreDefense,
        'status' => ProjectVersionStatus::Approved,
        'original_file_name' => 'pre-defensa.pdf',
        'evaluated_at' => now()->subDay(),
    ]);
    $version->approvers()->attach($process->currentCommitteeMembers->modelKeys(), ['approved_at' => now()->subDay()]);
    $act = Defense::factory()->for($process)->create(['scheduled_at' => now()->subHour()]);

    return [$process, $act, $process->currentCommitteeMembers, $version];
}

beforeEach(function () {
    $this->withoutVite();
    $this->travelTo(Carbon::parse('2026-12-02 11:00:00', 'UTC'));
});

test('UC-010 NFR-005: guests and users without the permission cannot open the registration pages', function () {
    [$process, $act] = uc010HttpProcess();
    $student = $process->student->user;

    $this->get(route('defense-reviews.index'))->assertRedirect(route('login'));

    $this->actingAs($student)->get(route('defense-reviews.index'))->assertForbidden();
    $this->actingAs($student)->get(route('defense-reviews.show', $act))->assertForbidden();
    $this->actingAs($student)
        ->post(route('defense-reviews.store', $act), [
            'decision' => PreDefenseDecision::NoObservations->value,
            'schedule_revision' => $act->schedule_revision,
        ])
        ->assertForbidden();
});

test('UC-010 steps 1-2 and A4: the list shows only started pending acts of current tribunals', function () {
    [$process, $act, $members] = uc010HttpProcess();
    $member = $members->first();
    $user = $member->teacher->user;

    $this->actingAs($user)
        ->get(route('defense-reviews.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('defense-reviews/index')
            ->has('preDefenses', 1)
            ->where('preDefenses.0.id', $act->id)
            ->where('preDefenses.0.student', $process->student->user->name)
            ->where('message', null));

    $futureProcess = GraduationProcess::factory()->withCommittee()->create();
    CommitteeMember::factory()->for($futureProcess)->for($member->teacher)->create();
    Defense::factory()->for($futureProcess)->create(['scheduled_at' => now()->addHour()]);

    $this->actingAs($user)
        ->get(route('defense-reviews.index'))
        ->assertInertia(fn ($page) => $page->has('preDefenses', 1));

    DefenseReview::factory()->create([
        'defense_id' => $act->id,
        'committee_member_id' => $member->id,
    ]);

    $this->actingAs($user)
        ->get(route('defense-reviews.index'))
        ->assertInertia(fn ($page) => $page
            ->has('preDefenses', 0)
            ->where('message', 'No hay pre-defensas pendientes de su registro.'));

    $member->forceFill(['replaced_at' => now()->subMinute()])->save();

    $this->actingAs($user)
        ->get(route('defense-reviews.index'))
        ->assertInertia(fn ($page) => $page
            ->has('preDefenses', 0)
            ->where('message', 'No hay pre-defensas pendientes de su registro.'));
});

test('UC-010 step 4 and NFR-004: the detail shows the approved version and the members who registered', function () {
    [$process, $act, $members, $version] = uc010HttpProcess();
    $first = $members->get(0);
    $second = $members->get(1);

    // UC-010 step 5 and BR-004: the three options are offered while nobody registered.
    $this->actingAs($first->teacher->user)
        ->get(route('defense-reviews.show', $act))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('decisions', 3)
            ->where('decisions.2.value', 'inasistencia'));

    app(RegisterPreDefense::class)->handle(
        $first,
        $act,
        PreDefenseDecision::NoObservations,
        [],
        $act->schedule_revision,
    );

    $this->actingAs($second->teacher->user)
        ->get(route('defense-reviews.show', $act))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('defense-reviews/show')
            ->where('preDefense.id', $act->id)
            ->where('preDefense.version.id', $version->id)
            ->where('preDefense.version.file_name', 'pre-defensa.pdf')
            ->where('preDefense.version.download_url', route('defense-reviews.download', [$act, $version]))
            ->has('reportedBy', 1)
            ->where('reportedBy.0.id', $first->id)
            ->where('reportedBy.0.name', $first->teacher->user->name)
            // UC-010 step 5 and BR-004: the absence is gone once somebody registered.
            ->has('decisions', 2)
            ->where('decisions.0.value', 'observaciones')
            ->where('decisions.1.value', 'sin-observaciones'));

    $this->actingAs(Teacher::factory()->create()->user)
        ->get(route('defense-reviews.show', $act))
        ->assertForbidden();

    $this->actingAs(Teacher::factory()->create()->user)
        ->post(route('defense-reviews.store', $act), [
            'decision' => PreDefenseDecision::NoObservations->value,
            'schedule_revision' => $act->schedule_revision,
        ])
        ->assertSessionHasErrors(['defense' => 'No es miembro vigente del tribunal de esta pre-defensa.']);

    $second->forceFill(['replaced_at' => now()->subMinute()])->save();

    $this->actingAs($second->teacher->user)
        ->get(route('defense-reviews.show', $act))
        ->assertForbidden();
});

test('UC-010 step 4, NFR-004 and NFR-008: a current member downloads the approved version', function () {
    Storage::fake('local');

    [$process, $act, $members, $version] = uc010HttpProcess();
    Storage::disk('local')->put($version->file_path, 'contenido');
    $user = $members->first()->teacher->user;

    $this->actingAs($user)
        ->get(route('defense-reviews.download', [$act, $version]))
        ->assertOk()
        ->assertDownload('pre-defensa.pdf');

    $this->actingAs(Teacher::factory()->create()->user)
        ->get(route('defense-reviews.download', [$act, $version]))
        ->assertForbidden();
    $this->actingAs($process->student->user)
        ->get(route('defense-reviews.download', [$act, $version]))
        ->assertForbidden();

    $formalVersion = ProjectVersion::factory()->for($process)->create([
        'number' => 2,
        'phase' => Phase::FormalDefense,
        'status' => ProjectVersionStatus::Approved,
    ]);

    $this->actingAs($user)
        ->get(route('defense-reviews.download', [$act, $formalVersion]))
        ->assertNotFound();
});

test('UC-010 A3: the form rejects an invalid decision and observations in Spanish without writing', function () {
    [$process, $act, $members] = uc010HttpProcess();
    $user = $members->first()->teacher->user;
    $route = route('defense-reviews.store', $act);
    $base = ['schedule_revision' => $act->schedule_revision];

    $this->actingAs($user)->post($route, $base)
        ->assertSessionHasErrors(['decision' => 'Elija si registra observaciones, que no las hubo o la inasistencia del estudiante.']);
    $this->actingAs($user)->post($route, $base + [
        'decision' => PreDefenseDecision::Observations->value,
        'observations' => ['  '],
    ])->assertSessionHasErrors(['observations' => 'Registre al menos una observación.']);
    $this->actingAs($user)->post($route, $base + [
        'decision' => PreDefenseDecision::Observations->value,
        'observations' => [str_repeat('a', 2001)],
    ])->assertSessionHasErrors(['observations.0' => 'Cada observación admite como máximo 2000 caracteres.']);
    $this->actingAs($user)->post($route, ['decision' => PreDefenseDecision::NoObservations->value])
        ->assertSessionHasErrors(['schedule_revision' => 'La revisión de la programación es obligatoria.']);

    expect($act->reviews()->count())->toBe(0)
        ->and($act->observations()->count())->toBe(0)
        ->and(Notification::query()->count())->toBe(0)
        ->and($act->fresh()->is_held)->toBeFalse();
});

test('UC-010 steps 6-11 and A1: three confirmations report the state and move the process to formal defense', function () {
    [$process, $act, $members] = uc010HttpProcess();
    $messages = [
        'Se registró la pre-defensa. Faltan registros de otros miembros del tribunal; la pre-defensa sigue pendiente.',
        'Se registró la pre-defensa. Faltan registros de otros miembros del tribunal; la pre-defensa sigue pendiente.',
        'Se registró la pre-defensa. La pre-defensa quedó realizada y el trámite pasó a la fase defensa formal.',
    ];

    foreach ($members->values() as $index => $member) {
        $this->actingAs($member->teacher->user)
            ->post(route('defense-reviews.store', $act), [
                'decision' => PreDefenseDecision::NoObservations->value,
                'schedule_revision' => $act->schedule_revision,
            ])
            ->assertRedirect(route('defense-reviews.index'))
            ->assertInertiaFlash('toast.type', 'success')
            ->assertInertiaFlash('toast.message', $messages[$index]);
    }

    expect($act->fresh()->is_held)->toBeTrue()
        ->and($process->fresh()->phase)->toBe(Phase::FormalDefense)
        ->and($act->reviews()->count())->toBe(3)
        ->and(Notification::query()->count())->toBe(2);
});

test('UC-010 A5: an unavailable registration reports the reason and writes nothing', function () {
    [$process, $act, $members] = uc010HttpProcess();

    $this->actingAs($members->first()->teacher->user)
        ->post(route('defense-reviews.store', $act), [
            'decision' => PreDefenseDecision::NoObservations->value,
            'schedule_revision' => 999,
        ])
        ->assertRedirect(route('defense-reviews.index'))
        ->assertInertiaFlash('toast.type', 'error')
        ->assertInertiaFlash(
            'toast.message',
            'La programación cambió desde que abrió el formulario. Consulte las pre-defensas pendientes.',
        );

    expect($act->reviews()->count())->toBe(0)
        ->and(Notification::query()->count())->toBe(0)
        ->and($process->fresh()->phase)->toBe(Phase::PreDefense);
});

test('UC-010 A2 and BR-004: registering the absence redirects and abandons the process', function () {
    [$process, $act, $members] = uc010HttpProcess();

    $this->actingAs($members->first()->teacher->user)
        ->post(route('defense-reviews.store', $act), [
            'decision' => PreDefenseDecision::Absence->value,
            'schedule_revision' => $act->schedule_revision,
        ])
        ->assertRedirect(route('defense-reviews.index'))
        ->assertInertiaFlash(
            'toast.message',
            'Se registró la inasistencia del estudiante. El trámite de titulación quedó abandonado.',
        );

    expect($act->fresh()->is_student_absent)->toBeTrue()
        ->and($process->fresh()->abandoned_at)->not->toBeNull()
        ->and($act->reviews()->count())->toBe(0)
        ->and(Notification::query()->count())->toBe(4);
});
