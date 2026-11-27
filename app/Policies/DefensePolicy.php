<?php

namespace App\Policies;

use App\Actions\Defenses\FindCurrentCommitteeMember;
use App\Enums\DefenseType;
use App\Models\Defense;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Who registers a pre-defense and downloads its approved version (UC-010, NFR-004, NFR-008).
 */
class DefensePolicy
{
    public function __construct(private FindCurrentCommitteeMember $findMember) {}

    /**
     * UC-010 BR-001: only a current member of the tribunal registers the pre-defense.
     */
    public function registerPreDefense(User $user, Defense $defense): Response
    {
        if ($defense->type !== DefenseType::PreDefense) {
            return Response::deny('El acto seleccionado no es una pre-defensa.');
        }

        return $this->findMember->forDefense($user, $defense) !== null
            ? Response::allow()
            : Response::deny('No es miembro vigente del tribunal de esta pre-defensa.');
    }

    /**
     * UC-010 step 4, NFR-004 and NFR-008: the current member downloads the approved pre-defense version.
     */
    public function downloadVersion(User $user, Defense $defense): Response
    {
        return $this->registerPreDefense($user, $defense);
    }

    public function gradeFormalDefense(User $user, Defense $defense): Response
    {
        return $defense->type === DefenseType::FormalDefense && $this->findMember->forDefense($user, $defense) !== null
            ? Response::allow()
            : Response::deny('No es miembro vigente del tribunal de esta defensa formal.');
    }
}
