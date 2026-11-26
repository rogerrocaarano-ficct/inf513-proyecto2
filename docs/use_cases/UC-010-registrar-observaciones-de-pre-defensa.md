# Use Case: Registrar observaciones de pre-defensa

## Overview

**Use Case ID:** UC-010  
**Use Case Name:** Registrar observaciones de pre-defensa  
**Primary Actor:** Miembro del tribunal  
**Goal:** Registrar sus observaciones de la pre-defensa sobre el documento y el prototipo, o la inasistencia del estudiante, para que el estudiante sepa qué subsanar antes de la defensa formal  
**Trigger:** Termina la pre-defensa programada, o el estudiante no se presenta a ella  
**Status:** Reviewed  

**Requirements:** [FR-016, FR-021, FR-042, NFR-004, NFR-005, NFR-009, NFR-013, C-005, C-006](../requirements.md)  
**Layers:** UI

## Preconditions

- El miembro del tribunal inició sesión con el rol docente.

## Main Success Scenario

1. El miembro del tribunal solicita registrar una pre-defensa.
2. El sistema muestra las pre-defensas de los trámites de titulación de cuyo tribunal es miembro vigente, cuya hora de inicio ya pasó y que esperan su registro.
3. El miembro del tribunal elige una pre-defensa.
4. El sistema muestra la versión del proyecto aprobada de la fase pre-defensa para descargarla, y qué miembros del tribunal ya registraron la pre-defensa.
5. El miembro del tribunal ingresa sus observaciones sobre el documento y el prototipo, indica que no tiene observaciones o indica que el estudiante no se presentó.
6. El miembro del tribunal confirma su decisión. El sistema verifica que el trámite sigue en curso en la fase pre-defensa, que el acto sigue pendiente, conserva la programación consultada y ya pasó su hora de inicio, y que el miembro sigue vigente y aún no tiene un registro.
7. El sistema registra la pre-defensa por ese miembro, con la fecha y hora, y sus observaciones con el miembro como autor.
8. El sistema verifica que los tres miembros vigentes del tribunal registraron la pre-defensa.
9. El sistema marca la pre-defensa como realizada y pasa el trámite de titulación de la fase pre-defensa a la fase defensa formal.
10. El sistema notifica dentro del sistema al estudiante y a su tutor que la pre-defensa quedó registrada, con todas las observaciones del tribunal o con la indicación de que no las hubo, y que el trámite pasó a la fase defensa formal.
11. El sistema muestra la confirmación con el estado de la pre-defensa.

## Alternative Flows

### A1: Faltan registros de otros miembros

**Trigger:** Al menos un miembro vigente del tribunal aún no registró la pre-defensa (step 8)  
**Flow:**

1. El sistema deja la pre-defensa sin realizar, con los registros hechos hasta ahora.
2. Use case continues at step 11.

### A2: El estudiante no se presentó

**Trigger:** El miembro del tribunal confirmó la inasistencia del estudiante y la validación del paso 6 pasó (step 7)  
**Flow:**

1. El sistema marca la pre-defensa como realizada con la inasistencia del estudiante, y registra el trámite de titulación como abandonado, con la fecha y hora.
2. El sistema notifica dentro del sistema al estudiante, a su tutor y a los demás miembros vigentes del tribunal que el trámite de titulación quedó abandonado por inasistencia a la pre-defensa.
3. Use case continues at step 11.

### A3: Decisión u observaciones inválidas

**Trigger:** El miembro confirma sin elegir una opción, elige registrar observaciones sin ingresar ninguna o confirma una observación vacía o de más de 2000 caracteres (step 6)  
**Flow:**

1. El sistema indica en español qué opción u observación falta o cuál es inválida, y conserva lo escrito.
2. El miembro del tribunal elige una opción o corrige las observaciones.
3. Use case continues at step 6.

### A4: No hay pre-defensas por registrar

**Trigger:** Ninguna pre-defensa de sus tribunales espera su registro (step 2)  
**Flow:**

1. El sistema informa que no hay pre-defensas pendientes de su registro.
2. Use case ends.

### A5: El registro ya no está disponible

**Trigger:** Al confirmar, el trámite ya no está en curso en la fase pre-defensa, el acto ya se marcó como realizado o se reprogramó, el miembro dejó de estar vigente o ya tiene un registro (step 6)  
**Flow:**

1. El sistema informa en español que el registro ya no está disponible y actualiza la lista de pre-defensas pendientes.
2. Use case ends.

## Postconditions

### Success Postconditions

- Si el estudiante asistió, el miembro del tribunal tiene un registro con la fecha y hora y con sus observaciones, cada una con su autor, o con la indicación de que no dejó observaciones.
- Con los tres miembros vigentes registrados, la pre-defensa está realizada, el trámite de titulación está en la fase defensa formal, y el estudiante y su tutor tienen una notificación con las observaciones de la pre-defensa.
- Tras una inasistencia, la pre-defensa está realizada con la inasistencia del estudiante, el trámite de titulación está abandonado con la fecha y hora, y el estudiante, su tutor y los demás miembros del tribunal tienen una notificación del abandono.
- Si el estudiante asistió y falta el registro de un miembro vigente, la pre-defensa sigue sin realizar y el trámite en la fase pre-defensa.

### Failure Postconditions

- Este intento no agrega registros ni observaciones, no cambia el estado de la pre-defensa ni del trámite y no genera notificaciones.

## Business Rules

### BR-001: Quién registra

Solo un miembro vigente del tribunal evaluador del trámite de titulación registra su pre-defensa, una vez por pre-defensa. Un registro hecho no se cambia.

### BR-002: Desde cuándo

Una pre-defensa se registra después de su hora de inicio programada (UC-009).

### BR-003: Pre-defensa realizada

Cada miembro vigente registra sus propias observaciones sobre el documento y el prototipo, o que no tiene observaciones. La pre-defensa queda realizada cuando los tres miembros vigentes la registraron, y entonces el trámite de titulación pasa a la fase defensa formal. Si al menos un miembro registró observaciones, el estudiante puede presentar versiones del proyecto en la fase defensa formal (UC-004 BR-006).

### BR-004: Inasistencia a la pre-defensa

Basta con que un miembro vigente del tribunal registre que el estudiante no se presentó: la pre-defensa queda realizada con la inasistencia del estudiante y el trámite de titulación queda abandonado (UC-001 BR-008). Un trámite abandonado no continúa y no recibe defensa formal.

### BR-005: Observaciones de la pre-defensa

Las observaciones de la pre-defensa se refieren a la pre-defensa, no a una versión del proyecto; no se corrigen ni se borran, y tienen a lo sumo 2000 caracteres cada una.
