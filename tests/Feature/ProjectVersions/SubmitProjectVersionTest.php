<?php

use App\Actions\Notifications\NotifyUser;
use App\Enums\Phase;
use App\Enums\ProjectVersionStatus;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\Notification;
use App\Models\Observation;
use App\Models\ProjectVersion;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->student = Student::factory()->create();
    $this->studentUser = $this->student->user;
});

/**
 * A PDF file to attach to the version.
 */
function uc004Pdf(string $name = 'perfil.pdf', int $kilobytes = 200): UploadedFile
{
    return UploadedFile::fake()->create($name, $kilobytes, 'application/pdf');
}

/**
 * The process of the student in the given phase; committee members exist from pre-defense on.
 */
function uc004Process(Student $student, Phase $phase, ?string $title = null): GraduationProcess
{
    $factory = GraduationProcess::factory()->for($student);

    $factory = match ($phase) {
        Phase::PreDefense => $factory->withCommittee(),
        Phase::FormalDefense => $factory->withCommittee()->inPhase(Phase::FormalDefense),
        default => $factory->inPhase($phase),
    };

    return $factory->create($title === null ? [] : ['project_title' => $title]);
}

/**
 * A held pre-defense of the process, with one observation of a committee member when asked.
 */
function uc004HeldPreDefense(GraduationProcess $process, bool $withObservation): Defense
{
    $defense = Defense::factory()->for($process)->held()->create(['scheduled_at' => now()->subDays(3)]);

    if ($withObservation) {
        Observation::factory()->forDefense($defense)->create();
    }

    return $defense;
}

/**
 * The next page the student opens shows the confirmation as a toast.
 */
function uc004AssertToast($testCase, string $message): void
{
    $testCase->get(route('project-versions.create'))->assertInertia(fn ($page) => $page->hasFlash('toast', [
        'type' => 'success',
        'message' => $message,
    ]));
}

/**
 * @return list<string>
 */
function uc004Messages(int $userId): array
{
    return Notification::query()->where('user_id', $userId)->pluck('message')->all();
}

test('UC-004 NFR-004: a user without the permission cannot use the page and a guest is sent to login', function () {
    $teacher = Teacher::factory()->create();

    $this->get(route('project-versions.create'))->assertRedirect(route('login'));
    $this->post(route('project-versions.store'), [])->assertRedirect(route('login'));
    $this->actingAs($teacher->user)->get(route('project-versions.create'))->assertForbidden();
    $this->actingAs($teacher->user)
        ->post(route('project-versions.store'), ['title' => 'X', 'file' => uc004Pdf()])
        ->assertForbidden();
    expect(ProjectVersion::query()->count())->toBe(0);
});

test('UC-004 step 2: the page shows the phase and the last version with its observations and asks for the title', function () {
    $process = uc004Process($this->student, Phase::ProfileEvaluation);
    $first = ProjectVersion::factory()->for($process)->profileRejected()->create(['title' => 'Primer título']);
    $observation = Observation::factory()->forVersion($first)->create(['body' => 'Falta justificar el alcance.']);

    $this->actingAs($this->studentUser)
        ->get(route('project-versions.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('project-versions/create')
            ->where('phase', 'Evaluación de perfil')
            ->where('reason', null)
            ->where('requiresTitle', true)
            ->where('lastVersion.number', 1)
            ->where('lastVersion.status', 'Rechazada')
            ->where('lastVersion.title', 'Primer título')
            ->has('lastVersion.observations', 1)
            ->where('lastVersion.observations.0.author', $observation->author->name)
            ->where('lastVersion.observations.0.body', 'Falta justificar el alcance.'));
});

test('UC-004 step 2 BR-007: in pre-defense the page does not ask for the title', function () {
    uc004Process($this->student, Phase::PreDefense, 'Título aprobado');

    $this->actingAs($this->studentUser)
        ->get(route('project-versions.create'))
        ->assertInertia(fn ($page) => $page->where('reason', null)->where('requiresTitle', false)->where('lastVersion', null));
});

test('UC-004 steps 3 to 8 BR-007: a project profile is submitted in review with the workshop teacher as reviewer', function () {
    $workshopTeacher = Teacher::factory()->workshopTeacher()->create();
    $process = uc004Process($this->student, Phase::ProfileEvaluation);

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['title' => 'Sistema de titulación', 'file' => uc004Pdf()])
        ->assertRedirect(route('project-versions.create'));
    uc004AssertToast($this, 'Versión 1 presentada. Estado: En revisión.');

    $version = ProjectVersion::query()->sole();
    expect($version->graduation_process_id)->toBe($process->id)
        ->and($version->number)->toBe(1)
        ->and($version->phase)->toBe(Phase::ProfileEvaluation)
        ->and($version->title)->toBe('Sistema de titulación')
        ->and($version->status)->toBe(ProjectVersionStatus::InReview)
        ->and($version->reviewer_id)->toBe($workshopTeacher->id)
        ->and($version->submitted_at)->not->toBeNull()
        ->and($process->fresh()->phase)->toBe(Phase::ProfileEvaluation);
});

