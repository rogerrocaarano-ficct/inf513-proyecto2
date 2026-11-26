<?php

namespace App\Policies;

use App\Models\GraduationProcess;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Who works on a graduation process as its tutor (UC-007).
 */
class GraduationProcessPolicy
{
    /**
     * UC-007 BR-001: Only the current tutor of a process in progress observes its project versions.
     */
    public function viewAsTutor(User $user, GraduationProcess $process): Response
    {
        $teacherId = $user->teacher?->id;

        if ($teacherId === null || $process->tutor_id !== $teacherId) {
            return Response::deny('Ya no es el tutor vigente de este trámite de titulación.');
        }

        if ($process->concluded_at !== null || $process->abandoned_at !== null) {
            return Response::deny('El trámite de titulación ya no está en curso.');
        }

        return Response::allow();
    }
}
