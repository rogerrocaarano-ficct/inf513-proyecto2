# Use Case: Revisar versión del proyecto como tribunal

## Overview

**Use Case ID:** UC-008  
**Use Case Name:** Revisar versión del proyecto como tribunal  
**Primary Actor:** Miembro del tribunal  
**Goal:** Aprobar u observar una versión del proyecto de la fase pre-defensa, para que el estudiante sepa qué subsanar o, con la aprobación de los tres miembros, su proyecto de grado pueda ir a pre-defensa  
**Trigger:** El miembro del tribunal solicita revisar las versiones del proyecto que esperan su pronunciamiento  
**Status:** Draft  

**Requirements:** [FR-010, FR-011, FR-014, FR-021, NFR-004, NFR-005, NFR-009, NFR-013, C-005, C-006](../requirements.md)  
**Layers:** UI

## Preconditions

- El miembro del tribunal inició sesión y tiene el rol docente.

## Main Success Scenario

1. El miembro del tribunal solicita ver los trámites de titulación de cuyo tribunal evaluador es miembro.
2. El sistema muestra los trámites de titulación en curso de los que es miembro vigente del tribunal, primero los que tienen una versión del proyecto de la fase pre-defensa en revisión que espera su pronunciamiento.
3. El miembro del tribunal elige esa versión del proyecto.
4. El sistema muestra la versión del proyecto para descargarla, qué miembros del tribunal ya se pronunciaron, y las versiones anteriores del proyecto con su fase y sus observaciones, cada una con su autor (BR-005).
5. El miembro del tribunal aprueba la versión y, si lo desea, registra observaciones como sugerencias.
6. El miembro del tribunal confirma su pronunciamiento.
7. El sistema registra el pronunciamiento confirmado con la fecha y hora: la aprobación con las sugerencias que se hayan ingresado, o las observaciones sin aprobación. Cada observación queda con el miembro como autor.
8. El sistema verifica que los tres miembros vigentes del tribunal se pronunciaron y que los tres aprobaron la versión.
9. El sistema registra la versión del proyecto como aprobada, con la fecha y hora de la evaluación.
10. El sistema informa al estudiante mediante una notificación interna que el tribunal evaluador aprobó su versión del proyecto y que espera la programación de su pre-defensa.
11. El sistema muestra la confirmación con el estado de la versión del proyecto.

## Alternative Flows

### A1: El miembro observa la versión

**Trigger:** El miembro del tribunal no aprueba la versión (step 5)  
**Flow:**

1. El miembro del tribunal registra al menos una observación sobre lo que el estudiante debe subsanar.
2. Use case continues at step 6.

### A2: Faltan pronunciamientos

**Trigger:** Al menos un miembro vigente del tribunal aún no se pronunció sobre la versión (step 8)  
**Flow:**

1. El sistema deja la versión del proyecto en revisión.
2. Use case continues at step 11.

### A3: La versión queda con observaciones

**Trigger:** Los tres miembros vigentes se pronunciaron y al menos uno no aprobó la versión (step 8)  
**Flow:**

1. El sistema registra la versión del proyecto con observaciones, con la fecha y hora de la evaluación.
2. El sistema informa al estudiante mediante una notificación interna que su versión del proyecto recibió observaciones del tribunal evaluador y que puede presentar una nueva versión con ellas subsanadas.
3. Use case continues at step 11.

### A4: Observación vacía o demasiado larga

**Trigger:** El miembro confirma sin ninguna observación tras no aprobar, o confirma con una observación vacía o de más de 2000 caracteres (step 6)  
**Flow:**

1. El sistema indica en español si falta una observación obligatoria o cuál está vacía o es demasiado larga, y conserva lo escrito.
2. El miembro del tribunal corrige las observaciones.
3. Use case continues at step 6.

### A5: No hay versiones que esperen su pronunciamiento

**Trigger:** Ninguno de sus trámites de titulación tiene una versión de la fase pre-defensa que espere su pronunciamiento (step 2)  
**Flow:**

1. El sistema informa que no hay versiones del proyecto pendientes de su revisión.
2. Use case ends.

### A6: La versión ya no admite el pronunciamiento del miembro

**Trigger:** Al confirmar, el miembro fue reemplazado, el trámite dejó de estar en curso, la versión dejó de estar en revisión o el miembro ya tiene un pronunciamiento registrado para la versión (step 6)  
**Flow:**

1. El sistema informa en español que el pronunciamiento ya no puede registrarse.
2. Use case ends.

## Postconditions

### Success Postconditions

- El miembro del tribunal tiene un pronunciamiento sobre la versión del proyecto: su aprobación, con sus sugerencias si las dejó, o sus observaciones sin aprobación, cada observación con su autor y su fecha y hora.
- Con los tres miembros vigentes pronunciados, la versión del proyecto está aprobada si los tres la aprobaron, o con observaciones si alguno no la aprobó, con la fecha y hora de la evaluación, y el estudiante tiene una notificación del resultado.
- Mientras falte un pronunciamiento, la versión del proyecto sigue en revisión.

### Failure Postconditions

- Este intento no registra ni modifica pronunciamientos u observaciones, no cambia el estado de la versión ni genera una notificación.

## Business Rules

### BR-001: Quién revisa

Solo un miembro vigente del tribunal evaluador del trámite de titulación revisa sus versiones del proyecto de la fase pre-defensa en revisión. Las versiones de la fase defensa formal no se revisan (UC-004 BR-004).

### BR-002: Pronunciamiento del miembro

Cada miembro vigente se pronuncia una vez por versión del proyecto: la aprueba, con observaciones opcionales que son sugerencias, o no la aprueba y registra al menos una observación. Un pronunciamiento registrado no se cambia.

### BR-003: Resultado de la versión

Una versión de la fase pre-defensa sigue en revisión hasta que los tres miembros vigentes se pronuncian. Entonces queda aprobada si los tres la aprobaron, o con observaciones si alguno no la aprobó. Solo bloquean las observaciones de un miembro que no aprobó la versión; las sugerencias de quien aprobó y las observaciones del tutor no la bloquean (UC-007 BR-003).

### BR-004: Miembro reemplazado

El pronunciamiento de un miembro reemplazado no cuenta; el nuevo miembro se pronuncia sobre la versión en revisión (UC-006 BR-004).

### BR-005: Historial del tribunal

El miembro del tribunal ve, junto a la versión que revisa, todas las versiones anteriores del proyecto del trámite de titulación con su fase y sus observaciones, para comprobar si las observaciones fueron subsanadas (FR-014).

### BR-006: Observaciones fijas

Una observación registrada no se corrige ni se borra, y tiene a lo sumo 2000 caracteres.
