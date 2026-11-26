<?php

namespace App\Actions\Accounts;

use RuntimeException;

/**
 * The account cannot be deactivated or lose the teacher role yet (UC-018 A3).
 */
class AccountChangeBlocked extends RuntimeException
{
    /**
     * @param  list<array{student: string|null, role: string}>  $assignments
     */
    public function __construct(public readonly array $assignments)
    {
        parent::__construct(
            'No se puede desactivar la cuenta ni quitarle el rol docente. El director de carrera debe reasignar antes:'
        );
    }
}