test('UC-004 BR-004 step 7: a profile notifies only the workshop teacher', function () {
    $workshopTeacher = Teacher::factory()->workshopTeacher()->create();
    uc004Process($this->student, Phase::ProfileEvaluation);

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['title' => 'T', 'file' => uc004Pdf()]);

    expect(Notification::query()->count())->toBe(1)
        ->and(uc004Messages($workshopTeacher->user_id))->toBe([
            "{$this->studentUser->name} presentó el perfil de proyecto (versión 1), que espera su revisión.",
        ]);
});

test('UC-004 steps 3 to 8: a pre-defense version is submitted in review with the title of the process', function () {
    $process = uc004Process($this->student, Phase::PreDefense, 'Título aprobado');
    ProjectVersion::factory()->for($process)->profileApproved()->create(['title' => 'Título aprobado']);

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['file' => uc004Pdf('version.pdf')])
        ->assertRedirect(route('project-versions.create'));
    uc004AssertToast($this, 'Versión 2 presentada. Estado: En revisión.');

    $version = ProjectVersion::query()->where('number', 2)->sole();
    expect($version->phase)->toBe(Phase::PreDefense)
        ->and($version->title)->toBe('Título aprobado')
        ->and($version->status)->toBe(ProjectVersionStatus::InReview)
        ->and($version->reviewer_id)->toBeNull();
});

test('UC-004 BR-004 step 7: a pre-defense version notifies the tutor and the three committee members', function () {
    $process = uc004Process($this->student, Phase::PreDefense, 'Título aprobado');

    $this->actingAs($this->studentUser)->post(route('project-versions.store'), ['file' => uc004Pdf()]);

    $message = "{$this->studentUser->name} presentó la versión 1 del proyecto de grado, que espera su revisión.";
    $recipients = $process->currentCommitteeMembers()->with('teacher')->get()->map(fn ($member) => $member->teacher->user_id)
        ->push($process->tutor->user_id);

    expect($recipients)->toHaveCount(4)
        ->and(Notification::query()->count())->toBe(4);

    foreach ($recipients as $userId) {
        expect(uc004Messages($userId))->toBe([$message]);
    }
});

