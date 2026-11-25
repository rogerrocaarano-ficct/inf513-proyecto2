# Use Case: Observar versión del proyecto como tutor

## Overview

**Use Case ID:** UC-007  
**Use Case Name:** Observar versión del proyecto como tutor  
**Primary Actor:** Tutor  
**Goal:** Registrar observaciones sobre una versión del proyecto de un estudiante que tutela, para que mejore su proyecto de grado antes de la revisión del tribunal evaluador  
**Trigger:** El tutor solicita revisar las versiones del proyecto de sus estudiantes  
**Status:** Draft  

**Requirements:** [FR-012, FR-021, NFR-004, NFR-005, NFR-009, NFR-013](../requirements.md)  
**Layers:** UI

## Preconditions

- El tutor inició sesión con el rol docente.

## Main Success Scenario

1. El tutor solicita ver los trámites de titulación de los que es tutor.
2. El sistema muestra sus trámites de titulación en curso con el estudiante, la fase y la última versión del proyecto, primero los que tienen una versión que el tutor aún no observó.
3. El tutor elige una versión del proyecto de uno de esos trámites.
4. El sistema muestra la versión del proyecto para descargarla, con su fase, su estado y las observaciones que ya tiene, cada una con su autor.
5. El tutor escribe una o más observaciones.
6. El tutor confirma las observaciones.
7. El sistema registra cada observación sobre la versión del proyecto, con el tutor como autor y la fecha y hora, sin cambiar el estado de la versión.
8. El sistema notifica al estudiante que su versión del proyecto recibió observaciones de su tutor.
9. El sistema muestra la versión del proyecto con las observaciones registradas.

## Alternative Flows

### A1: Observación vacía o demasiado larga

**Trigger:** Una observación está vacía o supera los 2000 caracteres (step 6)  
**Flow:**

1. El sistema señala la observación vacía o demasiado larga con un mensaje en español y conserva lo escrito.
2. El tutor corrige la observación.
3. Use case continues at step 6.

### A2: El tutor no tiene trámites en curso

**Trigger:** El tutor no es tutor de ningún trámite de titulación en curso (step 2)  
**Flow:**

1. El sistema informa que no tiene trámites de titulación en curso como tutor.
2. Use case ends.

### A3: El tutor solo consulta

**Trigger:** El tutor no escribe observaciones y no confirma (step 5)  
**Flow:**

1. El tutor sale después de consultar la versión del proyecto.
2. Use case ends.

## Postconditions

### Success Postconditions

- La versión del proyecto tiene las nuevas observaciones del tutor, cada una con su autor y su fecha y hora.
- El estado de la versión del proyecto no cambió.
- El estudiante tiene una notificación de las observaciones de su tutor.

### Failure Postconditions

- No se registra ninguna observación incompleta, y la versión del proyecto conserva su estado y sus observaciones.

## Business Rules

### BR-001: Quién observa

Solo el tutor vigente de un trámite de titulación en curso registra observaciones como tutor sobre sus versiones del proyecto (UC-002 BR-004).

### BR-002: Versiones que se observan

El tutor observa cualquier versión del proyecto de su trámite de titulación, de las fases evaluación de perfil, pre-defensa o defensa formal, mientras el trámite está en curso.

### BR-003: Observaciones orientativas

Las observaciones del tutor no cambian el estado de una versión del proyecto ni frenan la revisión del revisor o del tribunal evaluador (UC-004 BR-004).

### BR-004: Observaciones fijas

Una observación registrada no se corrige ni se borra; si el tutor quiere precisarla, registra una nueva. Cada observación tiene a lo sumo 2000 caracteres.
