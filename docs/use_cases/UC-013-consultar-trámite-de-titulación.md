# Use Case: Consultar trámite de titulación

## Overview

**Use Case ID:** UC-013  
**Use Case Name:** Consultar trámite de titulación  
**Primary Actor:** Estudiante  
**Goal:** Ver el estado de su trámite de titulación, sus versiones del proyecto y sus observaciones, para saber qué paso le corresponde y qué debe subsanar sin depender del asistente administrativo  
**Trigger:** El estudiante solicita consultar su trámite de titulación  
**Status:** Draft  

**Requirements:** [FR-013, FR-023, FR-040, NFR-004, NFR-005, NFR-008, NFR-009](../requirements.md)  
**Layers:** UI

## Preconditions

- El estudiante inició sesión con el rol estudiante.

## Main Success Scenario

1. El estudiante solicita consultar su trámite de titulación.
2. El sistema muestra su trámite de titulación más reciente: la fase actual y las fases ya recorridas, cada una con la fecha en que el trámite entró en ella, el tutor, los miembros vigentes del tribunal evaluador y las pre-defensas y defensas formales programadas o realizadas con su fecha, hora y ambiente.
3. El sistema muestra las versiones del proyecto del trámite de titulación, de la más reciente a la primera, cada una con su número, su fase, su estado y su fecha de presentación.
4. El estudiante elige una versión del proyecto.
5. El sistema muestra las observaciones de esa versión, cada una con su autor, su papel (revisor, tutor o miembro del tribunal) y su fecha, y permite descargar el archivo de la versión.
6. El estudiante descarga el archivo de la versión del proyecto.

## Alternative Flows

### A1: Trámites de titulación anteriores

**Trigger:** El estudiante tiene trámites de titulación anteriores, reprobados o abandonados (step 2)  
**Flow:**

1. El sistema muestra también la lista de sus trámites de titulación anteriores, con su resultado y su fecha.
2. El estudiante elige uno de ellos.
3. El sistema muestra ese trámite de titulación como en el paso 2.
4. Use case continues at step 3.

### A2: Observaciones de la pre-defensa

**Trigger:** La pre-defensa del trámite de titulación ya se realizó con observaciones (step 2)  
**Flow:**

1. El sistema muestra, junto a la pre-defensa, las observaciones de cada miembro del tribunal con su autor y su fecha.
2. Use case continues at step 3.

### A3: Calificación de la defensa formal

**Trigger:** El trámite de titulación concluyó con su defensa formal (step 2)  
**Flow:**

1. El sistema muestra la calificación conjunta y si el trámite de titulación concluyó aprobado o reprobado, sin las calificaciones de cada miembro del tribunal.
2. Use case continues at step 3.

### A4: El estudiante solo consulta

**Trigger:** El estudiante no elige una versión del proyecto (step 4)  
**Flow:**

1. El estudiante sale después de ver el estado de su trámite de titulación.
2. Use case ends.

## Postconditions

### Success Postconditions

- El estudiante vio la fase actual y las fases concluidas de su trámite de titulación, sus versiones del proyecto con sus observaciones y autores, y descargó el archivo que eligió.

### Failure Postconditions

_None — the use case changes no data._

## Business Rules

### BR-001: Solo sus propios trámites

El estudiante ve solo sus propios trámites de titulación, en curso, concluidos, abandonados o archivados (NFR-004).

### BR-002: Todas las versiones

El estudiante ve y descarga todas las versiones del proyecto de sus trámites de titulación, desde el perfil de proyecto, con su fase y sus observaciones; ninguna se oculta ni se borra (NFR-008).

### BR-003: Autor de cada observación

Cada observación se muestra con su autor y su papel en el trámite de titulación: revisor, tutor o miembro del tribunal.

### BR-004: Calificaciones individuales

El estudiante ve la calificación conjunta de su defensa formal, no la calificación de cada miembro del tribunal (UC-011 BR-003).