test('UC-004 A1 BR-005: without a workshop teacher the profile is in review without reviewer and the student is told', function () {
    uc004Process($this->student, Phase::ProfileEvaluation);

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['title' => 'Sin revisor', 'file' => uc004Pdf()])
        ->assertRedirect(route('project-versions.create'));
    uc004AssertToast($this, 'Versión 1 presentada. Estado: En revisión. Su perfil de proyecto queda pendiente de revisión hasta que el director de carrera designe al docente de taller de grado.');

    $version = ProjectVersion::query()->sole();
    expect($version->status)->toBe(ProjectVersionStatus::InReview)
        ->and($version->reviewer_id)->toBeNull()
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-004 A2 BR-006: a formal defense version is presented, not reviewed, and notifies the tutor and the committee', function () {
    $process = uc004Process($this->student, Phase::FormalDefense, 'Título aprobado');
    uc004HeldPreDefense($process, withObservation: true);
    $former = CommitteeMember::factory()->for($process)->replaced()->create();
    ProjectVersion::factory()->for($process)->create([
        'phase' => Phase::PreDefense, 'status' => ProjectVersionStatus::WithObservations, 'number' => 2,
    ]);

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['file' => uc004Pdf()])
        ->assertRedirect(route('project-versions.create'));
    uc004AssertToast($this, 'Versión 3 presentada. Estado: Presentada.');

    $version = ProjectVersion::query()->where('number', 3)->sole();
    expect($version->phase)->toBe(Phase::FormalDefense)
        ->and($version->status)->toBe(ProjectVersionStatus::Submitted)
        ->and($version->title)->toBe('Título aprobado')
        ->and($version->reviewer_id)->toBeNull();

    $message = "{$this->studentUser->name} presentó la versión 3 del proyecto de grado para la defensa formal.";
    $recipients = $process->currentCommitteeMembers()->with('teacher')->get()->map(fn ($member) => $member->teacher->user_id)
        ->push($process->tutor->user_id);

    expect($recipients)->toHaveCount(4)
        ->and(Notification::query()->count())->toBe(4)
        ->and(uc004Messages($former->teacher->user_id))->toBe([]);

    foreach ($recipients as $userId) {
        expect(uc004Messages($userId))->toBe([$message]);
    }

    // A2 BR-006: the student may present more versions until the formal defense.
    $this->actingAs($this->studentUser)->post(route('project-versions.store'), ['file' => uc004Pdf()]);
    expect(ProjectVersion::query()->where('number', 4)->exists())->toBeTrue();
});

test('UC-004 BR-001 C-014: the PDF is stored on the private disk and the database keeps its path and data', function () {
    $process = uc004Process($this->student, Phase::ProfileEvaluation);

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['title' => 'T', 'file' => uc004Pdf('mi-perfil.pdf', 300)]);

    $version = ProjectVersion::query()->sole();
    expect($version->file_path)->toStartWith("project-versions/{$process->id}/")
        ->and($version->original_file_name)->toBe('mi-perfil.pdf')
        ->and($version->file_size_bytes)->toBe(300 * 1024);
    Storage::disk('local')->assertExists($version->file_path);
});

test('UC-004 BR-001 NFR-008: previous versions and their observations are kept unchanged', function () {
    $process = uc004Process($this->student, Phase::ProfileEvaluation);
    $previous = ProjectVersion::factory()->for($process)->profileRejected()->create();
    Observation::factory()->forVersion($previous)->create();
    $before = $previous->fresh()->toArray();

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['title' => 'Segundo intento', 'file' => uc004Pdf()]);

    expect($previous->fresh()->toArray())->toBe($before)
        ->and($previous->observations()->count())->toBe(1)
        ->and(ProjectVersion::query()->count())->toBe(2);
});

test('UC-004 BR-003: the numbering continues across phases without restarting', function () {
    $process = uc004Process($this->student, Phase::PreDefense, 'Título aprobado');
    ProjectVersion::factory()->for($process)->profileRejected()->create(['number' => 1]);
    ProjectVersion::factory()->for($process)->profileApproved()->create(['number' => 2]);
    ProjectVersion::factory()->for($process)->create([
        'number' => 3, 'phase' => Phase::PreDefense, 'status' => ProjectVersionStatus::WithObservations,
    ]);

    $this->actingAs($this->studentUser)->post(route('project-versions.store'), ['file' => uc004Pdf()]);

    expect(ProjectVersion::query()->max('number'))->toBe(4)
        ->and(ProjectVersion::query()->where('number', 4)->sole()->phase)->toBe(Phase::PreDefense);
});

