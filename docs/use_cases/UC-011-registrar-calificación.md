# Use Case: Registrar calificación

## Overview

**Use Case ID:** UC-011  
**Use Case Name:** Registrar calificación  
**Primary Actor:** Miembro del tribunal  
**Goal:** Registrar su calificación de la defensa formal, o la inasistencia del estudiante, para que con las de los otros dos miembros el trámite de titulación quede concluido con su calificación conjunta  
**Trigger:** Termina la defensa formal programada, o el estudiante no se presenta a ella  
**Status:** Draft  

**Requirements:** [FR-019, FR-021, FR-042, NFR-004, NFR-005, NFR-009, NFR-013, C-007, C-016](../requirements.md)  
**Layers:** UI

## Preconditions

- El miembro del tribunal inició sesión con el rol docente.

## Main Success Scenario

1. El miembro del tribunal solicita registrar una defensa formal.
2. El sistema muestra las defensas formales de los trámites de titulación de cuyo tribunal es miembro vigente, cuya hora de inicio ya pasó y que esperan su calificación.
3. El miembro del tribunal elige una defensa formal.
4. El sistema muestra la versión del proyecto que se defiende para descargarla (UC-004 BR-006) y qué miembros del tribunal ya registraron su calificación, sin mostrar sus calificaciones.
5. El miembro del tribunal ingresa su calificación.
6. El miembro del tribunal confirma su calificación.
7. El sistema verifica que la calificación es un número entero entre 0 y 100.
8. El sistema registra la calificación del miembro con la fecha y hora.
9. El sistema verifica que los tres miembros vigentes del tribunal registraron su calificación.
10. El sistema calcula la calificación conjunta como el promedio de las tres, redondeado al entero, marca la defensa formal como realizada y concluye el trámite de titulación, aprobado con 51 o más o reprobado con menos, con la fecha y hora de conclusión.
11. El sistema notifica al estudiante, a su tutor y a los miembros del tribunal la calificación conjunta y si el trámite de titulación concluyó aprobado o reprobado.
12. El sistema muestra la confirmación con el estado de la defensa formal.

## Alternative Flows

### A1: Faltan calificaciones de otros miembros

**Trigger:** Al menos un miembro vigente del tribunal aún no registró su calificación (step 9)  
**Flow:**

1. El sistema deja la defensa formal sin realizar, con las calificaciones registradas hasta ahora.
2. Use case continues at step 12.

### A2: El estudiante no se presentó

**Trigger:** El miembro del tribunal indica que el estudiante no se presentó a la defensa formal (step 5)  
**Flow:**

1. El miembro del tribunal confirma la inasistencia del estudiante.
2. El sistema marca la defensa formal como realizada con la inasistencia del estudiante, sin calificación, y registra el trámite de titulación como abandonado, con la fecha y hora.
3. El sistema notifica al estudiante, a su tutor y a los demás miembros del tribunal que el trámite de titulación quedó abandonado por inasistencia a la defensa formal.
4. Use case continues at step 12.

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

## Postconditions

### Success Postconditions

- El miembro del tribunal tiene registrada su calificación de la defensa formal, con la fecha y hora.
- Con las tres calificaciones de los miembros vigentes, la defensa formal está realizada con la calificación conjunta, el trámite de titulación está concluido, aprobado o reprobado, con la fecha y hora de conclusión, y el estudiante, su tutor y los miembros del tribunal tienen una notificación del resultado.
- Tras una inasistencia, la defensa formal está realizada con la inasistencia del estudiante y sin calificación, el trámite de titulación está abandonado con la fecha y hora, y el estudiante, su tutor y los demás miembros del tribunal tienen una notificación del abandono.
- Mientras falte una calificación, la defensa formal sigue sin realizar y el trámite de titulación en curso.

### Failure Postconditions

- No se registra una calificación fuera del rango de 0 a 100 ni una calificación conjunta con menos de tres calificaciones.
- Un trámite de titulación no queda concluido sin su calificación conjunta, ni abandonado sin una inasistencia confirmada.

## Business Rules

### BR-001: Quién califica

Solo un miembro vigente del tribunal evaluador del trámite de titulación registra su calificación de la defensa formal, una vez. Una calificación registrada es definitiva y no se corrige.

### BR-002: Desde cuándo

Una defensa formal se registra desde su hora de inicio programada, ese día o después; nunca antes (UC-009).

### BR-003: Calificación conjunta

Cada miembro vigente califica con un número entero entre 0 y 100 (C-007). La calificación conjunta es el promedio de las tres calificaciones, redondeado al entero con las mitades hacia arriba, y se calcula cuando el tercer miembro vigente registra la suya. Ningún miembro ve las calificaciones de los demás antes de registrar la suya.

### BR-004: Conclusión del trámite

Con la calificación conjunta, la defensa formal queda realizada y el trámite de titulación concluye: aprobado con 51 o más, reprobado con menos (C-016). Un trámite reprobado permite al estudiante iniciar uno nuevo (UC-001 BR-003). La calificación conjunta es definitiva.

### BR-005: Inasistencia a la defensa formal

Basta con que un miembro vigente del tribunal registre que el estudiante no se presentó: la defensa formal queda realizada con la inasistencia del estudiante, sin calificación, y el trámite de titulación queda abandonado (UC-001 BR-008, UC-010 BR-004).
