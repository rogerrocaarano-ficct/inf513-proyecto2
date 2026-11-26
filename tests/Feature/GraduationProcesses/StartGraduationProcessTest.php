<?php

use App\Actions\Notifications\NotifyUser;
use App\Enums\Phase;
use App\Enums\RequirementDocumentType;
use App\Enums\RoleName;
use App\Models\GraduationProcess;
use App\Models\Notification;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
    $this->assistant = User::factory()->administrativeAssistant()->create();
});

/**
 * Form data to start the graduation process of a student not registered yet.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function uc001Payload(array $overrides = []): array
{
    return array_merge([
        'registration_number' => '2021100234',
        'name' => 'Lucía Fernández Rojas',
        'email' => 'lucia.fernandez@example.com',
        'id_card_number' => '8765432',
        'id_card_complement' => null,
        'phone' => '71234567',
        'enrollment_slip' => UploadedFile::fake()->create('boleta.pdf', 200, 'application/pdf'),
        'curriculum_vitae' => UploadedFile::fake()->create('hoja-de-vida.pdf', 300, 'application/pdf'),
        'request_letter' => UploadedFile::fake()->create('carta.pdf', 100, 'application/pdf'),
        'tutor_id' => null,
        'proposed_tutor_name' => null,
    ], $overrides);
}

// Step 1-2 and permission (NFR-005)

test('UC-001 step 2: the assistant sees the form with the eligible teachers as tutor options (BR-006)', function () {
    $eligible = Teacher::factory()->create();
    Teacher::factory()->workshopTeacher()->create();
    Teacher::factory()->inactive()->create();

    $this->actingAs($this->assistant)
        ->get(route('graduation-processes.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('graduation-processes/create')
            ->has('tutors', 1)
            ->where('tutors.0.id', $eligible->id)
            ->where('tutors.0.name', $eligible->user->name)
            ->where('lookup', null));
});

test('UC-001 NFR-005: a user without the permission to start processes cannot open or submit the form', function () {
    $teacher = Teacher::factory()->create()->user;

    $this->actingAs($teacher)->get(route('graduation-processes.create'))->assertForbidden();
    $this->actingAs($teacher)->post(route('graduation-processes.store'), uc001Payload())->assertForbidden();
});

test('UC-001: a guest is sent to the login page', function () {
    $this->get(route('graduation-processes.create'))->assertRedirect(route('login'));
});

// Steps 3-4: lookup

test('UC-001 step 4: a registration number not registered asks for the student data', function () {
    $this->actingAs($this->assistant)
        ->get(route('graduation-processes.create', ['registration_number' => '2021100234']))
        ->assertInertia(fn ($page) => $page
            ->where('lookup.status', 'not_registered')
            ->where('lookup.canStart', true)
            ->where('registrationNumber', '2021100234'));
});

test('UC-001 A1: a student with a process in progress sees the name and the current phase, and cannot continue', function () {
    $student = Student::factory()->create();
    GraduationProcess::factory()->for($student)->withTutor()->create();

    $this->actingAs($this->assistant)
        ->get(route('graduation-processes.create', ['registration_number' => $student->registration_number]))
        ->assertInertia(fn ($page) => $page
            ->where('lookup.status', 'in_progress')
            ->where('lookup.canStart', false)
            ->where('lookup.studentName', $student->user->name)
            ->where('lookup.phase', 'Evaluación de perfil'));
});

test('UC-001 C-016 A2: a process concluded with a grade of 50 is failed and the student can start another', function () {
    $student = Student::factory()->create();
    GraduationProcess::factory()->for($student)->concluded(50)->create();

    $this->actingAs($this->assistant)
        ->get(route('graduation-processes.create', ['registration_number' => $student->registration_number]))
        ->assertInertia(fn ($page) => $page
            ->where('lookup.status', 'returning')
            ->where('lookup.canStart', true));
});

test('UC-001 A3 BR-009 C-016: a student with a process concluded with a grade of 51 sees that it is approved, and cannot continue', function () {
    $student = Student::factory()->create();
    GraduationProcess::factory()->for($student)->concluded(51)->create();

    $this->actingAs($this->assistant)
        ->get(route('graduation-processes.create', ['registration_number' => $student->registration_number]))
        ->assertInertia(fn ($page) => $page
            ->where('lookup.status', 'approved')
            ->where('lookup.canStart', false)
            ->where('lookup.studentName', $student->user->name));
});

test('UC-001 A2: a student with only failed or abandoned processes sees name, id card and previous processes', function () {
    $student = Student::factory()->create(['id_card_number' => '1234567', 'id_card_complement' => '1A']);
    GraduationProcess::factory()->for($student)->failed()->create(['started_at' => now()->subYear()]);
    GraduationProcess::factory()->for($student)->abandoned()->create(['started_at' => now()->subMonths(6)]);

    $this->actingAs($this->assistant)
        ->get(route('graduation-processes.create', ['registration_number' => $student->registration_number]))
        ->assertInertia(fn ($page) => $page
            ->where('lookup.status', 'returning')
            ->where('lookup.canStart', true)
            ->where('lookup.studentName', $student->user->name)
            ->where('lookup.idCard', '1234567 1A')
            ->has('lookup.previousProcesses', 2)
            ->where('lookup.previousProcesses.0.outcome', 'Reprobado')
            ->where('lookup.previousProcesses.1.outcome', 'Abandonado'));
});

test('UC-001 A4: retyping the registration number looks up the corrected student', function () {
    $wrong = Student::factory()->create();
    $intended = Student::factory()->create();

    $this->actingAs($this->assistant)
        ->get(route('graduation-processes.create', ['registration_number' => $wrong->registration_number]))
        ->assertInertia(fn ($page) => $page
            ->where('lookup.status', 'returning')
            ->where('lookup.studentName', $wrong->user->name));

    $this->actingAs($this->assistant)
        ->get(route('graduation-processes.create', ['registration_number' => $intended->registration_number]))
        ->assertInertia(fn ($page) => $page
            ->where('registrationNumber', $intended->registration_number)
            ->where('lookup.studentName', $intended->user->name)
            ->where('lookup.canStart', true));
});

// Main success scenario

test('UC-001 steps 5-13: a new student with a tutor gets an account, a process in Evaluación de perfil, documents and a notification', function () {
    $tutor = Teacher::factory()->create();

    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload([
            'id_card_complement' => '1B',
            'tutor_id' => $tutor->id,
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('graduation-processes.create'))
        ->assertInertiaFlash('toast', [
            'type' => 'success',
            'message' => 'Trámite de titulación iniciado. La contraseña inicial del estudiante es su número de cédula de identidad.',
        ]);

    $student = Student::query()->where('registration_number', '2021100234')->firstOrFail();
    expect($student->id_card_number)->toBe('8765432')
        ->and($student->id_card_complement)->toBe('1B')
        ->and($student->phone)->toBe('71234567')
        ->and($student->user->name)->toBe('Lucía Fernández Rojas')
        ->and($student->user->email)->toBe('lucia.fernandez@example.com');

    $process = $student->graduationProcesses()->sole();
    expect($process->phase)->toBe(Phase::ProfileEvaluation)
        ->and($process->tutor_id)->toBe($tutor->id)
        ->and($process->started_at)->not->toBeNull();

    $phaseChange = $process->phaseChanges()->sole();
    expect($phaseChange->phase)->toBe(Phase::ProfileEvaluation)
        ->and($phaseChange->entered_at->equalTo($process->started_at))->toBeTrue()
        ->and($phaseChange->changed_by)->toBe($this->assistant->id);

    $notification = Notification::query()->where('user_id', $tutor->user_id)->sole();
    expect($notification->message)->toBe('Se le asignó como tutor del trámite de titulación de Lucía Fernández Rojas.')
        ->and($notification->graduation_process_id)->toBe($process->id);
});

test('UC-001 BR-005: the new student account has the Estudiante role, the id card number without complement as password, and must change it', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['id_card_complement' => '1B']))
        ->assertSessionHasNoErrors();

    $user = User::query()->where('email', 'lucia.fernandez@example.com')->firstOrFail();

    expect(Hash::check('8765432', $user->password))->toBeTrue()
        ->and($user->must_change_password)->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->is_active)->toBeTrue()
        ->and($user->hasRole(RoleName::Student))->toBeTrue()
        ->and($user->roles)->toHaveCount(1);
});

test('UC-001 BR-001 BR-002: the three PDFs are stored on the private disk with their metadata and uploader', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload())
        ->assertSessionHasNoErrors();

    $process = GraduationProcess::query()->sole();
    $documents = $process->requirementDocuments()->get()->keyBy(fn ($document) => $document->type->value);

    expect($documents)->toHaveCount(3)
        ->and($documents->keys()->all())->toEqualCanonicalizing(array_column(RequirementDocumentType::cases(), 'value'))
        ->and($documents[RequirementDocumentType::EnrollmentSlip->value]->original_file_name)->toBe('boleta.pdf')
        ->and($documents[RequirementDocumentType::CurriculumVitae->value]->original_file_name)->toBe('hoja-de-vida.pdf')
        ->and($documents[RequirementDocumentType::RequestLetter->value]->original_file_name)->toBe('carta.pdf');

    foreach ($documents as $document) {
        expect($document->uploaded_by)->toBe($this->assistant->id)
            ->and($document->uploaded_at)->not->toBeNull()
            ->and($document->file_size_bytes)->toBeGreaterThan(0)
            ->and($document->file_path)->toStartWith("requirement-documents/{$process->id}/");
        Storage::disk('local')->assertExists($document->file_path);
    }

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('UC-001 A6 BR-006: without a tutor the process starts in Requisitos, without tutor and without notification', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload())
        ->assertSessionHasNoErrors();

    $process = GraduationProcess::query()->sole();
    expect($process->phase)->toBe(Phase::Requirements)
        ->and($process->tutor_id)->toBeNull()
        ->and($process->proposed_tutor_name)->toBeNull()
        ->and($process->phaseChanges()->sole()->phase)->toBe(Phase::Requirements)
        ->and(Notification::query()->count())->toBe(0);
});

test('UC-001 A5: a proposed tutor who is not eligible is noted by name and the process starts in Requisitos', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['proposed_tutor_name' => 'Dr. Mario Quispe']))
        ->assertSessionHasNoErrors();

    $process = GraduationProcess::query()->sole();
    expect($process->phase)->toBe(Phase::Requirements)
        ->and($process->tutor_id)->toBeNull()
        ->and($process->proposed_tutor_name)->toBe('Dr. Mario Quispe');
});

test('UC-001 BR-006: the workshop teacher and an inactive teacher cannot be chosen as tutor', function () {
    foreach ([Teacher::factory()->workshopTeacher()->create(), Teacher::factory()->inactive()->create()] as $teacher) {
        $this->actingAs($this->assistant)
            ->post(route('graduation-processes.store'), uc001Payload(['tutor_id' => $teacher->id]))
            ->assertSessionHasErrors('tutor_id');
    }

    expect(GraduationProcess::query()->count())->toBe(0)
        ->and(User::query()->where('email', 'lucia.fernandez@example.com')->exists())->toBeFalse();
});

test('UC-001 A8 OQ-039: the phone is optional', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['phone' => null]))
        ->assertSessionHasNoErrors();

    expect(Student::query()->sole()->phone)->toBeNull();
});

// A2 + BR-007

test('UC-001 A2 BR-007: a registered student starts a new process without a new account, data or password change', function () {
    $student = Student::factory()->create(['phone' => '60000000']);
    $originalPassword = $student->user->password;
    GraduationProcess::factory()->for($student)->failed()->create();
    $usersBefore = User::query()->count();

    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), [
            'registration_number' => $student->registration_number,
            'confirmed_student' => '1',
            'name' => 'Otro Nombre',
            'email' => 'otro@example.com',
            'enrollment_slip' => UploadedFile::fake()->create('boleta.pdf', 50, 'application/pdf'),
            'curriculum_vitae' => UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf'),
            'request_letter' => UploadedFile::fake()->create('carta.pdf', 50, 'application/pdf'),
        ])
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Trámite de titulación iniciado.']);

    $student->refresh();
    expect(User::query()->count())->toBe($usersBefore)
        ->and($student->user->password)->toBe($originalPassword)
        ->and($student->user->name)->not->toBe('Otro Nombre')
        ->and($student->user->email)->not->toBe('otro@example.com')
        ->and($student->phone)->toBe('60000000')
        ->and($student->graduationProcesses()->count())->toBe(2)
        ->and($student->graduationProcesses()->inProgress()->sole()->requirementDocuments()->count())->toBe(3);
});

test('UC-001 A2: the assistant must confirm that the registered student is the one who presents', function () {
    $student = Student::factory()->create();
    GraduationProcess::factory()->for($student)->abandoned()->create();

    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), [
            'registration_number' => $student->registration_number,
            'enrollment_slip' => UploadedFile::fake()->create('boleta.pdf', 50, 'application/pdf'),
            'curriculum_vitae' => UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf'),
            'request_letter' => UploadedFile::fake()->create('carta.pdf', 50, 'application/pdf'),
        ])
        ->assertSessionHasErrors(['confirmed_student' => 'Confirme que es el estudiante que se presenta.']);

    expect($student->graduationProcesses()->count())->toBe(1);
});

// BR-003, BR-009 on store

test('UC-001 BR-003 A1: a student with a process in progress cannot start another one', function () {
    $student = Student::factory()->create(['phone' => '60000000']);
    $process = GraduationProcess::factory()->for($student)->create();
    $before = $student->user->only(['name', 'email', 'password']);
    $usersBefore = User::query()->count();

    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload([
            'registration_number' => $student->registration_number,
            'confirmed_student' => '1',
            'phone' => '71111111',
        ]))
        ->assertSessionHasErrors('registration_number');

    $student->refresh();
    expect($student->graduationProcesses()->count())->toBe(1)
        ->and($student->graduationProcesses()->sole()->is($process))->toBeTrue()
        ->and($student->user->only(['name', 'email', 'password']))->toBe($before)
        ->and($student->phone)->toBe('60000000')
        ->and(User::query()->count())->toBe($usersBefore);
    Storage::disk('local')->assertDirectoryEmpty('requirement-documents');
});

test('UC-001 BR-009 A3: a student with an approved process cannot start another one', function () {
    $student = Student::factory()->create();
    GraduationProcess::factory()->for($student)->approved()->create();

    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload([
            'registration_number' => $student->registration_number,
            'confirmed_student' => '1',
        ]))
        ->assertSessionHasErrors();

    expect($student->graduationProcesses()->count())->toBe(1);
});

// A7

test('UC-001 A7 BR-004: an email that belongs to another user is rejected and nothing is stored', function () {
    $existing = User::factory()->create();

    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['email' => $existing->email]))
        ->assertSessionHasErrors(['email' => 'El correo electrónico ya pertenece a otro usuario.']);

    expect(Student::query()->count())->toBe(0)
        ->and(GraduationProcess::query()->count())->toBe(0);
});

test('UC-001 A7 BR-004: an id card (number and complement) that belongs to another student is rejected', function () {
    Student::factory()->create(['id_card_number' => '8765432', 'id_card_complement' => null]);

    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload())
        ->assertSessionHasErrors(['id_card_number' => 'La cédula de identidad ya pertenece a otro estudiante.']);

    Student::factory()->create(['id_card_number' => '1111111', 'id_card_complement' => '1A']);

    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload([
            'id_card_number' => '1111111', 'id_card_complement' => '1A',
        ]))
        ->assertSessionHasErrors(['id_card_number' => 'La cédula de identidad ya pertenece a otro estudiante.']);

    expect(GraduationProcess::query()->count())->toBe(0);
});

test('UC-001 BR-004: the same id card number with a different complement is a different student', function () {
    Student::factory()->create(['id_card_number' => '8765432', 'id_card_complement' => '1A']);

    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['id_card_complement' => '1B']))
        ->assertSessionHasNoErrors();

    expect(GraduationProcess::query()->count())->toBe(1);
});

// A8

test('UC-001 A8: each missing required student datum is reported in Spanish', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload([
            'name' => null, 'email' => null, 'id_card_number' => null,
        ]))
        ->assertSessionHasErrors([
            'name' => 'El campo nombre completo es obligatorio.',
            'email' => 'El campo correo electrónico es obligatorio.',
            'id_card_number' => 'El campo número de cédula de identidad es obligatorio.',
        ]);

    expect(User::query()->where('email', 'lucia.fernandez@example.com')->exists())->toBeFalse();
});

test('UC-001 A8: an invalid email or phone is rejected', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['email' => 'no-es-correo', 'phone' => '12345']))
        ->assertSessionHasErrors([
            'email' => 'El campo correo electrónico debe ser una dirección de correo electrónico válida.',
            'phone' => 'El teléfono debe tener 7 u 8 dígitos, con el prefijo +591 opcional.',
        ]);
});

test('UC-001 OQ-048: letters in the id card number are rejected', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['id_card_number' => '87654A2']))
        ->assertSessionHasErrors(['id_card_number' => 'El número de cédula de identidad solo puede tener dígitos.']);
});

test('UC-001 OQ-048: letters and digits are accepted in the complement, other characters are not', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['id_card_complement' => '1A']))
        ->assertSessionHasNoErrors();

    expect(Student::query()->sole()->id_card_complement)->toBe('1A');

    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload([
            'registration_number' => '2021100999', 'email' => 'otra@example.com', 'id_card_complement' => '1-A',
        ]))
        ->assertSessionHasErrors(['id_card_complement' => 'El complemento solo puede tener letras y dígitos.']);
});

test('UC-001 OQ-052: a phone with the +591 prefix is stored as its digits only', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['phone' => '+59171234567']))
        ->assertSessionHasNoErrors();

    expect(Student::query()->sole()->phone)->toBe('71234567');
});

test('UC-001 A8 OQ-039: a phone of 7 or 8 digits with an optional +591 prefix is valid', function (string $phone) {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['phone' => $phone]))
        ->assertSessionHasNoErrors();
})->with(['2123456', '71234567', '+59171234567', '+5912123456']);

test('UC-001 A8 OQ-039: a phone that is not 7 or 8 digits is invalid', function (string $phone) {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['phone' => $phone]))
        ->assertSessionHasErrors('phone');
})->with(['123456', '712345678', '+59212345678', '7123-4567', 'abcdefgh']);

test('UC-001 A8: the student data is limited to the lengths of the entity model', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload([
            'name' => str_repeat('a', 256),
            'id_card_number' => str_repeat('1', 21),
            'id_card_complement' => '123456',
            'proposed_tutor_name' => str_repeat('a', 151),
        ]))
        ->assertSessionHasErrors(['name', 'id_card_number', 'id_card_complement', 'proposed_tutor_name']);
});

// A9

test('UC-001 A9 BR-001: each missing document is reported by name', function (string $field, string $message) {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload([$field => null]))
        ->assertSessionHasErrors([$field => $message]);

    expect(GraduationProcess::query()->count())->toBe(0);
})->with([
    ['enrollment_slip', 'Adjunte la boleta de inscripción.'],
    ['curriculum_vitae', 'Adjunte la hoja de vida.'],
    ['request_letter', 'Adjunte la carta de solicitud.'],
]);

test('UC-001 A9 BR-002 C-004: a document that is not a PDF is rejected', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload([
            'curriculum_vitae' => UploadedFile::fake()->create('cv.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'request_letter' => UploadedFile::fake()->image('carta.png'),
        ]))
        ->assertSessionHasErrors([
            'curriculum_vitae' => 'El archivo debe ser PDF.',
            'request_letter' => 'El archivo debe ser PDF.',
        ]);
});

test('UC-001 A9 BR-002 C-004: a document over 20 MB is rejected and one of exactly 20 MB is accepted', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload([
            'enrollment_slip' => UploadedFile::fake()->create('boleta.pdf', 20481, 'application/pdf'),
        ]))
        ->assertSessionHasErrors(['enrollment_slip' => 'El archivo no debe superar los 20 MB.']);

    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload([
            'enrollment_slip' => UploadedFile::fake()->create('boleta.pdf', 20480, 'application/pdf'),
        ]))
        ->assertSessionHasNoErrors();
});

// Failure postconditions

test('UC-001 failure postcondition: when a step fails after the files were stored, no account, process or file remains', function () {
    $tutor = Teacher::factory()->create();
    $this->mock(NotifyUser::class)->shouldReceive('handle')->andThrow(new RuntimeException('fallo'));
    $this->withoutExceptionHandling();

    expect(fn () => $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['tutor_id' => $tutor->id])))
        ->toThrow(RuntimeException::class);

    expect(Student::query()->count())->toBe(0)
        ->and(User::query()->where('email', 'lucia.fernandez@example.com')->exists())->toBeFalse()
        ->and(GraduationProcess::query()->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('UC-001 failure postcondition: a rejected submission stores no file', function () {
    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), uc001Payload(['email' => null]))
        ->assertSessionHasErrors('email');

    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('UC-001 BR-001: a new process of a registered student does not reuse the documents of the previous one', function () {
    $student = Student::factory()->create();
    $previous = GraduationProcess::factory()->for($student)->failed()->create();
    $oldDocument = $previous->requirementDocuments()->create([
        'type' => RequirementDocumentType::EnrollmentSlip,
        'file_path' => 'requirement-documents/old.pdf',
        'original_file_name' => 'old.pdf',
        'file_size_bytes' => 10,
        'uploaded_by' => $this->assistant->id,
        'uploaded_at' => now()->subYear(),
    ]);

    $this->actingAs($this->assistant)
        ->post(route('graduation-processes.store'), [
            'registration_number' => $student->registration_number,
            'confirmed_student' => '1',
            'enrollment_slip' => UploadedFile::fake()->create('nueva-boleta.pdf', 50, 'application/pdf'),
            'curriculum_vitae' => UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf'),
            'request_letter' => UploadedFile::fake()->create('carta.pdf', 50, 'application/pdf'),
        ])
        ->assertSessionHasNoErrors();

    $current = $student->graduationProcesses()->inProgress()->sole();
    expect($current->requirementDocuments()->pluck('original_file_name')->all())->not->toContain('old.pdf')
        ->and($current->requirementDocuments()->count())->toBe(3)
        ->and($previous->requirementDocuments()->sole()->is($oldDocument))->toBeTrue();
});
