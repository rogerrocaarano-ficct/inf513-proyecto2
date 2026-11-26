# Use Case: Registrar calificación

## Overview

**Use Case ID:** UC-011  
**Use Case Name:** Registrar calificación  
**Primary Actor:** Miembro del tribunal  
**Goal:** Registrar su calificación de la defensa formal, o la inasistencia del estudiante, para que con las de los otros dos miembros el trámite de titulación quede concluido con su calificación conjunta  
**Trigger:** Termina la defensa formal programada, o el estudiante no se presenta a ella  
**Status:** Reviewed  

**Requirements:** [FR-019, FR-021, FR-042, NFR-004, NFR-005, NFR-009, NFR-013, C-005, C-006, C-007, C-016](../requirements.md)  
**Layers:** UI

## Preconditions

- El miembro del tribunal inició sesión y tiene el rol docente.

## Main Success Scenario

1. El miembro del tribunal solicita registrar una defensa formal.
2. El sistema muestra las defensas formales de los trámites de titulación de cuyo tribunal es miembro vigente, cuya hora de inicio ya pasó y que esperan su calificación.
3. El miembro del tribunal elige una defensa formal.
4. El sistema muestra la versión del proyecto que se defiende para descargarla (UC-004 BR-006) y qué miembros del tribunal ya registraron su calificación, sin mostrar sus calificaciones.
5. El miembro del tribunal ingresa su calificación o, mientras ningún miembro haya registrado su calificación, indica que el estudiante no se presentó (BR-005).
6. El miembro del tribunal confirma su decisión. El sistema verifica que el trámite sigue en curso en la fase defensa formal, que el acto sigue pendiente, conserva la programación consultada y ya pasó su hora de inicio, que el miembro sigue vigente y aún no registró una calificación y, si indicó la inasistencia, que ningún miembro registró aún su calificación.
7. El sistema verifica que la calificación es un número entero entre 0 y 100.
8. El sistema registra la calificación del miembro con la fecha y hora.
9. El sistema verifica que los tres miembros vigentes del tribunal registraron su calificación.
10. El sistema calcula y redondea la calificación conjunta (BR-003), marca la defensa formal como realizada y concluye el trámite de titulación con su fecha y hora. El resultado se decide sobre la calificación redondeada (BR-004).
11. El sistema notifica dentro del sistema al estudiante, a su tutor y a los miembros vigentes del tribunal la calificación conjunta y si el trámite de titulación concluyó aprobado o reprobado.
12. El sistema muestra la confirmación con el estado de la defensa formal.

## Alternative Flows

### A1: Faltan calificaciones de otros miembros

**Trigger:** Al menos un miembro vigente del tribunal aún no registró su calificación (step 9)  
**Flow:**

1. El sistema deja la defensa formal sin realizar, con las calificaciones registradas hasta ahora.
2. Use case continues at step 12.

### A2: El estudiante no se presentó

**Trigger:** El miembro del tribunal confirmó la inasistencia del estudiante y la validación del paso 6 pasó (step 7)  
**Flow:**

1. El sistema marca la defensa formal como realizada con la inasistencia del estudiante y el miembro que la registró, sin calificación conjunta, y registra el trámite de titulación como abandonado, con la fecha y hora.
2. El sistema notifica dentro del sistema al estudiante, a su tutor y a los demás miembros vigentes del tribunal que el trámite de titulación quedó abandonado por inasistencia a la defensa formal.
3. Use case continues at step 12.

### A3: Calificación inválida

**Trigger:** La calificación falta, no es un número entero o está fuera del rango de 0 a 100 (step 7)  
**Flow:**

1. El sistema informa, con un mensaje en español, que la calificación es un número entero entre 0 y 100, y conserva lo ingresado.
2. El miembro del tribunal corrige la calificación.
3. Use case continues at step 6.

### A4: No hay defensas formales por calificar

**Trigger:** Ninguna defensa formal de sus tribunales espera su calificación (step 2)  
**Flow:**

1. El sistema informa que no hay defensas formales pendientes de su calificación.
2. Use case ends.

### A5: El registro ya no está disponible

**Trigger:** Al confirmar, el trámite ya no está en curso en la fase defensa formal, el acto ya se marcó como realizado o se reprogramó, el miembro dejó de estar vigente o ya registró una calificación, o indicó la inasistencia y otro miembro ya registró su calificación (step 6)  
**Flow:**

1. El sistema informa en español que el registro ya no está disponible y actualiza la lista de defensas formales pendientes.
2. Use case ends.

## Postconditions

### Success Postconditions

- Si el estudiante asistió, el miembro del tribunal tiene registrada su calificación de la defensa formal, con la fecha y hora.
- Con las tres calificaciones de los miembros vigentes, la defensa formal está realizada con la calificación conjunta, el trámite de titulación está concluido, aprobado o reprobado, con la fecha y hora de conclusión, y el estudiante, su tutor y los miembros del tribunal tienen una notificación del resultado.
- Tras una inasistencia, la defensa formal está realizada con la inasistencia del estudiante y sin calificación, el trámite de titulación está abandonado con la fecha y hora, y el estudiante, su tutor y los demás miembros del tribunal tienen una notificación del abandono.
- Si el estudiante asistió y falta la calificación de un miembro vigente, la defensa formal sigue sin realizar y el trámite de titulación en curso.

### Failure Postconditions

- Este intento no registra calificaciones ni inasistencias, no cambia el estado de la defensa formal ni del trámite y no genera notificaciones.

## Business Rules

### BR-001: Quién califica

Solo un miembro vigente del tribunal evaluador del trámite de titulación registra su calificación de la defensa formal, una vez. Una calificación registrada es definitiva y no se corrige.

### BR-002: Desde cuándo

Una defensa formal se registra después de su hora de inicio programada (UC-009).

### BR-003: Calificación conjunta

Cada miembro vigente califica con un número entero entre 0 y 100 (C-007). La calificación conjunta es el promedio de las tres calificaciones, redondeado al entero más cercano con las mitades hacia arriba. Se calcula cuando el tercer miembro vigente registra la suya. Hasta que se calcula la calificación conjunta, los miembros del tribunal pueden ver quiénes ya calificaron, pero no las calificaciones de los demás.

### BR-004: Conclusión del trámite

Con la calificación conjunta, la defensa formal queda realizada y el trámite de titulación concluye: aprobado con 51 o más, reprobado con menos (C-016). Un trámite reprobado permite al estudiante iniciar uno nuevo (UC-001 BR-003). La calificación conjunta es definitiva.

### BR-005: Inasistencia a la defensa formal

Basta con que un miembro vigente del tribunal registre que el estudiante no se presentó: la defensa formal queda realizada con la inasistencia del estudiante, con el miembro que la registró, sin calificación conjunta, y el trámite de titulación queda abandonado (UC-001 BR-008, UC-010 BR-004). La inasistencia se registra solo mientras ningún miembro haya registrado su calificación; una calificación registrada confirma que el estudiante se presentó.
