<?php

use App\Actions\Notifications\NotifyUser;
use App\Enums\ProjectVersionStatus;
use App\Models\GraduationProcess;
use App\Models\Notification;
use App\Models\ProjectVersion;
use App\Models\Teacher;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

function careerDirectorUser(): User
{
    return Teacher::factory()->careerDirector()->create()->user;
}

function designate(User $actor, Teacher $teacher)
{
    return test()->actingAs($actor)->put(route('workshop-teacher.update'), ['teacher_id' => $teacher->id]);
}

test('UC-003 precondition: a user without the permission cannot open or use the page', function () {
    $teacher = Teacher::factory()->create();

    $this->actingAs($teacher->user)->get(route('workshop-teacher.edit'))->assertForbidden();
    designate($teacher->user, $teacher)->assertForbidden();
    expect($teacher->fresh()->is_workshop_teacher)->toBeFalse();
});

test('UC-003 precondition: a guest is sent to log in', function () {
    $this->get(route('workshop-teacher.edit'))->assertRedirect(route('login'));
});

test('UC-003 step 2: shows the current workshop teacher and the other active teachers', function () {
    $current = Teacher::factory()->workshopTeacher()->create();
    $active = Teacher::factory()->create();
    $inactive = Teacher::factory()->inactive()->create();

    $this->actingAs(careerDirectorUser())
        ->get(route('workshop-teacher.edit'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('workshop-teacher/edit')
            ->where('current.id', $current->id)
            ->where('candidates', fn ($candidates) => collect($candidates)->pluck('id')->contains($active->id)
                && ! collect($candidates)->pluck('id')->contains($inactive->id)
                && ! collect($candidates)->pluck('id')->contains($current->id)));
});

test('UC-003 step 4 BR-003: the preview counts the profiles of the current teacher and the unassigned ones', function () {
    $current = Teacher::factory()->workshopTeacher()->create();
    ProjectVersion::factory()->count(2)->profileInReview($current)->create();
    ProjectVersion::factory()->profileInReview()->create();
    ProjectVersion::factory()->profileInReview(Teacher::factory()->create())->create();
    ProjectVersion::factory()->profileApproved($current)->create();

    $this->actingAs(careerDirectorUser())
        ->get(route('workshop-teacher.edit'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('profilesInReview', 3));
});

test('UC-003 step 4 A1: the preview of the first designation counts the unassigned profiles', function () {
    ProjectVersion::factory()->count(2)->profileInReview()->create();
    ProjectVersion::factory()->profileInReview(Teacher::factory()->create())->create();

    $this->actingAs(careerDirectorUser())
        ->get(route('workshop-teacher.edit'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('profilesInReview', 2));
});

test('UC-003 step 2 A1: with no workshop teacher the current one is empty', function () {
    $this->actingAs(careerDirectorUser())
        ->get(route('workshop-teacher.edit'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('current', null));
});

test('UC-003 step 5 BR-001: designates the chosen teacher and removes the designation from the previous one', function () {
    $previous = Teacher::factory()->workshopTeacher()->create();
    $new = Teacher::factory()->create();

    designate(careerDirectorUser(), $new)->assertRedirect(route('workshop-teacher.edit'));

    expect($new->fresh()->is_workshop_teacher)->toBeTrue()
        ->and($previous->fresh()->is_workshop_teacher)->toBeFalse()
        ->and(Teacher::query()->workshopTeacher()->count())->toBe(1);
});

test('UC-003 step 6 BR-003: profiles in review of the previous teacher pass to the new one', function () {
    $previous = Teacher::factory()->workshopTeacher()->create();
    $new = Teacher::factory()->create();
    $inReview = ProjectVersion::factory()->count(2)->profileInReview($previous)->create();

    designate(careerDirectorUser(), $new);

    expect($inReview->every(fn (ProjectVersion $version) => $version->fresh()->reviewer_id === $new->id))->toBeTrue();
});

test('UC-003 step 6 BR-003: approved and rejected profiles keep their reviewer', function () {
    $previous = Teacher::factory()->workshopTeacher()->create();
    $new = Teacher::factory()->create();
    $approved = ProjectVersion::factory()->profileApproved($previous)->create();
    $rejected = ProjectVersion::factory()->profileRejected($previous)->create();

    designate(careerDirectorUser(), $new);

    expect($approved->fresh()->reviewer_id)->toBe($previous->id)
        ->and($approved->fresh()->status)->toBe(ProjectVersionStatus::Approved)
        ->and($rejected->fresh()->reviewer_id)->toBe($previous->id);
});

test('UC-003 step 6 BR-003: profiles in review of another reviewer are not touched', function () {
    Teacher::factory()->workshopTeacher()->create();
    $other = Teacher::factory()->create();
    $new = Teacher::factory()->create();
    $version = ProjectVersion::factory()->profileInReview($other)->create();

    designate(careerDirectorUser(), $new);

    expect($version->fresh()->reviewer_id)->toBe($other->id);
});

test('UC-003 step 7: notifies the new teacher and the previous one with the texts of OQ-034', function () {
    $previous = Teacher::factory()->workshopTeacher()->create();
    $new = Teacher::factory()->create();
    ProjectVersion::factory()->count(2)->profileInReview($previous)->create();

    designate(careerDirectorUser(), $new);

    expect(Notification::query()->where('user_id', $new->user_id)->pluck('message')->all())
        ->toBe(['Se le designó como docente de taller de grado; recibe 2 perfiles de proyecto para revisar.'])
        ->and(Notification::query()->where('user_id', $previous->user_id)->pluck('message')->all())
        ->toBe(["Dejó de ser docente de taller de grado; sus perfiles de proyecto en revisión pasaron a {$new->user->name}."]);
});

test('UC-003 step 8: confirms the designated teacher and the number of profiles', function () {
    $previous = Teacher::factory()->workshopTeacher()->create();
    $new = Teacher::factory()->create();
    ProjectVersion::factory()->count(3)->profileInReview($previous)->create();

    designate(careerDirectorUser(), $new)->assertInertiaFlash('toast', [
        'type' => 'success',
        'message' => "{$new->user->name} es el docente de taller de grado; 3 perfiles de proyecto pasaron a su revisión.",
    ]);
});

test('UC-003 A1: the first designation gives the teacher the profiles in review without reviewer', function () {
    $new = Teacher::factory()->create();
    $waiting = ProjectVersion::factory()->count(2)->profileInReview()->create();
    $approved = ProjectVersion::factory()->profileApproved(Teacher::factory()->create())->create();

    designate(careerDirectorUser(), $new)->assertRedirect(route('workshop-teacher.edit'));

    expect($new->fresh()->is_workshop_teacher)->toBeTrue()
        ->and($waiting->every(fn (ProjectVersion $version) => $version->fresh()->reviewer_id === $new->id))->toBeTrue()
        ->and($approved->fresh()->reviewer_id)->not->toBe($new->id)
        ->and(Notification::query()->where('user_id', $new->user_id)->pluck('message')->all())
        ->toBe(['Se le designó como docente de taller de grado; recibe 2 perfiles de proyecto para revisar.'])
        ->and(Notification::query()->count())->toBe(1);
});

test('UC-003 A2: without confirming (no request) nothing changes', function () {
    $previous = Teacher::factory()->workshopTeacher()->create();
    Teacher::factory()->create();
    $version = ProjectVersion::factory()->profileInReview($previous)->create();

    $this->actingAs(careerDirectorUser())->get(route('workshop-teacher.edit'))->assertOk();

    expect($previous->fresh()->is_workshop_teacher)->toBeTrue()
        ->and($version->fresh()->reviewer_id)->toBe($previous->id)
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-003 BR-002: an inactive teacher cannot be designated', function () {
    $previous = Teacher::factory()->workshopTeacher()->create();
    $inactive = Teacher::factory()->inactive()->create();

    designate(careerDirectorUser(), $inactive)->assertSessionHasErrors([
        'teacher_id' => 'El docente elegido no puede ser designado como docente de taller de grado.',
    ]);

    expect($previous->fresh()->is_workshop_teacher)->toBeTrue()
        ->and($inactive->fresh()->is_workshop_teacher)->toBeFalse();
});

test('UC-003 BR-002: the current workshop teacher cannot be designated again', function () {
    $current = Teacher::factory()->workshopTeacher()->create();

    designate(careerDirectorUser(), $current)->assertSessionHasErrors([
        'teacher_id' => 'El docente elegido no puede ser designado como docente de taller de grado.',
    ]);

    expect(Notification::query()->count())->toBe(0);
});

test('UC-003 BR-002: the teacher is required', function () {
    $this->actingAs(careerDirectorUser())
        ->put(route('workshop-teacher.update'), [])
        ->assertSessionHasErrors(['teacher_id' => 'Elija al docente de taller de grado.']);
});

test('UC-003 BR-002: a teacher who tutors processes in progress can be designated and keeps the tutorships', function () {
    $tutor = Teacher::factory()->create();
    $process = GraduationProcess::factory()->withTutor($tutor)->create();

    designate(careerDirectorUser(), $tutor)->assertSessionHasNoErrors();

    expect($tutor->fresh()->is_workshop_teacher)->toBeTrue()
        ->and($process->fresh()->tutor_id)->toBe($tutor->id);
});

test('UC-003 BR-001: the designation of the new teacher and the removal from the previous one are atomic', function () {
    $previous = Teacher::factory()->workshopTeacher()->create();
    $new = Teacher::factory()->create();
    $version = ProjectVersion::factory()->profileInReview($previous)->create();
    app()->bind(NotifyUser::class, fn () => new class extends NotifyUser
    {
        public function handle(User $recipient, string $message, ?GraduationProcess $process = null): Notification
        {
            throw new RuntimeException('fallo');
        }
    });

    $this->withoutExceptionHandling();
    expect(fn () => designate(careerDirectorUser(), $new))->toThrow(RuntimeException::class);

    expect($previous->fresh()->is_workshop_teacher)->toBeTrue()
        ->and($new->fresh()->is_workshop_teacher)->toBeFalse()
        ->and($version->fresh()->reviewer_id)->toBe($previous->id);
});
