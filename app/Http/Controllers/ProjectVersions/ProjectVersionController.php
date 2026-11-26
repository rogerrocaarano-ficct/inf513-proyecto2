<?php

namespace App\Http\Controllers\ProjectVersions;

use App\Actions\ProjectVersions\FindVersionAdmission;
use App\Actions\ProjectVersions\SubmitProjectVersion;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectVersions\SubmitProjectVersionRequest;
use App\Models\Observation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectVersionController extends Controller
{
    /**
     * Step 2 and A3: the current phase, the last version with its observations, and the form when one is admitted.
     */
    public function create(Request $request, FindVersionAdmission $findAdmission): Response
    {
        $admission = $findAdmission->handle($request->user());
        $last = $admission->lastVersion?->load(['observations.author']);

        return Inertia::render('project-versions/create', [
            'phase' => $admission->process?->phase->value,
            'reason' => $admission->reason,
            'requiresTitle' => $admission->requiresTitle(),
            'lastVersion' => $last === null ? null : [
                'number' => $last->number,
                'phase' => $last->phase->value,
                'status' => $last->status->value,
                'title' => $last->title,
                'submitted_at' => $last->submitted_at->toIso8601String(),
                'observations' => $last->observations->map(fn (Observation $observation): array => [
                    'id' => $observation->id,
                    'author' => $observation->author->name,
                    'body' => $observation->body,
                    'recorded_at' => $observation->recorded_at->toIso8601String(),
                ])->values(),
            ],
        ]);
    }

    /**
     * Steps 3-8 and A1, A2: submit the version and confirm its number and status.
     */
    public function store(SubmitProjectVersionRequest $request, SubmitProjectVersion $submitProjectVersion): RedirectResponse
    {
        $submitted = $submitProjectVersion->handle(
            $request->user(),
            $request->validated('title'),
            $request->file('file'),
        );

        $message = "Versión {$submitted->version->number} presentada. Estado: {$submitted->version->status->value}.";

        // UC-004 A1: Tell the student that a profile without workshop teacher waits for the designation.
        if ($submitted->withoutReviewer) {
            $message .= ' Su perfil de proyecto queda pendiente de revisión hasta que el director de carrera designe al docente de taller de grado.';
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('project-versions.create');
    }
}
