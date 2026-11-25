# Use Case: Evaluar perfil de proyecto

## Overview

**Use Case ID:** UC-005  
**Use Case Name:** Evaluar perfil de proyecto  
**Primary Actor:** Revisor  
**Goal:** Aprobar o rechazar un perfil de proyecto con las observaciones que lo justifican, para que el estudiante sepa si su proyecto de grado es viable para la modalidad de graduación  
**Trigger:** El revisor recibe la notificación de un perfil de proyecto que espera su revisión, o decide revisar sus perfiles de proyecto pendientes  
**Status:** Reviewed  

**Requirements:** [FR-007, FR-021, FR-041, NFR-004, NFR-005, NFR-009, NFR-013](../requirements.md)  
**Layers:** UI

## Preconditions

- El revisor inició sesión como el docente de taller de grado designado.

## Main Success Scenario

1. El revisor solicita ver los perfiles de proyecto que esperan su revisión.
2. El sistema muestra los perfiles de proyecto en revisión de los que es revisor, con el estudiante, el título y la fecha de presentación, los más antiguos primero.
3. El revisor elige un perfil de proyecto.
4. El sistema muestra el perfil de proyecto para descargarlo y los perfiles de proyecto rechazados anteriores del mismo trámite de titulación, con sus observaciones.
5. El revisor aprueba el perfil de proyecto y, si lo desea, registra observaciones.
6. El revisor confirma la evaluación.
7. El sistema registra el perfil de proyecto como aprobado, con la fecha y hora de la evaluación y las observaciones, y toma su título como título del proyecto de grado del trámite de titulación.
8. El sistema notifica al estudiante que su perfil de proyecto fue aprobado.
9. El sistema muestra la confirmación de la evaluación.

## Alternative Flows

### A1: Rechazo del perfil de proyecto

**Trigger:** El revisor rechaza el perfil de proyecto (step 5)  
**Flow:**

1. El revisor registra al menos una observación que justifica el rechazo.
2. El revisor confirma la evaluación.
3. El sistema registra el perfil de proyecto como rechazado, con la fecha y hora de la evaluación y las observaciones.
4. El sistema notifica al estudiante que su perfil de proyecto fue rechazado y que puede presentar uno nuevo con las observaciones subsanadas.
5. Use case continues at step 9.

### A2: Rechazo sin observaciones

**Trigger:** El revisor confirma un rechazo sin ninguna observación (step 6)  
**Flow:**

1. El sistema informa, con un mensaje en español, que el rechazo necesita al menos una observación, y conserva lo ingresado.
2. El revisor registra la observación.
3. Use case continues at step 6.

### A3: No hay perfiles de proyecto pendientes

**Trigger:** El revisor no tiene perfiles de proyecto en revisión (step 2)  
**Flow:**

1. El sistema informa que no hay perfiles de proyecto que esperen su revisión.
2. Use case ends.

### A4: El revisor deja la evaluación para después

**Trigger:** El revisor no confirma la evaluación (step 6)  
**Flow:**

1. El revisor sale sin confirmar.
2. Use case ends.

## Postconditions

### Success Postconditions

- El perfil de proyecto está aprobado o rechazado, con la fecha y hora de la evaluación y sus observaciones, cada una con su autor.
- Un perfil de proyecto aprobado deja su título como título del proyecto de grado del trámite de titulación, y el trámite queda a la espera de la asignación del tribunal evaluador (UC-006).
- El estudiante tiene una notificación del resultado de la evaluación.

### Failure Postconditions

- El perfil de proyecto sigue en revisión, sin evaluación ni observaciones nuevas.

## Business Rules

### BR-001: Quién evalúa

Solo el revisor de un perfil de proyecto, el docente de taller de grado al que el perfil de proyecto está asignado, puede evaluarlo (UC-003 BR-003).

### BR-002: Resultado de la evaluación

Un perfil de proyecto se aprueba o se rechaza; no queda con observaciones pendientes. Un rechazo tiene al menos una observación; una aprobación puede tenerlas.

### BR-003: Evaluación definitiva

Un perfil de proyecto evaluado no cambia de resultado. Tras un rechazo, el estudiante presenta un nuevo perfil de proyecto (UC-004).

### BR-004: Perfil de proyecto aprobado

Un trámite de titulación tiene a lo sumo un perfil de proyecto aprobado. Con él, el trámite sigue en la fase evaluación de perfil hasta que el director de carrera le asigna el tribunal evaluador (UC-006).

### BR-005: Historial del revisor

El revisor ve, junto al perfil de proyecto que evalúa, los perfiles de proyecto rechazados anteriores del mismo trámite de titulación con sus observaciones, para compararlos (FR-041).
