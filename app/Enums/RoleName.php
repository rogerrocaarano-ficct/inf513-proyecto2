<?php

namespace App\Enums;

/**
 * Roles fijos que define el sistema (FR-027).
 */
enum RoleName: string
{
    case SystemAdministrator = 'Administrador del sistema';
    case AdministrativeAssistant = 'Asistente administrativo';
    case CareerDirector = 'Director de carrera';
    case Teacher = 'Docente';
    case Student = 'Estudiante';

    /**
     * Roles que el administrador del sistema asigna al gestionar cuentas de usuario.
     *
     * @return list<self>
     */
    public static function assignableByAdministrator(): array
    {
        // UC-018 BR-001: The administrator assigns teacher, career director or assistant.
        return [self::Teacher, self::CareerDirector, self::AdministrativeAssistant];
    }
}
