<?php

namespace App\Enums;

/**
 * Tipo de acto: pre-defensa o defensa formal.
 */
enum DefenseType: string
{
    case PreDefense = 'Pre-defensa';
    case FormalDefense = 'Defensa formal';
}
