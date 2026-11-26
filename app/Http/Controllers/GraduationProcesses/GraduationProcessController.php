<?php

namespace App\Http\Controllers\GraduationProcesses;

use App\Actions\GraduationProcesses\LookupStudentForGraduationProcess;
use App\Actions\GraduationProcesses\StartGraduationProcess;
use App\Actions\GraduationProcesses\StudentLookup;
use App\Enums\RequirementDocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\GraduationProcesses\LookupStudentRequest;
use App\Http\Requests\GraduationProcesses\StartGraduationProcessRequest;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GraduationProcessController extends Controller
{
    /**
     * Show the form to start a graduation process, with the result of the registration number lookup.
     */
    public function create(LookupStudentRequest $request, LookupStudentForGraduationProcess $lookupStudent): Response
    {
        $registrationNumber = $request->validated('registration_number');
        $lookup = $registrationNumber !== null ? $lookupStudent->handle($registrationNumber) : null;
        $student = $lookup?->student;

        return Inertia::render('graduation-processes/create', [
            // UC-001 BR-006: The tutor options are the eligible teachers.
            'tutors' => Teacher::query()
                ->eligibleAsTutor()
                ->with('user:id,name')
                ->get()
                ->map(fn (Teacher $teacher): array => ['id' => $teacher->id, 'name' => $teacher->user->name])
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all(),
            'registrationNumber' => $registrationNumber,
            'lookup' => $lookup === null ? null : [
                'status' => $lookup->status,
                'canStart' => $lookup->canStartProcess(),
                'studentName' => $student?->user->name,
                'idCard' => $student === null ? null : trim($student->id_card_number.' '.$student->id_card_complement),
                'phase' => $lookup->phase,
                'previousProcesses' => $lookup->previousProcesses,
            ],
        ]);
    }

    /**
     * Start the graduation process and, for a new student, create the account.
     */
    public function store(StartGraduationProcessRequest $request, StartGraduationProcess $startGraduationProcess): RedirectResponse
    {
        $isNewStudent = $request->studentLookup()->status === StudentLookup::NotRegistered;

        $started = $startGraduationProcess->handle(
            assistant: $request->user(),
            registrationNumber: $request->validated('registration_number'),
            studentData: $isNewStudent
                ? $request->safe()->only(['name', 'email', 'id_card_number', 'id_card_complement', 'phone'])
                : null,
            documents: [
                RequirementDocumentType::EnrollmentSlip->value => $request->file('enrollment_slip'),
                RequirementDocumentType::CurriculumVitae->value => $request->file('curriculum_vitae'),
                RequirementDocumentType::RequestLetter->value => $request->file('request_letter'),
            ],
            tutor: $request->validated('tutor_id') !== null ? Teacher::query()->whereKey($request->validated('tutor_id'))->firstOrFail() : null,
            proposedTutorName: $request->validated('proposed_tutor_name'),
        );

        $message = 'Trámite de titulación iniciado.';

        if ($started->accountCreated) {
            $message .= ' La contraseña inicial del estudiante es su número de cédula de identidad.';
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('graduation-processes.create');
    }
}
