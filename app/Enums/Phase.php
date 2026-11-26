<?php

namespace App\Enums;

/**
 * Fase de un trámite de titulación, en el orden en que se suceden.
 */
enum Phase: string
{
    case Requirements = 'Requisitos';
    case ProfileEvaluation = 'Evaluación de perfil';
    case PreDefense = 'Pre-defensa';
    case FormalDefense = 'Defensa formal';
    case Archive = 'Archivo';
}
