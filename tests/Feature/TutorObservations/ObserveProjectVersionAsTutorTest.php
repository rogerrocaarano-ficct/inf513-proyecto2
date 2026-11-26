<?php

use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\GraduationProcess;
use App\Models\Notification;
use App\Models\Observation;
use App\Models\ProjectVersion;
use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->tutor = Teacher::factory()->create();
    $this->tutorUser = $this->tutor->user;
    $this->process = GraduationProcess::factory()->withTutor($this->tutor)->create();
    $this->version = ProjectVersion::factory()->for($this->process)->profileInReview()->create(['number' => 1]);
});

test('UC-007 NFR-005: a guest is sent to login and a user without the permission cannot open the pages', function () {
    $student = $this->process->student->user;

    $this->get(route('tutor-observations.index'))->assertRedirect(route('login'));
    $this->actingAs($student)->get(route('tutor-observations.index'))->assertForbidden();
    $this->actingAs($student)->get(route('tutor-observations.show', $this->process))->assertForbidden();
    $this->actingAs($student)->get(route('tutor-observations.version', $this->version))->assertForbidden();
    $this->actingAs($student)->get(route('tutor-observations.download', $this->version))->assertForbidden();
    $this->actingAs($student)
        ->post(route('tutor-observations.store', $this->version), ['observations' => ['Texto.']])
        ->assertForbidden();

    expect(Observation::query()->count())->toBe(0);
});