test('UC-004 BR-007: outside profile evaluation the title is fixed and a submitted one is ignored', function () {
    $process = uc004Process($this->student, Phase::PreDefense, 'Título fijo');

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['title' => 'Título cambiado', 'file' => uc004Pdf()])
        ->assertSessionHasNoErrors();

    expect(ProjectVersion::query()->sole()->title)->toBe('Título fijo')
        ->and($process->fresh()->project_title)->toBe('Título fijo');
});

test('UC-004 BR-002 A3: the page gives the reason when the phase admits no version', function (string $scenario, string $reason) {
    $process = match ($scenario) {
        'no-process' => null,
        'archive' => uc004Process($this->student, Phase::Archive),
        'requirements' => uc004Process($this->student, Phase::Requirements),
        'profile-in-review' => tap(uc004Process($this->student, Phase::ProfileEvaluation), fn ($p) => ProjectVersion::factory()->for($p)->profileInReview()->create(['number' => 2])),
        'profile-approved' => tap(uc004Process($this->student, Phase::ProfileEvaluation), fn ($p) => ProjectVersion::factory()->for($p)->profileApproved()->create()),
        'pre-defense-in-review' => tap(uc004Process($this->student, Phase::PreDefense), fn ($p) => ProjectVersion::factory()->for($p)->create(['number' => 3, 'phase' => Phase::PreDefense])),
        'pre-defense-approved' => tap(uc004Process($this->student, Phase::PreDefense), fn ($p) => ProjectVersion::factory()->for($p)->create(['phase' => Phase::PreDefense, 'status' => ProjectVersionStatus::Approved])),
        'formal-defense-held' => tap(uc004Process($this->student, Phase::FormalDefense), function ($p) {
            uc004HeldPreDefense($p, withObservation: true);
            Defense::factory()->for($p)->formalDefense()->held()->create(['scheduled_at' => now()->subDay()]);
        }),
        'formal-defense-started' => tap(uc004Process($this->student, Phase::FormalDefense), function ($p) {
            uc004HeldPreDefense($p, withObservation: true);
            Defense::factory()->for($p)->formalDefense()->create(['scheduled_at' => now()->subHour()]);
        }),
        'no-observations' => tap(uc004Process($this->student, Phase::FormalDefense), fn ($p) => uc004HeldPreDefense($p, withObservation: false)),
    };

    $versionsBefore = ProjectVersion::query()->orderBy('id')->get()->toArray();
    $phaseBefore = $process?->fresh()->phase;

    $this->actingAs($this->studentUser)
        ->get(route('project-versions.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('reason', $reason)->where('requiresTitle', false));

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['title' => 'T', 'file' => uc004Pdf()])
        ->assertSessionHasErrors(['project_version' => $reason]);

    // The reason comes before any input error: a submission without file gets the same answer, not a server error.
    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), [])
        ->assertRedirect()
        ->assertSessionHasErrors(['project_version' => $reason])
        ->assertSessionDoesntHaveErrors(['file', 'title']);

    // UC-004 failure postconditions: earlier versions and the phase of the process do not change.
    expect(ProjectVersion::query()->orderBy('id')->get()->toArray())->toBe($versionsBefore)
        ->and($process?->fresh()->phase)->toBe($phaseBefore)
        ->and(ProjectVersion::query()->where('title', 'T')->exists())->toBeFalse()
        ->and(Storage::disk('local')->allFiles())->toBe([]);
})->with([
    'no process in progress' => ['no-process', 'No tiene un trámite de titulación en curso.'],
    'process in archive' => ['archive', 'No tiene un trámite de titulación en curso.'],
    'requirements without tutor' => ['requirements', 'Su trámite de titulación espera la asignación de su tutor.'],
    'profile in review' => ['profile-in-review', 'Su versión 2 del proyecto sigue en revisión.'],
    'profile approved' => ['profile-approved', 'Su perfil de proyecto ya fue aprobado y espera la asignación del tribunal evaluador.'],
    'pre-defense version in review' => ['pre-defense-in-review', 'Su versión 3 del proyecto sigue en revisión.'],
    'pre-defense version approved' => ['pre-defense-approved', 'Su versión de la fase pre-defensa ya fue aprobada y espera el siguiente paso.'],
    'formal defense already held' => ['formal-defense-held', 'Su defensa formal ya se realizó.'],
    'formal defense already started' => ['formal-defense-started', 'Su defensa formal ya llegó a su hora de inicio.'],
    'pre-defense left no observations' => ['no-observations', 'Su pre-defensa no dejó observaciones que subsanar.'],
]);

