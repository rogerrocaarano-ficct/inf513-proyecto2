# Use Case: Asignar tutor

## Overview

**Use Case ID:** UC-002  
**Use Case Name:** Asignar tutor  
**Primary Actor:** Director de carrera  
**Goal:** Dejar a un docente activo como tutor de un trámite de titulación en curso, para que el estudiante tenga quién guíe su proyecto de grado  
**Trigger:** El director de carrera decide asignar o cambiar el tutor de un trámite de titulación  
**Status:** Reviewed  

**Requirements:** [FR-004, FR-018, FR-021, FR-022, NFR-004, NFR-005, NFR-009](../requirements.md)  
**Layers:** UI

## Preconditions

- El director de carrera inició sesión y tiene el rol director de carrera.
- El administrador del sistema registró docentes activos además del docente de taller de grado (UC-018).

## Main Success Scenario

1. El director de carrera solicita asignar tutor.
2. El sistema muestra los trámites de titulación en curso con su estudiante, su fase y su tutor, primero los que están sin tutor, con el nombre del tutor propuesto cuando lo hay.
3. El director de carrera elige un trámite de titulación sin tutor.
4. El sistema muestra los docentes elegibles como tutor de ese trámite de titulación (BR-001) y el nombre del tutor propuesto, cuando lo hay.
5. El director de carrera elige al docente.
6. El director de carrera confirma la asignación.
7. El sistema asigna al docente como tutor del trámite de titulación y lo pasa de la fase requisitos a la fase evaluación de perfil.
8. El sistema notifica al tutor que se le asignó el trámite de titulación y al estudiante que su trámite pasó a la fase evaluación de perfil.
9. El sistema muestra la confirmación con el tutor asignado y la fase del trámite de titulación.

## Alternative Flows

### A1: Cambio de tutor

**Trigger:** El director de carrera elige un trámite de titulación que ya tiene tutor (step 3)  
**Flow:**

1. El sistema muestra el tutor actual y los docentes elegibles como tutor del trámite de titulación (BR-001).
2. El director de carrera elige al nuevo tutor y confirma el cambio.
3. El sistema verifica que el nuevo tutor no tiene un choque de horario con una pre-defensa o defensa formal programada del trámite de titulación (BR-006).
4. El sistema reemplaza al tutor del trámite de titulación sin cambiar su fase ni la programación de sus actos.
5. El sistema notifica al nuevo tutor que se le asignó el trámite de titulación y al estudiante que su trámite tiene un nuevo tutor (BR-005).
6. Use case continues at step 9.

### A2: El director de carrera desiste

**Trigger:** El director de carrera no confirma la asignación (step 6), o no confirma el cambio de tutor en A1  
**Flow:**

1. El director de carrera cancela la operación.
2. Use case ends.

### A3: No hay trámites en curso

**Trigger:** No hay trámites de titulación en curso que mostrar (step 2)  
**Flow:**

1. El sistema informa que no hay trámites de titulación en curso.
2. Use case ends.

### A4: El nuevo tutor tiene un choque de horario

**Trigger:** En un cambio de tutor (A1), el nuevo tutor tiene otra pre-defensa o defensa formal que se cruza con un acto programado del trámite de titulación (step 6)  
**Flow:**

1. El sistema informa con qué pre-defensa o defensa formal se cruza el nuevo tutor, no cambia el tutor e indica que el director de carrera debe reprogramar antes el acto (UC-009 A3) o elegir otro docente.
2. Use case ends.

## Postconditions

### Success Postconditions

- El trámite de titulación tiene como tutor al docente elegido.
- Un trámite de titulación que estaba sin tutor quedó en la fase evaluación de perfil; uno que cambió de tutor conserva su fase.
- El tutor asignado tiene una notificación de la asignación y el estudiante una notificación del cambio; tras un cambio, el tutor anterior no recibe notificación.
- Las versiones del proyecto y las observaciones del trámite de titulación se conservan con su autor, también las del tutor anterior.

### Failure Postconditions

- El trámite de titulación conserva su tutor y su fase.

## Business Rules

### BR-001: Docente elegible como tutor

Es elegible como tutor de un trámite de titulación un docente activo que no es el docente de taller de grado, que no es miembro del tribunal de ese trámite y que no es ya su tutor.

### BR-002: Solo trámites en curso

El tutor se asigna o se cambia solo en un trámite de titulación en curso; un trámite concluido o abandonado conserva el tutor que tenía (UC-001 BR-003).

### BR-003: Fase al asignar el tutor

Asignar tutor a un trámite de titulación en la fase requisitos lo pasa a la fase evaluación de perfil. Cambiar el tutor no cambia la fase.

### BR-004: Acceso del tutor anterior

Tras un cambio de tutor, el tutor anterior deja de participar en el trámite de titulación y ya no puede verlo (NFR-004), salvo que participe en él por otro papel.

### BR-005: Notificación del cambio de tutor

Un cambio de tutor se notifica al nuevo tutor (FR-022) y al estudiante (FR-021); el tutor anterior no recibe notificación, a diferencia del miembro del tribunal reemplazado (UC-006 A2).

### BR-006: Cambio de tutor con un acto programado

Un cambio de tutor no se confirma si el nuevo tutor tiene un choque de horario (UC-009 BR-003) con una pre-defensa o defensa formal programada sin realizar del trámite de titulación; el director de carrera la reprograma antes (UC-009 BR-007). Sin choque, la programación se conserva.
