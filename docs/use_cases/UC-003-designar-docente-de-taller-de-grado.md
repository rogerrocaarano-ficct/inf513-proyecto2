# Use Case: Designar docente de taller de grado

## Overview

**Use Case ID:** UC-003  
**Use Case Name:** Designar docente de taller de grado  
**Primary Actor:** Director de carrera  
**Goal:** Designar al docente de taller de grado, para que todo perfil de proyecto le llegue como revisor sin asignarlo uno por uno  
**Trigger:** El director de carrera solicita designar al docente de taller de grado, al inicio de la gestión o cuando cambia el docente que dicta el taller  
**Status:** Reviewed  

**Requirements:** [FR-006, FR-022, NFR-004, NFR-005, NFR-009](../requirements.md)  
**Layers:** UI

## Preconditions

- El director de carrera inició sesión con el rol director de carrera.

## Main Success Scenario

1. El director de carrera solicita designar al docente de taller de grado.
2. El sistema muestra al docente de taller de grado actual y los demás docentes activos.
3. El director de carrera elige al nuevo docente de taller de grado.
4. El director de carrera confirma la designación.
5. El sistema designa al docente elegido como docente de taller de grado y retira la designación al docente anterior.
6. El sistema pasa al nuevo docente de taller de grado, como revisor, los perfiles de proyecto que estaban en revisión con el docente anterior.
7. El sistema notifica al nuevo docente de taller de grado su designación, con los perfiles de proyecto que recibe, y al docente anterior que dejó de ser docente de taller de grado.
8. El sistema muestra la confirmación con el docente designado y la cantidad de perfiles de proyecto que pasaron a su revisión.

## Alternative Flows

### A1: Primera designación

**Trigger:** No hay un docente de taller de grado designado (step 5)  
**Flow:**

1. El sistema designa al docente elegido como docente de taller de grado.
2. El sistema le asigna como revisor los perfiles de proyecto que esperan revisión sin revisor.
3. El sistema notifica al docente su designación, con los perfiles de proyecto que recibe.
4. Use case continues at step 8.

### A2: El director de carrera desiste

**Trigger:** El director de carrera no confirma la designación (step 4)  
**Flow:**

1. El director de carrera cancela la operación.
2. Use case ends.

## Postconditions

### Success Postconditions

- El docente elegido es el único docente de taller de grado.
- Todo perfil de proyecto en revisión, con el docente anterior o sin revisor, tiene como revisor al nuevo docente de taller de grado; los perfiles de proyecto ya evaluados conservan a su revisor.
- El nuevo docente de taller de grado tiene una notificación de su designación y, tras un cambio, el docente anterior tiene una notificación de que dejó de serlo.

### Failure Postconditions

- El docente de taller de grado y el revisor de cada perfil de proyecto no cambian.

## Business Rules

### BR-001: Un solo docente de taller de grado

Hay a lo sumo un docente de taller de grado; una vez designado el primero, siempre hay exactamente uno. Designar a otro retira la designación al anterior en el mismo acto.

### BR-002: Docente elegible

Solo un docente activo que no es ya el docente de taller de grado puede ser designado. Un docente que es tutor de trámites de titulación en curso puede ser designado y conserva esas tutorías, pero mientras sea docente de taller de grado no recibe tutorías nuevas (UC-001 BR-006, UC-002 BR-001).

### BR-003: Perfiles de proyecto en revisión

Al cambiar el docente de taller de grado, los perfiles de proyecto en revisión pasan al nuevo como revisor. En la primera designación, el docente recibe los perfiles de proyecto que se presentaron sin revisor y esperan revisión. Un perfil de proyecto aprobado o rechazado conserva al revisor que lo evaluó, con sus observaciones.

### BR-004: Revisor de los perfiles nuevos

Todo perfil de proyecto que un estudiante presente después de la designación tiene como revisor al docente de taller de grado designado (UC-004). Un perfil de proyecto presentado mientras no hay docente de taller de grado queda en revisión, sin revisor, hasta la primera designación.

### BR-005: Acceso del docente anterior

El docente anterior deja de ver los perfiles de proyecto que pasaron al nuevo revisor (NFR-004), salvo que participe en esos trámites de titulación por otro papel.
