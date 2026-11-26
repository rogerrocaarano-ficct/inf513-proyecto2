<?php

namespace App\Enums;

/**
 * Situación de la revisión de una versión del proyecto.
 */
enum ProjectVersionStatus: string
{
    case InReview = 'En revisión';
    case WithObservations = 'Con observaciones';
    case Approved = 'Aprobada';
    case Rejected = 'Rechazada';
    case Submitted = 'Presentada';
}
