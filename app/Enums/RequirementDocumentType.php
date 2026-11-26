<?php

namespace App\Enums;

/**
 * Clase de documento de la documentación de requisitos.
 */
enum RequirementDocumentType: string
{
    case EnrollmentSlip = 'Boleta de inscripción';
    case CurriculumVitae = 'Hoja de vida';
    case RequestLetter = 'Carta de solicitud';
}
