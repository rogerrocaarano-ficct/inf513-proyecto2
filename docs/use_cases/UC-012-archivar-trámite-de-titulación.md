# Use Case: Archivar trámite de titulación

## Overview

**Use Case ID:** UC-012  
**Use Case Name:** Archivar trámite de titulación  
**Primary Actor:** Asistente administrativo  
**Goal:** Archivar un trámite de titulación concluido o abandonado, para que deje de figurar entre los trámites pendientes y su documentación quede disponible para consulta  
**Trigger:** El asistente administrativo solicita archivar los trámites de titulación que ya terminaron  
**Status:** Draft  

**Requirements:** [FR-020, FR-021, NFR-005, NFR-008, NFR-009](../requirements.md)  
**Layers:** UI

## Preconditions

- El asistente administrativo inició sesión con el rol asistente administrativo.

## Main Success Scenario

1. El asistente administrativo solicita archivar trámites de titulación.
2. El sistema muestra los trámites de titulación concluidos o abandonados que no están archivados, con el estudiante, el título del proyecto de grado, el resultado (aprobado, reprobado o abandonado) y su fecha, los más antiguos primero.
3. El asistente administrativo elige un trámite de titulación.
4. El sistema muestra el resumen del trámite de titulación: estudiante, título del proyecto de grado, tutor, tribunal evaluador, documentación de requisitos, versiones del proyecto y calificación o abandono.
5. El asistente administrativo confirma el archivo.
6. El sistema registra el trámite de titulación como archivado, con la fecha y hora, y lo pasa a la fase archivo.
7. El sistema notifica al estudiante que su trámite de titulación fue archivado.
8. El sistema muestra la confirmación y la lista de trámites de titulación por archivar, sin el archivado.

## Alternative Flows

### A1: No hay trámites por archivar

**Trigger:** Ningún trámite de titulación concluido o abandonado espera su archivo (step 2)  
**Flow:**

1. El sistema informa que no hay trámites de titulación por archivar.
2. Use case ends.

### A2: El asistente administrativo desiste

**Trigger:** El asistente administrativo no confirma el archivo (step 5)  
**Flow:**

1. El asistente administrativo vuelve a la lista sin archivar.
2. Use case ends.

## Postconditions

### Success Postconditions

- El trámite de titulación está archivado, en la fase archivo, con la fecha y hora del archivo.
- El trámite de titulación conserva su documentación de requisitos, sus versiones del proyecto, sus observaciones y su calificación o su abandono, disponibles para consulta.
- El estudiante tiene una notificación del archivo.

### Failure Postconditions

- El trámite de titulación no cambia de fase ni de estado.

## Business Rules

### BR-001: Trámites que se archivan

Solo se archiva un trámite de titulación concluido, aprobado o reprobado (UC-011 BR-004), o abandonado por inasistencia (UC-001 BR-008). Un trámite en curso no se archiva.

### BR-002: El archivo conserva todo

Archivar no borra ni cambia nada del trámite de titulación: su documentación de requisitos, sus versiones del proyecto y sus observaciones se conservan y se pueden descargar (NFR-008).

### BR-003: Archivo definitivo

Un trámite de titulación archivado no vuelve a otra fase ni recibe cambios.

### BR-004: Trámites archivados en las consultas

Un trámite de titulación archivado deja de figurar entre los trámites pendientes de archivo y entre los trámites en curso (UC-014), pero sigue disponible para consulta y búsqueda (UC-013, UC-015).
