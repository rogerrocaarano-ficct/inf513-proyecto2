# Use Case: Asignar tribunal evaluador

## Overview

**Use Case ID:** UC-006  
**Use Case Name:** Asignar tribunal evaluador  
**Primary Actor:** Director de carrera  
**Goal:** Asignar tres docentes como tribunal evaluador de un trámite de titulación con perfil de proyecto aprobado, o reemplazar a uno de ellos, para que el proyecto de grado tenga quién lo evalúe  
**Trigger:** El director de carrera solicita asignar el tribunal evaluador de un trámite de titulación  
**Status:** Approved  

**Requirements:** [FR-008, FR-018, FR-021, FR-022, NFR-004, NFR-005, NFR-009, C-006](../requirements.md)  
**Layers:** UI

## Preconditions

- El director de carrera inició sesión y tiene el rol director de carrera.

## Main Success Scenario

1. El director de carrera solicita asignar el tribunal evaluador.
2. El sistema muestra los trámites de titulación en curso con perfil de proyecto aprobado, primero los que no tienen tribunal evaluador, con su estudiante, el título del proyecto de grado, su tutor y su tribunal evaluador cuando lo tienen.
3. El director de carrera elige un trámite de titulación sin tribunal evaluador.
4. El sistema muestra los docentes elegibles como miembros del tribunal de ese trámite de titulación (BR-001).
5. El director de carrera elige tres docentes.
6. El director de carrera confirma la asignación.
7. El sistema verifica que los docentes elegidos son exactamente tres y distintos.
8. El sistema registra a los tres docentes como miembros del tribunal del trámite de titulación, con la fecha y hora de la asignación, y pasa el trámite de la fase evaluación de perfil a la fase pre-defensa.
9. El sistema notifica a cada miembro del tribunal que se le asignó el trámite de titulación, y al estudiante que su trámite pasó a la fase pre-defensa, con los miembros de su tribunal evaluador.
10. El sistema muestra la confirmación con el tribunal evaluador y la fase del trámite de titulación.

## Alternative Flows

### A1: No son tres docentes

**Trigger:** El director de carrera eligió menos o más de tres docentes (step 7)  
**Flow:**

1. El sistema informa, con un mensaje en español, que el tribunal evaluador se compone de exactamente tres docentes, y conserva la elección.
2. El director de carrera corrige la elección.
3. Use case continues at step 6.

### A2: Reemplazo de un miembro del tribunal

**Trigger:** El director de carrera elige un trámite de titulación que ya tiene tribunal evaluador (step 3)  
**Flow:**

1. El sistema muestra los miembros del tribunal vigentes y los docentes elegibles (BR-001).
2. El director de carrera elige al miembro que reemplaza y al docente que entra en su lugar, y confirma el reemplazo.
3. El sistema verifica que el nuevo docente no tiene un choque de horario con una pre-defensa o defensa formal programada del trámite de titulación (BR-006).
4. El sistema registra la fecha y hora del reemplazo del miembro anterior y registra al nuevo docente como miembro del tribunal, sin cambiar la fase del trámite de titulación ni la programación de sus actos.
5. El sistema notifica al nuevo miembro que se le asignó el trámite de titulación, al miembro anterior que dejó de serlo y al estudiante que su tribunal evaluador tiene un nuevo miembro.
6. Use case continues at step 10.

### A3: El reemplazo ya no es posible

**Trigger:** El trámite de titulación elegido para un reemplazo ya realizó su defensa formal (step 3)  
**Flow:**

1. El sistema informa que el tribunal evaluador ya no se puede cambiar porque la defensa formal se realizó.
2. Use case ends.

### A4: El director de carrera desiste

**Trigger:** El director de carrera no confirma la asignación o el reemplazo (step 6)  
**Flow:**

1. El director de carrera cancela la operación.
2. Use case ends.

### A5: El nuevo miembro tiene un choque de horario

**Trigger:** En un reemplazo (A2), el nuevo docente tiene otra pre-defensa o defensa formal que se cruza con un acto programado del trámite de titulación (step 6)  
**Flow:**

1. El sistema informa con qué pre-defensa o defensa formal se cruza el nuevo docente, no hace el reemplazo e indica que el director de carrera debe reprogramar antes el acto (UC-009 A3) o elegir otro docente.
2. Use case ends.

## Postconditions

### Success Postconditions

- El trámite de titulación tiene exactamente tres miembros del tribunal vigentes, cada uno con la fecha y hora de su asignación.
- Un trámite de titulación que no tenía tribunal evaluador quedó en la fase pre-defensa; un reemplazo no cambia la fase.
- Cada miembro asignado tiene una notificación de la asignación y el estudiante una notificación del cambio; tras un reemplazo, el miembro anterior tiene una notificación de que dejó de serlo y conserva la fecha y hora de su reemplazo.
- Las observaciones y aprobaciones de un miembro reemplazado se conservan con su autor.

### Failure Postconditions

- El tribunal evaluador y la fase del trámite de titulación no cambian.

## Business Rules

### BR-001: Docente elegible como miembro del tribunal

Es elegible como miembro del tribunal de un trámite de titulación un docente activo que no es el director de carrera, que no es el tutor de ese trámite y que no es ni fue miembro de su tribunal. El docente de taller de grado es elegible, también el que evaluó el perfil de proyecto del trámite.

### BR-002: Composición del tribunal

El tribunal evaluador de un trámite de titulación se compone de exactamente tres docentes distintos (C-006), y siempre tiene tres miembros vigentes desde su asignación.

### BR-003: Trámites que reciben tribunal

El tribunal evaluador se asigna solo a un trámite de titulación en curso, en la fase evaluación de perfil y con su perfil de proyecto aprobado (UC-005 BR-004). La asignación pasa el trámite a la fase pre-defensa.

### BR-004: Reemplazo de un miembro

El director de carrera reemplaza a un miembro a la vez, en un trámite de titulación en curso cuya defensa formal no se realizó. Las observaciones del miembro reemplazado se conservan con su autor; su aprobación de una versión del proyecto no cuenta para los tres miembros vigentes, así que el nuevo miembro revisa la versión en revisión.

### BR-005: Acceso del miembro reemplazado

El miembro reemplazado deja de participar en el trámite de titulación y ya no puede verlo (NFR-004), salvo que participe en él por otro papel.

### BR-006: Reemplazo con un acto programado

Un reemplazo no se confirma si el nuevo docente tiene un choque de horario (UC-009 BR-003) con una pre-defensa o defensa formal programada sin realizar del trámite de titulación; el director de carrera la reprograma antes (UC-009 BR-007). Sin choque, la programación se conserva.