test('UC-004 BR-002: a concluded or abandoned process is not in progress', function () {
    GraduationProcess::factory()->for($this->student)->abandoned()->create();

    $this->actingAs($this->studentUser)
        ->get(route('project-versions.create'))
        ->assertInertia(fn ($page) => $page->where('reason', 'No tiene un trámite de titulación en curso.'));
});

test('UC-004 BR-002: a student only presents versions in their own process', function () {
    uc004Process(Student::factory()->create(), Phase::ProfileEvaluation);

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['title' => 'T', 'file' => uc004Pdf()])
        ->assertSessionHasErrors(['project_version' => 'No tiene un trámite de titulación en curso.']);

    expect(ProjectVersion::query()->count())->toBe(0);
});

test('UC-004 A4: a profile without title is rejected and nothing is stored', function () {
    uc004Process($this->student, Phase::ProfileEvaluation);

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['file' => uc004Pdf()])
        ->assertSessionHasErrors(['title' => 'Ingrese el título del proyecto de grado.']);

    expect(ProjectVersion::query()->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBe([]);
});

test('UC-004 A4 BR-001: a missing file, a non-PDF file and a file over 20 MB are rejected with their message', function (?UploadedFile $file, string $message) {
    uc004Process($this->student, Phase::ProfileEvaluation);

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), array_filter(['title' => 'Un título', 'file' => $file]))
        ->assertSessionHasErrors(['file' => $message])
        ->assertSessionDoesntHaveErrors('title');

    expect(ProjectVersion::query()->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBe([]);
})->with([
    'missing file' => [null, 'Adjunte el archivo de la versión.'],
    'not a PDF' => [fn () => UploadedFile::fake()->create('perfil.docx', 100, 'application/msword'), 'El archivo debe ser PDF.'],
    'over 20 MB' => [fn () => uc004Pdf('grande.pdf', 20481), 'El archivo no debe superar los 20 MB.'],
]);

test('UC-004 A4 BR-001: a PDF of exactly 20 MB is accepted', function () {
    uc004Process($this->student, Phase::ProfileEvaluation);

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['title' => 'T', 'file' => uc004Pdf('limite.pdf', 20480)])
        ->assertSessionHasNoErrors();

    expect(ProjectVersion::query()->count())->toBe(1);
});

test('UC-004 A4: the page keeps the form after a rejected submission', function () {
    uc004Process($this->student, Phase::ProfileEvaluation);

    $this->actingAs($this->studentUser)->from(route('project-versions.create'))
        ->post(route('project-versions.store'), ['title' => 'Conservado'])
        ->assertRedirect(route('project-versions.create'))
        ->assertSessionHasErrors('file');
});

test('UC-004 failure postcondition: no file stays stored when the registration fails', function () {
    $process = uc004Process($this->student, Phase::PreDefense, 'Título aprobado');
    $previous = ProjectVersion::factory()->for($process)->profileApproved()->create();
    $before = $previous->fresh()->toArray();
    $this->mock(NotifyUser::class, fn ($mock) => $mock->shouldReceive('handle')->andThrow(new RuntimeException('fallo')));

    $this->actingAs($this->studentUser)
        ->post(route('project-versions.store'), ['file' => uc004Pdf()])
        ->assertServerError();

    expect(ProjectVersion::query()->count())->toBe(1)
        ->and($previous->fresh()->toArray())->toBe($before)
        ->and($process->fresh()->phase)->toBe(Phase::PreDefense)
        ->and(Storage::disk('local')->allFiles())->toBe([]);
});
