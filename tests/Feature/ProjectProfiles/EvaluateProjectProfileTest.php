<?php

use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Enums\RoleName;
use App\Models\GraduationProcess;
use App\Models\Notification;
use App\Models\Observation;
use App\Models\ProjectVersion;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->reviewer = Teacher::factory()->workshopTeacher()->create();
    $this->reviewerUser = $this->reviewer->user;
    $this->process = GraduationProcess::factory()->inPhase(Phase::ProfileEvaluation)->create();
    $this->profile = ProjectVersion::factory()->for($this->process)->profileInReview($this->reviewer)->create([
        'title' => 'Plataforma de gestión de titulación',
    ]);
});

test('UC-005 NFR-005: a guest is sent to login and a user without the permission cannot open the pages', function () {
    $student = $this->process->student->user;

    $this->get(route('project-profiles.index'))->assertRedirect(route('login'));
    $this->actingAs($student)->get(route('project-profiles.index'))->assertForbidden();
    $this->actingAs($student)->get(route('project-profiles.show', $this->profile))->assertForbidden();
    $this->actingAs($student)
        ->put(route('project-profiles.update', $this->profile), ['decision' => 'aprobar'])
        ->assertForbidden();
});

test('UC-005 step 2: the list shows only the profiles in review of the reviewer, the oldest first', function () {
    $this->profile->update(['submitted_at' => now()->subDays(2)]);
    $older = ProjectVersion::factory()->profileInReview($this->reviewer)->create(['submitted_at' => now()->subDays(9)]);
    ProjectVersion::factory()->profileInReview(Teacher::factory()->create())->create();
    ProjectVersion::factory()->profileApproved($this->reviewer)->create();
    ProjectVersion::factory()->profileRejected($this->reviewer)->create();
    ProjectVersion::factory()->profileInReview()->create();

    $this->actingAs($this->reviewerUser)
        ->get(route('project-profiles.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('project-profiles/index')
            ->has('profiles', 2)
            ->where('profiles.0.id', $older->id)
            ->where('profiles.0.student', $older->graduationProcess->student->user->name)
            ->where('profiles.1.id', $this->profile->id)
            ->where('profiles.1.title', 'Plataforma de gestión de titulación')
            ->has('profiles.1.submitted_at'));
});

test('UC-005 A3: without profiles in review the list is empty', function () {
    $this->profile->update(['status' => ProjectVersionStatus::Approved, 'evaluated_at' => now()]);

    $this->actingAs($this->reviewerUser)
        ->get(route('project-profiles.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('project-profiles/index')->has('profiles', 0));
});

test('UC-005 step 4 BR-005: the profile shows the earlier rejected profiles of the process with their observations', function () {
    $this->profile->update(['number' => 2]);
    $rejected = ProjectVersion::factory()->for($this->process)->profileRejected($this->reviewer)->create(['number' => 1]);
    $observation = Observation::factory()->forVersion($rejected)->create([
        'author_id' => $this->reviewerUser->id,
        'body' => 'Falta delimitar el alcance.',
    ]);
    $otherProcessRejected = ProjectVersion::factory()->profileRejected($this->reviewer)->create();
    Observation::factory()->forVersion($otherProcessRejected)->create();

    $this->actingAs($this->reviewerUser)
        ->get(route('project-profiles.show', $this->profile))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('project-profiles/show')
            ->where('profile.id', $this->profile->id)
            ->where('profile.student', $this->process->student->user->name)
            ->where('profile.number', 2)
            ->has('previousProfiles', 1)
            ->where('previousProfiles.0.id', $rejected->id)
            ->where('previousProfiles.0.observations.0.id', $observation->id)
            ->where('previousProfiles.0.observations.0.author', $this->reviewerUser->name)
            ->where('previousProfiles.0.observations.0.body', 'Falta delimitar el alcance.'));
});

test('UC-005 steps 5-9: approving without observations takes the title, keeps the phase and notifies the student', function () {
    $this->actingAs($this->reviewerUser)
        ->put(route('project-profiles.update', $this->profile), ['decision' => 'aprobar'])
        ->assertRedirect(route('project-profiles.index'))
        ->assertInertiaFlash('toast.message', 'Perfil de proyecto aprobado.');

    $this->profile->refresh();
    expect($this->profile->status)->toBe(ProjectVersionStatus::Approved)
        ->and($this->profile->evaluated_at)->not->toBeNull()
        ->and($this->profile->evaluated_at->gt($this->profile->submitted_at))->toBeTrue()
        ->and($this->profile->observations()->count())->toBe(0);

    $this->process->refresh();
    expect($this->process->project_title)->toBe('Plataforma de gestión de titulación')
        ->and($this->process->phase)->toBe(Phase::ProfileEvaluation);

    $notification = Notification::query()->where('user_id', $this->process->student->user_id)->sole();
    expect($notification->message)->toBe('Su perfil de proyecto (versión 1) fue aprobado.')
        ->and($notification->graduation_process_id)->toBe($this->process->id);
});

test('UC-005 step 5 BR-002: approving with observations records each one with its author', function () {
    $this->actingAs($this->reviewerUser)
        ->put(route('project-profiles.update', $this->profile), [
            'decision' => 'aprobar',
            'observations' => ['Revisar la bibliografía.', '', '  ', 'Citar normas APA.'],
        ])
        ->assertRedirect(route('project-profiles.index'));

    $observations = $this->profile->observations()->get();
    expect($observations->pluck('body')->all())->toBe(['Revisar la bibliografía.', 'Citar normas APA.'])
        ->and($observations->pluck('author_id')->unique()->all())->toBe([$this->reviewerUser->id])
        ->and($this->profile->refresh()->status)->toBe(ProjectVersionStatus::Approved);
});

test('UC-005 A1: rejecting with observations records them and notifies the student', function () {
    $this->actingAs($this->reviewerUser)
        ->put(route('project-profiles.update', $this->profile), [
            'decision' => 'rechazar',
            'observations' => ['El alcance es demasiado amplio.', 'No hay objetivos medibles.'],
        ])
        ->assertRedirect(route('project-profiles.index'))
        ->assertInertiaFlash('toast.message', 'Perfil de proyecto rechazado.');

    $this->profile->refresh();
    expect($this->profile->status)->toBe(ProjectVersionStatus::Rejected)
        ->and($this->profile->evaluated_at)->not->toBeNull()
        ->and($this->profile->observations()->count())->toBe(2)
        ->and($this->process->refresh()->project_title)->not->toBe('Plataforma de gestión de titulación');

    expect(Notification::query()->where('user_id', $this->process->student->user_id)->sole()->message)
        ->toBe('Su perfil de proyecto (versión 1) fue rechazado. Puede presentar uno nuevo con las observaciones subsanadas.');
});

test('UC-005 A2: rejecting without observations is refused, changes nothing and keeps what was entered', function () {
    $this->actingAs($this->reviewerUser)
        ->from(route('project-profiles.show', $this->profile))
        ->put(route('project-profiles.update', $this->profile), ['decision' => 'rechazar', 'observations' => ['  ', '']])
        ->assertRedirect(route('project-profiles.show', $this->profile))
        ->assertSessionHasErrors(['observations' => 'El rechazo necesita al menos una observación.'])
        ->assertSessionHasInput('decision', 'rechazar');

    expect($this->profile->refresh()->status)->toBe(ProjectVersionStatus::InReview)
        ->and($this->profile->evaluated_at)->toBeNull()
        ->and(Observation::query()->count())->toBe(0)
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-005 step 5: the decision is required and an observation has at most 2000 characters', function () {
    $this->actingAs($this->reviewerUser)
        ->put(route('project-profiles.update', $this->profile), [])
        ->assertSessionHasErrors('decision');

    $this->actingAs($this->reviewerUser)
        ->put(route('project-profiles.update', $this->profile), [
            'decision' => 'aprobar',
            'observations' => [str_repeat('a', 2001)],
        ])
        ->assertSessionHasErrors('observations.0');

    expect($this->profile->refresh()->status)->toBe(ProjectVersionStatus::InReview);
});

test('UC-005 A4: leaving without confirming changes nothing', function () {
    $this->actingAs($this->reviewerUser)->get(route('project-profiles.show', $this->profile))->assertOk();

    expect($this->profile->refresh()->status)->toBe(ProjectVersionStatus::InReview)
        ->and(Observation::query()->count())->toBe(0)
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-005 BR-001: only the reviewer of the profile evaluates it', function () {
    $otherTeacher = Teacher::factory()->create();
    $director = Teacher::factory()->careerDirector()->create();
    $formerWorkshopTeacher = Teacher::factory()->create();
    ProjectVersion::factory()->profileInReview($formerWorkshopTeacher)->create();
    $users = [
        $otherTeacher->user,
        $director->user,
        $formerWorkshopTeacher->user,
        User::factory()->withRoles(RoleName::Student)->create(),
    ];

    foreach ($users as $user) {
        $this->actingAs($user)
            ->put(route('project-profiles.update', $this->profile), ['decision' => 'aprobar'])
            ->assertForbidden();
        $this->actingAs($user)->get(route('project-profiles.show', $this->profile))->assertForbidden();
    }

    expect($this->profile->refresh()->status)->toBe(ProjectVersionStatus::InReview);
});

test('UC-005 BR-003: an evaluated profile does not change its result', function () {
    $this->actingAs($this->reviewerUser)
        ->put(route('project-profiles.update', $this->profile), ['decision' => 'aprobar'])
        ->assertRedirect(route('project-profiles.index'));
    $evaluatedAt = $this->profile->refresh()->evaluated_at;

    $this->actingAs($this->reviewerUser)
        ->from(route('project-profiles.show', $this->profile))
        ->put(route('project-profiles.update', $this->profile), [
            'decision' => 'rechazar',
            'observations' => ['Cambio de opinión.'],
        ])
        ->assertSessionHasErrors(['project_version' => 'El perfil de proyecto ya fue evaluado.']);

    $this->actingAs($this->reviewerUser)
        ->get(route('project-profiles.show', $this->profile))
        ->assertRedirect(route('project-profiles.index'))
        ->assertInertiaFlash('toast.message', 'El perfil de proyecto ya fue evaluado.');

    expect($this->profile->refresh()->status)->toBe(ProjectVersionStatus::Approved)
        ->and($this->profile->evaluated_at->equalTo($evaluatedAt))->toBeTrue()
        ->and(Observation::query()->count())->toBe(0);
});

test('UC-005 BR-004: approving keeps the process in profile evaluation with a single approved profile', function () {
    $this->profile->update(['number' => 2]);
    $previous = ProjectVersion::factory()->for($this->process)->profileRejected($this->reviewer)->create(['number' => 1]);

    $this->actingAs($this->reviewerUser)
        ->put(route('project-profiles.update', $this->profile), ['decision' => 'aprobar'])
        ->assertRedirect(route('project-profiles.index'));

    $profiles = $this->process->projectVersions()->profiles();
    expect($profiles->clone()->where('status', ProjectVersionStatus::Approved)->count())->toBe(1)
        ->and($previous->refresh()->status)->toBe(ProjectVersionStatus::Rejected)
        ->and($this->process->refresh()->phase)->toBe(Phase::ProfileEvaluation);
});

test('UC-005 BR-004: approving a profile when the process already has an approved one is refused and changes nothing', function () {
    $this->profile->update(['number' => 2]);
    ProjectVersion::factory()->for($this->process)->profileApproved($this->reviewer)->create(['number' => 1]);

    $this->actingAs($this->reviewerUser)
        ->from(route('project-profiles.show', $this->profile))
        ->put(route('project-profiles.update', $this->profile), [
            'decision' => 'aprobar',
            'observations' => ['Observación.'],
        ])
        ->assertSessionHasErrors(['project_version' => 'El trámite de titulación ya tiene un perfil de proyecto aprobado.']);

    expect($this->profile->refresh()->status)->toBe(ProjectVersionStatus::InReview)
        ->and($this->profile->evaluated_at)->toBeNull()
        ->and(Observation::query()->count())->toBe(0)
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-005 BR-001: a teacher who is not the reviewer gets 403 before validation, with the policy message', function () {
    $otherTeacher = Teacher::factory()->create();

    $this->actingAs($otherTeacher->user)
        ->put(route('project-profiles.update', $this->profile), ['decision' => 'rechazar', 'observations' => [str_repeat('a', 2001)]])
        ->assertForbidden()
        ->assertSee('Solo el revisor del perfil de proyecto puede evaluarlo.');

    expect($this->profile->refresh()->status)->toBe(ProjectVersionStatus::InReview);
});

test('UC-005 step 4: the reviewer downloads the profile with its original file name', function () {
    Storage::fake('local');
    Storage::disk('local')->put($this->profile->file_path, 'contenido del perfil');
    $this->profile->update(['original_file_name' => 'perfil.pdf']);

    $this->actingAs($this->reviewerUser)
        ->get(route('project-profiles.download', $this->profile))
        ->assertOk()
        ->assertDownload('perfil.pdf');
});

test('UC-005 BR-005 BR-001: download is allowed for earlier rejected profiles to the reviewer and denied to others', function () {
    Storage::fake('local');
    $this->profile->update(['number' => 2]);
    $rejected = ProjectVersion::factory()->for($this->process)->profileRejected($this->reviewer)->create(['number' => 1]);
    Storage::disk('local')->put($rejected->file_path, 'anterior');
    Storage::disk('local')->put($this->profile->file_path, 'actual');
    $otherTeacher = Teacher::factory()->create();

    $this->actingAs($this->reviewerUser)->get(route('project-profiles.download', $rejected))->assertOk();
    $this->actingAs($otherTeacher->user)->get(route('project-profiles.download', $rejected))->assertForbidden();
    $this->actingAs($otherTeacher->user)->get(route('project-profiles.download', $this->profile))->assertForbidden();
    $this->actingAs($this->process->student->user)->get(route('project-profiles.download', $this->profile))->assertForbidden();
});

test('UC-005 BR-005: a reviewer without a profile in review of the process cannot download its rejected profiles', function () {
    Storage::fake('local');
    $this->profile->update(['status' => ProjectVersionStatus::Approved, 'evaluated_at' => now(), 'number' => 2]);
    $formerReviewer = Teacher::factory()->create();
    $rejected = ProjectVersion::factory()->for($this->process)->profileRejected($formerReviewer)->create(['number' => 1]);
    Storage::disk('local')->put($rejected->file_path, 'anterior');

    $this->actingAs($this->reviewerUser)->get(route('project-profiles.download', $rejected))->assertForbidden();
});

test('UC-005 OQ-056: the menu option shows only to the workshop teacher', function () {
    $otherTeacher = Teacher::factory()->create();

    $this->actingAs($this->reviewerUser)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where(
            'menu',
            fn ($menu) => collect($menu)->pluck('href')->contains(route('project-profiles.index', absolute: false)),
        ));

    $this->actingAs($otherTeacher->user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where(
            'menu',
            fn ($menu) => ! collect($menu)->pluck('href')->contains(route('project-profiles.index', absolute: false)),
        ));
});