test('UC-007 steps 1-2: the list shows only the processes in progress of the tutor with the unobserved first', function () {
    $this->process->update(['started_at' => now()->subMonths(3)]);
    Observation::factory()->forVersion($this->version)->create(['author_id' => $this->tutorUser->id]);
    $unobservedProcess = GraduationProcess::factory()->withTutor($this->tutor)->create(['started_at' => now()->subMonth()]);
    $lastVersion = ProjectVersion::factory()->for($unobservedProcess)->profileInReview()->create(['number' => 2]);
    ProjectVersion::factory()->for($unobservedProcess)->profileRejected()->create(['number' => 1]);
    GraduationProcess::factory()->withTutor(Teacher::factory()->create())->create();
    GraduationProcess::factory()->withTutor($this->tutor)->concluded()->create();
    GraduationProcess::factory()->withTutor($this->tutor)->abandoned()->create();

    $this->actingAs($this->tutorUser)
        ->get(route('tutor-observations.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('tutor-observations/index')
            ->has('processes', 2)
            ->where('processes.0.id', $unobservedProcess->id)
            ->where('processes.0.student', $unobservedProcess->student->user->name)
            ->where('processes.0.phase', Phase::ProfileEvaluation->value)
            ->where('processes.0.has_unobserved_version', true)
            ->where('processes.0.last_version.number', 2)
            ->where('processes.0.last_version.status', $lastVersion->status->value)
            ->where('processes.1.id', $this->process->id)
            ->where('processes.1.has_unobserved_version', false));
});

test('UC-007 A2: a tutor without processes in progress gets an empty list', function () {
    $other = Teacher::factory()->create();

    $this->actingAs($other->user)
        ->get(route('tutor-observations.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('tutor-observations/index')->has('processes', 0));
});

test('UC-007 step 3: the process shows its version history, the latest first', function () {
    $second = ProjectVersion::factory()->for($this->process)->profileRejected()->create(['number' => 2]);
    Observation::factory()->forVersion($this->version)->count(2)->create();
    Observation::factory()->forVersion($this->version)->create(['author_id' => $this->tutorUser->id]);

    $this->actingAs($this->tutorUser)
        ->get(route('tutor-observations.show', $this->process))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('tutor-observations/show')
            ->where('process.student', $this->process->student->user->name)
            ->has('versions', 2)
            ->where('versions.0.id', $second->id)
            ->where('versions.0.observed_by_me', false)
            ->where('versions.1.id', $this->version->id)
            ->where('versions.1.observations_count', 3)
            ->where('versions.1.observed_by_me', true));
});

test('UC-007 step 4: the version shows its phase, status and observations with their authors', function () {
    $reviewerObservation = Observation::factory()->forVersion($this->version)->create(['body' => 'Falta el alcance.']);

    $this->actingAs($this->tutorUser)
        ->get(route('tutor-observations.version', $this->version))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('tutor-observations/version')
            ->where('version.id', $this->version->id)
            ->where('version.phase', Phase::ProfileEvaluation->value)
            ->where('version.status', ProjectVersionStatus::InReview->value)
            ->where('version.file_name', $this->version->original_file_name)
            ->has('observations', 1)
            ->where('observations.0.author', $reviewerObservation->author->name)
            ->where('observations.0.body', 'Falta el alcance.'));
});

test('UC-007 steps 5-9: the observations are recorded with their author, notify the student and keep the status', function () {
    $this->actingAs($this->tutorUser)
        ->post(route('tutor-observations.store', $this->version), [
            'observations' => ['  Revisar la bibliografía. ', 'Citar normas APA.'],
        ])
        ->assertRedirect(route('tutor-observations.version', $this->version))
        ->assertInertiaFlash('toast.message', 'Observaciones registradas.');

    $observations = $this->version->observations()->get();
    expect($observations->pluck('body')->all())->toBe(['Revisar la bibliografía.', 'Citar normas APA.'])
        ->and($observations->pluck('author_id')->unique()->all())->toBe([$this->tutorUser->id])
        ->and($observations->every(fn (Observation $observation): bool => $observation->recorded_at !== null))->toBeTrue()
        ->and($this->version->refresh()->status)->toBe(ProjectVersionStatus::InReview);

    $notification = Notification::query()->sole();
    expect($notification->user_id)->toBe($this->process->student->user_id)
        ->and($notification->message)->toBe('Su versión 1 del proyecto recibió observaciones de su tutor.')
        ->and($notification->graduation_process_id)->toBe($this->process->id);
});

test('UC-007 BR-002 BR-003: the tutor observes versions of every phase without changing their status', function () {
    $process = GraduationProcess::factory()->withCommittee()->create(['tutor_id' => $this->tutor->id]);
    $preDefense = ProjectVersion::factory()->for($process)->create(['number' => 1, 'phase' => Phase::PreDefense]);
    $formalDefense = ProjectVersion::factory()->for($process)->create([
        'number' => 2,
        'phase' => Phase::FormalDefense,
        'status' => ProjectVersionStatus::Submitted,
    ]);
    $approvedProfile = ProjectVersion::factory()->for($this->process)->profileApproved()->create(['number' => 2]);

    foreach ([$preDefense, $formalDefense, $approvedProfile, $this->version] as $version) {
        $status = $version->status;

        $this->actingAs($this->tutorUser)
            ->post(route('tutor-observations.store', $version), ['observations' => ['Orientación.']])
            ->assertRedirect(route('tutor-observations.version', $version));

        expect($version->observations()->count())->toBe(1)
            ->and($version->refresh()->status)->toBe($status);
    }
});

test('UC-007 A1: an empty or too long observation is flagged in Spanish and nothing is recorded', function () {
    $this->actingAs($this->tutorUser)
        ->from(route('tutor-observations.version', $this->version))
        ->post(route('tutor-observations.store', $this->version), ['observations' => ['Válida.', '  ']])
        ->assertRedirect(route('tutor-observations.version', $this->version))
        ->assertSessionHasErrors(['observations.1' => 'La observación no puede estar vacía.'])
        ->assertSessionHasInput('observations.0', 'Válida.');

    $this->actingAs($this->tutorUser)
        ->post(route('tutor-observations.store', $this->version), ['observations' => [str_repeat('a', 2001)]])
        ->assertSessionHasErrors(['observations.0' => 'Cada observación admite como máximo 2000 caracteres.']);

    $this->actingAs($this->tutorUser)
        ->post(route('tutor-observations.store', $this->version), [])
        ->assertSessionHasErrors(['observations' => 'Escriba al menos una observación.']);

    expect(Observation::query()->count())->toBe(0)
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-007 BR-004: an observation of exactly 2000 characters is recorded and a new one is added, never replaced', function () {
    $this->actingAs($this->tutorUser)
        ->post(route('tutor-observations.store', $this->version), ['observations' => [str_repeat('a', 2000)]])
        ->assertSessionHasNoErrors();
    $this->actingAs($this->tutorUser)
        ->post(route('tutor-observations.store', $this->version), ['observations' => ['Precisión posterior.']])
        ->assertSessionHasNoErrors();

    expect($this->version->observations()->pluck('body')->map(fn (string $body): int => strlen($body))->all())
        ->toBe([2000, strlen('Precisión posterior.')]);
});

test('UC-007 A3: consulting a version without confirming registers nothing', function () {
    $this->actingAs($this->tutorUser)->get(route('tutor-observations.version', $this->version))->assertOk();

    expect(Observation::query()->count())->toBe(0)
        ->and(Notification::query()->count())->toBe(0)
        ->and($this->version->refresh()->status)->toBe(ProjectVersionStatus::InReview);
});

test('UC-007 BR-001: a teacher who is not the tutor cannot open the process or the version and registers nothing', function () {
    $otherTeacher = Teacher::factory()->create();

    $this->actingAs($otherTeacher->user)->get(route('tutor-observations.show', $this->process))->assertForbidden();
    $this->actingAs($otherTeacher->user)->get(route('tutor-observations.version', $this->version))->assertForbidden();
    $this->actingAs($otherTeacher->user)
        ->post(route('tutor-observations.store', $this->version), ['observations' => ['Texto.']])
        ->assertSessionHasErrors(['project_version' => 'Ya no es el tutor vigente de este trámite de titulación.']);

    expect(Observation::query()->count())->toBe(0)
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-007 A4: a former tutor is told the reason and nothing is recorded or notified', function () {
    $this->process->update(['tutor_id' => Teacher::factory()->create()->id]);

    $this->actingAs($this->tutorUser)
        ->from(route('tutor-observations.version', $this->version))
        ->post(route('tutor-observations.store', $this->version), ['observations' => ['Texto.']])
        ->assertRedirect(route('tutor-observations.version', $this->version))
        ->assertSessionHasErrors(['project_version' => 'Ya no es el tutor vigente de este trámite de titulación.']);

    expect(Observation::query()->count())->toBe(0)
        ->and(Notification::query()->count())->toBe(0)
        ->and($this->version->refresh()->status)->toBe(ProjectVersionStatus::InReview);
});

test('UC-007 A4 BR-001: a process no longer in progress cannot be observed', function () {
    $this->process->update(['concluded_at' => now()]);

    $this->actingAs($this->tutorUser)->get(route('tutor-observations.version', $this->version))->assertForbidden();
    $this->actingAs($this->tutorUser)
        ->post(route('tutor-observations.store', $this->version), ['observations' => ['Texto.']])
        ->assertSessionHasErrors(['project_version' => 'El trámite de titulación ya no está en curso.']);

    $this->process->update(['concluded_at' => null, 'abandoned_at' => now()]);

    $this->actingAs($this->tutorUser)
        ->post(route('tutor-observations.store', $this->version), ['observations' => ['Texto.']])
        ->assertSessionHasErrors(['project_version' => 'El trámite de titulación ya no está en curso.']);

    expect(Observation::query()->count())->toBe(0)
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-007 step 4 BR-001: the tutor downloads the version with its original file name and others cannot', function () {
    Storage::fake('local');
    Storage::disk('local')->put($this->version->file_path, 'contenido');
    $this->version->update(['original_file_name' => 'avance.pdf']);

    $this->actingAs($this->tutorUser)
        ->get(route('tutor-observations.download', $this->version))
        ->assertOk()
        ->assertDownload('avance.pdf');

    $this->actingAs(Teacher::factory()->create()->user)
        ->get(route('tutor-observations.download', $this->version))
        ->assertForbidden();
    $this->actingAs($this->process->student->user)
        ->get(route('tutor-observations.download', $this->version))
        ->assertForbidden();
});

test('UC-007 FR-029: the menu option shows to teachers and not to students', function () {
    $href = route('tutor-observations.index', absolute: false);

    $this->actingAs($this->tutorUser)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('menu', fn ($menu) => collect($menu)->pluck('href')->contains($href)));

    $this->actingAs($this->process->student->user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('menu', fn ($menu) => ! collect($menu)->pluck('href')->contains($href)));
});
