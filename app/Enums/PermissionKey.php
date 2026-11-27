<?php

namespace App\Enums;

/**
 * Clave con la que el sistema comprueba cada permiso (NFR-005, ADR-005).
 */
enum PermissionKey: string
{
    case ViewHome = 'inicio.ver';
    case ManageAccounts = 'cuentas.gestionar';
    case StartGraduationProcess = 'tramites.iniciar';
    case ArchiveGraduationProcess = 'tramites.archivar';
    case AssignTutor = 'tutores.asignar';
    case DesignateWorkshopTeacher = 'taller.designar';
    case AssignCommittee = 'tribunales.asignar';
    case SubmitProjectVersion = 'versiones.presentar';
    case EvaluateProjectProfile = 'perfiles.evaluar';
    case ObserveProjectVersion = 'versiones.observar';
    case ReviewProjectVersion = 'versiones.revisar';
    case ScheduleDefense = 'defensas.programar';
    case RegisterPreDefense = 'pre-defensas.registrar';
    case GradeFormalDefense = 'defensas.calificar';
}
