<?php

namespace App\Enums;

/**
 * Decisión que un miembro del tribunal registra sobre una pre-defensa (UC-010).
 */
enum PreDefenseDecision: string
{
    case Observations = 'observaciones';
    case NoObservations = 'sin-observaciones';
    case Absence = 'inasistencia';
}
